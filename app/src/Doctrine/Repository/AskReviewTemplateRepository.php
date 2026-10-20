<?php

declare(strict_types=1);

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\AskReviewTemplate;
use App\Exception\AskReviewTemplateIdNotFound;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

final class AskReviewTemplateRepository extends EntityRepository implements AskReviewTemplateRepositoryInterface
{
    public function getTemplateFromServiceName(string $serviceName): ?AskReviewTemplate
    {
        $qb = $this->createQueryBuilder('art')
            ->where('art.serviceName = :serviceName')
            ->setParameter('serviceName', $serviceName);

        $result = $qb->getQuery()->getOneOrNullResult();

        if (null === $result) {
            throw new AskReviewTemplateIdNotFound();
        }

        return $result;
    }
}
