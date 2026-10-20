<?php

namespace App\Controller\ProductOptionValueLocaleDefault;

use App\Doctrine\Entity\ProductOptionValueLocaleDefault;
use App\Form\Type\ProductOptionValueLocaleDefaultType;
use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductOptionValueLocaleDefaultController extends ResourceController
{
    public function updateAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $productOptionRepository = $this->container->get('sylius.repository.product_option');
        $productOptionValueRepository = $this->container->get('sylius.repository.product_option_value');
        $localeRepository = $this->container->get('sylius.repository.locale');
        $productOptionLocaleDefaultRepository = $em->getRepository(ProductOptionValueLocaleDefault::class);

        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->findOr404($configuration);
        $productOptionLocaleDefaultValue = $productOptionLocaleDefaultRepository->findBy(['product' => $resource->getId()]);

        $form = $this->createForm(ProductOptionValueLocaleDefaultType::class, $resource);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $form->handleRequest($request)->isValid()) {
            $productOptionLocaleDefault = $_POST['sylius_product_option_locale_default'];

            foreach ($productOptionLocaleDefaultValue as $optionLocaleDefault) {
                $em->remove($optionLocaleDefault);
            }

            foreach ($productOptionLocaleDefault['optionsLocalesDefault'] as $option) {
                foreach ($option['locale'] as $locale) {
                    $optionLocaleDefault = new ProductOptionValueLocaleDefault();
                    $optionLocaleDefault->setOption($productOptionRepository->find($option['option']));
                    $optionLocaleDefault->setLocale($localeRepository->find($locale['locale']));
                    $optionLocaleDefault->setOptionValue($productOptionValueRepository->find($locale['optionValue']));
                    $optionLocaleDefault->setProduct($resource);

                    $this->getDoctrine()->getManager()->persist($optionLocaleDefault);
                    $this->getDoctrine()->getManager()->flush();
                }
            }

            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                if ($event->hasResponse()) {
                    return $event->getResponse();
                }

                return $this->redirectHandler->redirectToResource($configuration, $resource);
            }

            try {
                $this->resourceUpdateHandler->handle($resource, $configuration, $this->manager);
            } catch (UpdateHandlingException $exception) {
                if (!$configuration->isHtmlRequest()) {
                    return $this->viewHandler->handle(
                        $configuration,
                        View::create($form, $exception->getApiResponseCode())
                    );
                }

                $this->flashHelper->addErrorFlash($configuration, $exception->getFlash());

                return $this->redirectHandler->redirectToReferer($configuration);
            }

            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

            if (!$configuration->isHtmlRequest()) {
                $view = $configuration->getParameters()->get('return_content', false) ? View::create($resource, Response::HTTP_OK) : View::create(null, Response::HTTP_NO_CONTENT);

                return $this->viewHandler->handle($configuration, $view);
            }

            $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);

            if ($postEvent->hasResponse()) {
                return $postEvent->getResponse();
            }

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::UPDATE, $configuration, $resource);
        if ($initializeEvent->hasResponse()) {
            return $initializeEvent->getResponse();
        }

        return $this->render(
            $configuration->getTemplate(ResourceActions::UPDATE . '.html'),
            [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                $this->metadata->getName() => $resource,
                'form' => $form->createView(),
                'productOptionValueLocaleDefaultValue' => $productOptionLocaleDefaultValue,
            ]
        );
    }
}
