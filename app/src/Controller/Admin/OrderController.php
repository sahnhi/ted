<?php

namespace App\Controller\Admin;

use App\Business\Command\Quotation;
use App\Doctrine\Entity\CustomerSupport;
use App\Doctrine\Entity\OrderOutOfStock;
use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Form\Type\CancelOrderType;
use App\Form\Type\NewOrderType;
use App\Form\Type\NoteType;
use App\Form\Type\Payment\RefundType;
use App\Form\Type\QuotationType;
use App\Form\Type\Reason\OrderReasonType;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Services\Admin\Export\OrderExport;
use App\Services\Admin\OrderService;
use App\Services\Channel\ChannelCode;
use App\Services\Mail\OutOfStock\SendEmailToCustomer;
use FOS\RestBundle\View\View;
use Sentry;
use Sylius\Bundle\CoreBundle\Controller\OrderController as BaseOrderController;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Order\SyliusCartEvents;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Zendesk\API\HttpClient;

class OrderController extends BaseOrderController
{
    public function indexAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($configuration, ResourceActions::INDEX);

        /** @var ResourceGridView $resources */
        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        $this->eventDispatcher->dispatchMultiple(ResourceActions::INDEX, $configuration, $resources);

        if ('csv' === $request->get('export')) {
            $orderExport = $this->get('app.admin.order_export');

            $data = [];
            if ($resources->getData()->getNbResults() > OrderExport::MAX_ITEM_EXPORT) {
                $translator = $this->get('translator');
                $request->getSession()->getFlashBag()->add(
                    'error',
                    $translator->trans('app.ui.export_limit'),
                );
                $data[] = [$translator->trans('app.ui.export_limit')];
            }

            if ('sylius_admin_order_return_logistic_export' === $request->attributes->get('_route')) {
                return $orderExport->generateLogisticExport($resources, $data);
            }

            return $orderExport->generateExport($resources, $data);
        }

        if ($configuration->isHtmlRequest()) {
            return $this->render($configuration->getTemplate(ResourceActions::INDEX . '.html'), [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resources' => $resources,
                $this->metadata->getPluralName() => $resources,
            ]);
        }

