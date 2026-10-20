<?php

namespace App\Controller\Admin;

use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductInterface;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Error\RuntimeError;

class BundleGeneratorController extends ResourceController
{
    private const ACTION_GENERATOR_CREATED = 'configurator_created';
    private const ACTION_NEXT_STEP_TRIGGERED = 'next_step_triggered';
    private const ACTION_SAVE_DATA = 'save_data';
    private const ACTION_END = 'end';

    public function showAction(Request $request): Response
    {
        try {
            return parent::showAction($request);
        } catch (RuntimeError) {
            // redirect when product has 0 active variant
            return $this->redirectToRoute('sylius_shop_homepage');
        }
    }

    public function generateBundlesAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $id = $request->attributes->get('id');
        $productId = $request->attributes->get('productId');

        $product = $this->manager->getRepository(Product::class)->find($productId);
        if (!$product instanceof ProductInterface || $product->isNotEmptyAndDoesNotContainBundle() || !$product->getCanGenerateBundle()) {
            $this->addFlash('error', $this->get('translator')->trans('app.ui.bundle_association.errors.product_is_not_compatible'));

            return $this->redirectHandler->redirectToIndex($configuration, $product);
        }

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);

        if (null !== $id) {
            /** @var BundleGeneratorInterface $resource */
            $resource = $this->findOr404($configuration);

            if (!$resource->getProduct() === $product) {
                throw new NotFoundHttpException(sprintf('The "%s" has not been found', $this->metadata->getHumanizedName()));
            }
        } else {
            /** @var BundleGeneratorInterface $resource */
            $resource = $this->newResourceFactory->create($configuration, $this->factory);
            $this->repository->add($resource);

            return $this->redirectToRoute('app_admin_bundle_generation_form', ['id' => $resource->getId(), 'productId' => $product->getId()]);
        }

        /** @var FormFlowInterface $flow */
        $flow = $this->get(\App\Form\Type\Bundle\BundleGenerationFlow::class);
        $flow->bind($resource);

        $this->eventDispatcher->dispatchInitializeEvent(self::ACTION_GENERATOR_CREATED . '_' . $flow->getCurrentStepLabel(), $configuration, $flow->getFormData());

        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            $this->eventDispatcher->dispatchPreEvent(self::ACTION_SAVE_DATA . '_' . $flow->getCurrentStepLabel(), $configuration, $flow->getFormData());

            $this->repository->add($flow->getFormData());

            $this->manager->refresh($resource);

            $this->eventDispatcher->dispatchPostEvent(self::ACTION_SAVE_DATA . '_' . $flow->getCurrentStepLabel(), $configuration, $flow->getFormData());

            if ($flow->nextStep()) {
                $this->eventDispatcher->dispatchInitializeEvent(self::ACTION_NEXT_STEP_TRIGGERED . '_' . $flow->getCurrentStepLabel(), $configuration, $flow->getFormData());
                $form = $flow->createForm();
            } else {
                $this->eventDispatcher->dispatchPostEvent(self::ACTION_END, $configuration, $flow->getFormData());
                $flow->reset();

                return $this->redirectToRoute('sylius_admin_product_variant_index', ['productId' => $product->getId()]);
            }
        }

        return $this->render('@App/Admin/Bundle/' . $flow->getCurrentStepLabel() . '.html.twig', [
            'configuration' => $configuration,
            'metadata' => $this->metadata,
            'resource' => $resource,
            $this->metadata->getName() => $resource,
            'form' => $form->createView(),
            'flow' => $flow,
            'product' => $product,
        ]);
    }
}
