<?php

namespace App\Controller\Admin;

use App\Doctrine\Entity\ProductReviewAnswer;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductReview;
use App\Doctrine\Repository\Sylius\ProductRepository;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use App\Model\ProductReferenceInterface;
use App\Services\Admin\Export\ExportCsv;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\View\View;
use Sylius\Bundle\CoreBundle\Form\Type\Product\ProductReviewType;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductReviewController extends ResourceController
{
    public function indexAction(Request $request, $export = null): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::INDEX);

        if ('csv' === $export) {
            /** @var ProductReviewRepository $productReviewRepository */
            $productReviewRepository = $this->getDoctrine()->getRepository(ProductReview::class);
            $criteria = $request->get('criteria');

            $productReviewWithCriteria = $productReviewRepository->csvListQueryBuilder($criteria);

            $data = [];
            $data[] = ['Date', 'Title', 'comment', 'Rating', 'Status', 'Product', 'Author', 'Shipping Method', 'Order', 'Locale'];

            $questionPrefix = 'Question ';

            while (false !== ($row = $productReviewWithCriteria->next())) {
                $line = [
                    $row[0]->getCreatedAt()->format('d/m/Y H:i:s'),
                    $row[0]->getTitle(),
                    $row[0]->getComment(),
                    $row[0]->getRating(),
                    $row[0]->getStatus(),
                    $row[0]->getReviewSubject(),
                    $row[0]->getAuthor()->getEmail(),
                    $row[0]->getShippingMethod(),
                    $row[0]->getOrder(),
                    $row[0]->getLocale(),
                ];

                /** @var ProductReviewAnswer $answer */
                foreach ($row[0]->getAnswers() as $answer) {
                    $question = $answer->getQuestion();

                    if (false === ($key = array_search($questionPrefix . $question->getQuestion(), $data[0]))) {
                        $data[0][] = $questionPrefix . $question->getQuestion();
                        $key = array_key_last($data[0]);
                    }

                    $line[$key] = $answer->getAnswer();
                }

                $data[] = $line;

                $this->getDoctrine()->getManager()->detach($row[0]);
            }

            return (new ExportCsv())->generateCsv($data, 'product-review');
        }

        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        $this->eventDispatcher->dispatchMultiple(ResourceActions::INDEX, $configuration, $resources);

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

    /**
     * @return RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function translateAction(Request $request)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $idReview = $request->get('id');

        try {
            $productReviewRepository = $this->getDoctrine()->getRepository(ProductReview::class);
            $productReviewOrigin = $productReviewRepository->find($idReview);
        } catch (\Exception) {
            throw new \Exception('Product Review origin not found');
        }

        $productReview = clone $productReviewOrigin;
        $productReview->setParent($productReviewOrigin);
        $productReview->setAuthor($productReviewOrigin->getAuthor());

        $form = $this->createForm(ProductReviewType::class, $productReview);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($productReview);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sylius_admin_product_review_index');
        }

        return $this->render('@App/ProductReview/translate.html.twig', [
            'configuration' => $configuration,
            'metadata' => $this->metadata,
            'form' => $form->createView(),
            'resource' => $productReview,
            'product_review' => $productReview,
        ]);
    }

    public function createAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::CREATE);
        $newResource = $this->newResourceFactory->create($configuration, $this->factory);

        $form = $this->createForm(ProductReviewType::class, $newResource, ['product_review' => 'new']);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $newResource = $form->getData();
            $newResource->setProduct($form->get('product')->getData());
            $newResource->setReviewSubject($newResource->getProduct());
            $newResource->setAuthor($form->get('author')->getData());

            $this->getDoctrine()->getManager()->persist($newResource);

            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::CREATE, $configuration, $newResource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new BadRequestHttpException($event->getMessage(), null, $event->getErrorCode());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                if ($event->hasResponse()) {
                    return $event->getResponse();
                }

                return $this->redirectHandler->redirectToIndex($configuration, $newResource);
            }

            if ($configuration->hasStateMachine()) {
                $this->stateMachine->apply($configuration, $newResource);
            }

            $this->repository->add($newResource);
            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::CREATE, $configuration, $newResource);

            if (!$configuration->isHtmlRequest()) {
                return $this->viewHandler->handle($configuration, View::create($newResource, Response::HTTP_CREATED));
            }

            $this->flashHelper->addSuccessFlash($configuration, ResourceActions::CREATE, $newResource);

            if ($postEvent->hasResponse()) {
                return $postEvent->getResponse();
            }

            return $this->redirectHandler->redirectToResource($configuration, $newResource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::CREATE, $configuration, $newResource);
        if ($initializeEvent->hasResponse()) {
            return $initializeEvent->getResponse();
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

    public function remercieAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $entityManager = $this->getDoctrine()->getManager();

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        $resource = $this->findOr404($configuration);

        $today = new \DateTime('NOW');

        if (!$resource->getRemercie()) {
            $resource->setRemercie(true);
            $resource->setRemercieDate($today);
        }

        $entityManager->persist($resource);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @throws NonUniqueResultException
     */
    public function summaryAction()
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        /** @var ProductReviewRepository $productReviewRepository */
        $productReviewRepository = $this->getDoctrine()->getRepository(ProductReview::class);

        $brandProduct = $productRepository->findOneByCode(Product::BRAND_REVIEW_CODE);
        $trustPilotProduct = $productRepository->findOneByCode(Product::TRUST_PILOT_REVIEW_CODE);

        $productsReviews = $productReviewRepository->countAllForProducts(array_merge(
            ProductReferenceInterface::REPOSSESS_REFS,
            [
                Product::TRUST_PILOT_REVIEW_CODE,
                Product::BRAND_REVIEW_CODE,
            ]
        ));
        $brandReviews = $productReviewRepository->countAllForProduct($brandProduct);
        $trustPilotReviews = null !== $trustPilotProduct ?
            $productReviewRepository->countAllForProduct($trustPilotProduct) :
            [
                'averageRating' => null,
                'numberOfRating' => 0,
            ];

        $reviews = [
            'productsReviews' => array_merge(['name' => 'produits Tediber'], $productsReviews),
            'brandReviews' => array_merge(['name' => 'marque Tediber'], $brandReviews),
            'trustPilotReviews' => array_merge(['name' => 'TrustPilot'], $trustPilotReviews),
        ];

        return $this->render('@App/ProductReview/_count.html.twig', [
            'reviews' => $reviews,
        ]);
    }
}