        return $this->createRestView($configuration, $resources);
    }

    public function showAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        /** @var Order $resource */
        $resource = $this->findOr404($configuration);

        $ids = [];
        $referencedTickets = $this
            ->getDoctrine()
            ->getManagerForClass(CustomerSupport::class)
            ->getRepository(CustomerSupport::class)
            ->findBy(['customer' => $resource->getCustomer()->getId()]);
        /** @var CustomerSupport $ticket */
        foreach ($referencedTickets as $ticket) {
            $ids[] = $ticket->getSupportId();
        }

        /** @var HttpClient $zendeskService */
        $zendeskService = $this->get('client.zendesk');
        $supportTickets = [];
        if (!empty($ids)) {
            try {
                $supportTickets = $zendeskService->tickets()->findMany($ids);
            } catch (\Exception $e) {
                Sentry\configureScope(function (Sentry\State\Scope $scope) use ($resource, $e): void {
                    $scope->setLevel(Sentry\Severity::warning());
                    $scope->setTag('Zendesk', $resource->getCustomer()->getId());
                    Sentry\captureException($e);
                });
            }
        }

        /* Custom Code */
        if (empty($request->request->all()) && 'POST' === $request->getMethod() && !empty($resource->getOrderReason())) {
            foreach ($resource->getOrderReason() as $orderReason) {
                $resource->removeOrderReason($orderReason);
            }
            $em->persist($resource);
            $em->flush();
        }

        $orderService = $this->get(OrderService::class)->init($request, $resource);

        $form = $orderService->createForm(
            [
                'formType' => NoteType::class,
                'data' => [],
            ]
        );
        $orderReasonForm = $orderService->createForm(
            [
                'formType' => OrderReasonType::class,
                'data' => [
                    'order' => $resource,
                    'user' => $this->getUser(),
                    'attr' => ['isProblem' => 1],
                ],
            ],
            'order_reason_problem'
        );
        $orderReasonCreateForm = $orderService->createForm(
            [
                'formType' => OrderReasonType::class,
                'data' => [
                    'order' => $resource,
                    'user' => $this->getUser(),
                    'attr' => ['isProblem' => 0],
                ],
            ]
        );

        $refundForm = $orderService->createForm(
            [
                'formType' => RefundType::class,
                'data' => [],
                'order' => $resource,
            ]
        );

        $transactionIdsForms = $orderService->checkTransactionIds($resource);

        // Security redirect to referer
        // TODO : À refaire, ceci est temporaire et pas très propre
        // TODO : Il faudrait mettre le formulaire "refundForm" dans un partial pour pouvoir configurer cette redirection directement dans la route
        if ($refundForm->isSubmitted() && $refundForm->isValid()) {
            return new RedirectResponse($request->headers->get('referer'));
        }

        $refundFormView = $parentOrder = false;
        if (null !== $resource->getParentNumber()) {
            $parentOrder = $this->getOrderRepository()->findOneByNumber($resource->getParentNumber());

            if ($resource->isFromChannel(ChannelCode::CHANNEL_RETURN)) {
                if (!$orderService->isRefundAlreadyDone($resource)) {
                    $refundFormView = $refundForm->createView();
                }

                foreach ($parentOrder->getGiftCards() as $giftCard) {
                    $resource->addGiftCard($giftCard);
                }
            }

            foreach ($parentOrder->getPayments() as $payment) {
                /** @var $payment PaymentInterface */
                if (!$resource->isFromChannel(ChannelCode::CHANNEL_RETURN) && PaymentInterfaceAlias::STATE_COMPLETED === $payment->getState()) {
                    $resource->addPayment($payment);
                    break;
                } elseif ($resource->isFromChannel(ChannelCode::CHANNEL_RETURN) && (
                    PaymentInterfaceAlias::STATE_COMPLETED === $payment->getState()
                    || PaymentInterfaceAlias::STATE_REFUNDED === $payment->getState()
                )) {
                    $resource->addPayment($payment);
                }
            }
        }

        $parent = ChannelCode::CHANNEL_RETURN === $resource->getChannel()->getCode() && $resource->getParentNumber() ?
            $this->getOrderRepository()->findOneByNumber($resource->getParentNumber()) :
            null;

        /* End Custom Code */
        $this->eventDispatcher->dispatch(ResourceActions::SHOW, $configuration, $resource);

        if ($configuration->isHtmlRequest()) {
            return $this->render($configuration->getTemplate(ResourceActions::SHOW . '.html'), [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                'supportTickets' => $supportTickets,
                'orderReasons' => $resource->getOrderReason(),
                'form' => $form->createView(),
                'orderReasonForm' => $orderReasonForm->createView(),
                'orderReasonCreateForm' => $orderReasonCreateForm->createView(),
                'transactionsIdsForms' => $transactionIdsForms,
                'refundForm' => $refundFormView,
                'parent' => $parent, // Custom Code
                $this->metadata->getName() => $resource,
            ]);
        }

        return $this->createRestView($configuration, $resource);
    }

    public function summaryAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $cart = $this->getCurrentCart();
        if (null !== $cart->getId()) {
            $cart = $this->getOrderRepository()->findCartForSummary($cart->getId());
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($cart));
        }

        $form = $this->resourceFormFactory->create($configuration, $cart);

        /* Begin Custom Part */
        $command = new Quotation($cart);
        $quotationForm = $this->createForm(QuotationType::class, $command);
        /* End Custom Part */

        return $this->render(
            $configuration->getTemplate('summary.html'),
            [
                'cart' => $cart,
                'form' => $form->createView(),
                'quotationForm' => $quotationForm->createView(), // Custom part
            ]
        );
    }

    public function saveAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->getCurrentCart();

        $form = $this->resourceFormFactory->create($configuration, $resource);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $form->handleRequest($request)->isValid()) {
            $resource = $form->getData();

            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                return $this->redirectHandler->redirectToResource($configuration, $resource);
            }

            if ($configuration->hasStateMachine()) {
                $this->stateMachine->apply($configuration, $resource);
            }

            $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

            if (!$configuration->isHtmlRequest()) {
                return $this->viewHandler->handle($configuration, View::create(null, Response::HTTP_NO_CONTENT));
            }

            $this->getEventDispatcher()->dispatch(new GenericEvent($resource), SyliusCartEvents::CART_CHANGE);
            $this->manager->flush();

            $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        /* Begin Custom Part */
        $cart = $this->getCurrentCart();
        $command = new Quotation($cart);
        $quotationForm = $this->createForm(QuotationType::class, $command);
        /* End Custom Part */

        return $this->render(
            $configuration->getTemplate(ResourceActions::UPDATE . '.html'),
            [
                'configuration' => $configuration,
                $this->metadata->getName() => $resource,
                'form' => $form->createView(),
                'cart' => $resource,
                'quotationForm' => $quotationForm->createView(), // Custom part
            ]
        );
    }

    /**
     * @throws EmailConfigurationNotFoundException
     * @throws TransportExceptionInterface
     */
    public function outOfStockAction(Request $request, EmailConfigurationRegistryInterface $emailConfigurationRegistry): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::INDEX);

        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        /** @var MailerInterface $mailer */
        $mailer = $this->get('mailer');
        $cancelService = $this->get('app.service.spidy.cancel');
        $state = $request->get('state', null);
        $sendMail = false;

        if (null !== $state) {
            $body = 'Bonjour,<br/><br/>
                Merci de bloquer et de mettre en rupture au 31/12 les commandes suivantes : <br/>';

            /** @var Order $resource */
            foreach ($resources as $resource) {
                if (OrderOutOfStockController::NONE_VALUE === $state && null !== $resource->getOrderOutOfStock()) {
                    $this->manager->remove($resource->getOrderOutOfStock());
                    $resource->setOrderOutOfStock(null);
                } else {
                    if (null === $resource->getOrderOutOfStock()) {
                        $outOfStock = new OrderOutOfStock();
                        $outOfStock->setOrder($resource);
                        $outOfStock->setCreatedAt(new \DateTime());

                        $resource->setOrderOutOfStock($outOfStock);
                    }

                    $outOfStock = $resource->getOrderOutOfStock();
                    if ($state !== $outOfStock->getStatus()) {
                        $outOfStock->setStatus($state);
                        $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);

                        if (OrderOutOfStockInterface::STATE_OUT_OF_STOCK === $state) {
                            $body .= 'TED' . ltrim((string) $resource->getNumber(), '0') . '<br />';
                            $cancelService->cancel($resource, false);
                            $sendMail = true;
                            $maxDate = new \DateTime();
                            /** @var OrderItem $item */
                            foreach ($resource->getItems() as $item) {
                                /** @var ProductVariant $variant */
                                $variant = $item->getVariant();
                                foreach ($variant->getShippingStock() as $stock) {
                                    $maxDate = max($maxDate, $stock->getShippingDate());
                                }
                                if (!empty($item->getOrderItemLogistics())) {
                                    foreach ($item->getOrderItemLogistics() as $itemCombo) {
                                        if (!empty($itemCombo->getProductVariantLogistic())) {
                                            /** @var ProductVariant $variant */
                                            $variant = $itemCombo->getProductVariantLogistic();
                                            foreach ($variant->getShippingStock() as $stock) {
                                                $maxDate = max($maxDate, $stock->getShippingDate());
                                            }
                                        }
                                    }
                                }
                            }
                            $this->get(SendEmailToCustomer::class)->send($resource, $maxDate);
                            $this->addFlash('success', 'Email envoyé au client');
                        }
                    }

                    $this->manager->persist($outOfStock);
                }
            }

            $this->manager->flush();

            if ($sendMail) {
                $body .= '<br />Bonne journée,<br/>
                Merci,<br/><br/>
                Cordialement,<br/><br/>
                Tediber';

                $message = (new Email())
                    ->subject('Blocage de commandes au 31/12')
                    ->from(new Address('noreply@tediber.com', 'TEDIBER Sylius'))
                    ->to(...$emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::BLOCKED_ORDER)->getPrincipals())
                    ->html($body);

                $mailer->send($message);
            }
        }

        return $this->redirectHandler->redirectToReferer($configuration);
    }

    public function cancelAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->findOr404($configuration);

        $form = $this->createForm(CancelOrderType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resource = $form->getData();

            $this->stateMachine->apply($configuration, $resource);
            $this->manager->flush();

            $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        return $this->render(
            $configuration->getTemplate(ResourceActions::UPDATE . '.html'),
            [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                $this->metadata->getName() => $resource,
                'form' => $form->createView(),
            ]
        );
    }

    public function historyAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);

        /** @var Order $resource */
        $resource = $this->findOr404($configuration);

        $orders = $this->getOrderRepository()->findAssociatedOrders(substr((string) $resource->getNumber(), 0, 9));

        return $this->render(
            $configuration->getTemplate(ResourceActions::UPDATE . '.html'),
            [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                'orders' => $orders,
                $this->metadata->getName() => $resource,
            ]
        );
    }

    public function createAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::CREATE);
        $newResource = $this->newResourceFactory->create($configuration, $this->factory);

        $form = $this->resourceFormFactory->create($configuration, $newResource);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $newResource = $form->getData();
            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::CREATE, $configuration, $newResource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                $eventResponse = $event->getResponse();
                if (null !== $eventResponse) {
                    return $eventResponse;
                }

                return $this->redirectHandler->redirectToIndex($configuration, $newResource);
            }
            if ($configuration->hasStateMachine()) {
                $this->stateMachine->apply($configuration, $newResource);
            }

            $this->repository->add($newResource);

            /* Custom Code */
            $formData = $request->get('sylius_admin_order_creation_new_order');
            if (!empty($formData['bisExchange'])) {
                $response = $this->forward(Action\BisCreationWithExchangeAction::class, [
                    'configuration' => $configuration,
                    'order' => $newResource,
                ]);
                if (!in_array($response->getStatusCode(), [201, 200])) {
                    $this->addFlash('error', $response->getContent());
                }
            }
            /* End Custom Code */

            if ($configuration->isHtmlRequest()) {
                $this->flashHelper->addSuccessFlash($configuration, ResourceActions::CREATE, $newResource);
            }

            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::CREATE, $configuration, $newResource);

            if (!$configuration->isHtmlRequest()) {
                return $this->viewHandler->handle($configuration, View::create($newResource, Response::HTTP_CREATED));
            }

            return $postEvent->getResponse() ?? $this->redirectHandler->redirectToResource($configuration, $newResource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::CREATE, $configuration, $newResource);
        $initializeEventResponse = $initializeEvent->getResponse();
        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        return $this->render(
            $configuration->getTemplate(ResourceActions::CREATE . '.html'),
            [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $newResource,
                $this->metadata->getName() => $newResource,
                'form' => $form->createView(),
            ]
        );
    }

    public function previewAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        /** @var Order $order */
        $order = $this->findOr404($configuration);

        $returnType = $request->request->get('sylius_admin_order_creation_new_order', [])['returnType'] ?? null;

        $form = $this->createForm(NewOrderType::class, $order, [
            'user' => $this->getUser(),
            'attr' => [
                'returnType' => $returnType,
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();

            /** @var OrderProcessorInterface $orderProcessor */
            $orderProcessor = $this->get(OrderProcessorInterface::class);
            $orderProcessor->process($order);

            $this->addFlash('success', 'YOUPI');

            return $this->redirectToRoute('sylius_admin_order_show', ['id' => $order->getId()]);
        }

        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', sprintf('%s : %s', $error->getMessage(), $error->getOrigin()?->getName()));
        }

        return $this->render('@SyliusAdminOrderCreationPlugin/Order/preview.html.twig', [
            'form' => $form->createView(),
            'disabledInputs' => true,
        ]);
    }

    public function updateAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->findOr404($configuration);

        $form = $this->resourceFormFactory->create($configuration, $resource);
        $form->handleRequest($request);

        if (
            in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)
            && $form->isSubmitted()
            && $form->isValid()
        ) {
            $resource = $form->getData();
            /** @var ResourceControllerEvent $event */
            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                $eventResponse = $event->getResponse();
                if (null !== $eventResponse) {
                    return $eventResponse;
                }

                return $this->redirectHandler->redirectToResource($configuration, $resource);
            }

            try {
                $this->resourceUpdateHandler->handle($resource, $configuration, $this->manager);
            } catch (UpdateHandlingException $exception) {
                if (!$configuration->isHtmlRequest()) {
                    return $this->createRestView($configuration, $form, $exception->getApiResponseCode());
                }

                $this->flashHelper->addErrorFlash($configuration, $exception->getFlash());

                return $this->redirectHandler->redirectToReferer($configuration);
            }
            if ($configuration->isHtmlRequest()) {
                $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);
            }

            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

            /* Custom Code */
            $isPreview = str_contains((string) $request->getPathInfo(), 'create-from-preview');
            if (!empty($resource->getParentNumber())) {
                $orderRepository = $this->get('sylius.repository.order');
                $bis = $orderRepository->getLastCreatedBisByParentNumber($resource->getParentNumber());
                if (null !== $bis && $bis->getChannel()->getCode() !== $resource->getChannel()->getCode()) {
                    if (!$isPreview) {
                        $bis->setCheckoutState(OrderCheckoutStates::STATE_PAYMENT_SKIPPED);
                        $form = $this->resourceFormFactory->create($configuration, $bis);
                        $form->handleRequest($request);
                        $bis = $form->getData();
                        $this->resourceUpdateHandler->handle($bis, $configuration, $this->manager);
                    }
                    $response = $this->forward(Action\BisCreationWithExchangeAction::class, [
                        'configuration' => $configuration,
                        'order' => $bis,
                    ]);
                    if (!in_array($response->getStatusCode(), [201, 200])) {
                        $this->addFlash('error', $response->getContent());
                    }
                }
            }
            /* End Custom Code */
            if (!$configuration->isHtmlRequest()) {
                if ($configuration->getParameters()->get('return_content', false)) {
                    return $this->createRestView($configuration, $resource, Response::HTTP_OK);
                }

                return $this->createRestView($configuration, null, Response::HTTP_NO_CONTENT);
            }

            $postEventResponse = $postEvent->getResponse();
            if (null !== $postEventResponse) {
                return $postEventResponse;
            }

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->createRestView($configuration, $form, Response::HTTP_BAD_REQUEST);
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::UPDATE, $configuration, $resource);
        $initializeEventResponse = $initializeEvent->getResponse();
        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        return $this->render($configuration->getTemplate(ResourceActions::UPDATE . '.html'), [
            'configuration' => $configuration,
            'metadata' => $this->metadata,
            'resource' => $resource,
            $this->metadata->getName() => $resource,
            'form' => $form->createView(),
        ]);
    }
}
