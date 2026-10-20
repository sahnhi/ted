<?php

namespace App\Controller\Api\Familiarity;

use App\View\FamiliarityView;
use App\ViewRepository\FamiliarityViewRepositoryInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListAction
{
    public function __construct(
        private readonly FamiliarityViewRepositoryInterface $familiarityViewRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['familiarity.list']);

            $familiarities = $this->familiarityViewRepository->getAvailableByLocaleEnabled($request->attributes->get('localeCode'));

            $familiaritiesView = [];
            foreach ($familiarities->items as $familiarity) {
                $familiarityView = new FamiliarityView();
                $familiarityView->code = $familiarity['code'];
                $familiarityView->name = $familiarity['name'];

                $familiaritiesView[] = $familiarityView;
            }

            $view = View::create($familiaritiesView, Response::HTTP_OK);

            return $this->viewHandler->handle($view);
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
