<?php

namespace App\Doctrine\Loader;

use Doctrine\ORM\Mapping\Entity;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;

class ResourceLoader implements LoaderResourcesByGridViewInterface
{
    /**
     * @return Entity[]
     */
    public function load(ResourceGridView $resourceGridView, int $offset): array
    {
        $maxResults = 10;
        $firstResult = $maxResults * $offset;
        $resourceGridView->getData()->getAdapter()->getQuery()->setFirstResult($firstResult);
        $resourceGridView->getData()->getAdapter()->getQuery()->setMaxResults($maxResults);

        return $resourceGridView->getData()->getAdapter()->getQuery()->getResult();
    }
}
