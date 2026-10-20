<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Factory\OrderFactory;
use App\Form\Type\NewOrderType;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Webmozart\Assert\Assert;

final class OrderPreviewAction
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly FormFactoryInterface $formFactory,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly Environment $twig,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FlashBagInterface $flashBag
    ) {
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(Request $request): Response
    {
        Assert::notNull($request->attributes->get('customerId'));
        $customerEmail = $request->attributes->get('customerId');

        Assert::notNull($request->attributes->get('channelCode'));
        $channelCode = $request->attributes->get('channelCode');

        /* Custom Code */
        $returnType = $request->request->get('sylius_admin_order_creation_new_order')['returnType'] ?? null;
        /* End Custom Code */

        $order = $this->orderFactory->createForCustomerAndChannel($customerEmail, $channelCode);
        $form = $this->formFactory->create(NewOrderType::class, $order, [
            /* Custom Code */
            'user' => $this->tokenStorage->getToken()->getUser(),
            'attr' => [
                'returnType' => $returnType,
            ],
            /* End Custom Code */
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $order = $form->getData();
            $this->orderProcessor->process($order);

            return new Response($this->twig->render('@SyliusAdminOrderCreationPlugin/Order/preview.html.twig', [
                'form' => $form->createView(),
            ]));
        }

        foreach ($form->getErrors(true) as $error) {
            $this->flashBag->add(
                'error',
                $error->getMessage()
                . ' : '
                . $error->getOrigin()->getName()
            );
        }

        return new RedirectResponse($request->headers->get('referer'));
    }
}
