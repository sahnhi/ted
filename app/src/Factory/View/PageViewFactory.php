<?php

namespace App\Factory\View;

use App\View\PageLinksView;
use App\View\PageView;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\RouterInterface;

final class PageViewFactory implements PageViewFactoryInterface
{
    public function __construct(
        private readonly RouterInterface $router
    ) {
    }

    public function create(Pagerfanta $pagerfanta, string $route, array $parameters): PageView
    {
        $page = new PageView();
        $page->page = $pagerfanta->getCurrentPage();
        $page->limit = $pagerfanta->getMaxPerPage();
        $page->pages = $pagerfanta->getNbPages();
        $page->total = $pagerfanta->getNbResults();
        $page->links = new PageLinksView();
        $page->links->self = $this->router->generate($route, array_merge($parameters, [
            'page' => $pagerfanta->getCurrentPage(),
            'limit' => $pagerfanta->getMaxPerPage(),
        ]));
        $page->links->first = $this->router->generate($route, array_merge($parameters, [
            'page' => 1,
            'limit' => $pagerfanta->getMaxPerPage(),
        ]));
        $page->links->last = $this->router->generate($route, array_merge($parameters, [
            'page' => $pagerfanta->getNbPages(),
            'limit' => $pagerfanta->getMaxPerPage(),
        ]));
        $page->links->next = $this->router->generate($route, array_merge($parameters, [
            'page' => ($pagerfanta->getCurrentPage() < $pagerfanta->getNbPages()) ? $pagerfanta->getCurrentPage() + 1 : $pagerfanta->getCurrentPage(),
            'limit' => $pagerfanta->getMaxPerPage(),
        ]));

        return $page;
    }
}
