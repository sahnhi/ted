<?php

namespace App\Controller\Api\Taxon;

use App\ViewRepository\TaxonViewRepositoryInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

class ListAction
{
    private readonly Request $request;

    public function __construct(
        private readonly TaxonViewRepositoryInterface $taxonViewRepository,
        private readonly ViewHandlerInterface $viewHandler,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function __invoke(string $channelCode, string $localeCode): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['taxon.list']);

            $slugs = $this->request->query->get('slugs');
            if (!is_null($slugs)) {
                Assert::isArray($slugs, 'Slugs must be an array');
            }
            $bounds = !is_null($this->request->query->get('bounds'));

            $view = View::create($this->taxonViewRepository->findAllByChannelAndLocale($channelCode, $localeCode, $slugs, $bounds), Response::HTTP_OK);
            $view->setContext($context);

            $response = $this->viewHandler->handle($view);
            $response->setSharedMaxAge(600);
            $response->headers->addCacheControlDirective('must-revalidate', true);

            return $response;
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
