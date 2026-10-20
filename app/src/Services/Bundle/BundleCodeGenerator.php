<?php

namespace App\Services\Bundle;

use App\Doctrine\Entity\BundleGeneratorCombinationInterface;
use App\Doctrine\Entity\BundleGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

class BundleCodeGenerator implements BundleCodeGeneratorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function generateCodes(ResourceControllerEvent $event): void
    {
        /** @var BundleGeneratorInterface $configurator */
        $configurator = $event->getSubject();

        /** @var BundleGeneratorCombinationInterface $combination */
        foreach ($configurator->getCombinations() as $combination) {
            if ($combination->isWillBeGenerated()) {
                $combination->setCode($configurator->generateCodeForCombination($combination));
            } else {
                $combination->setCode(null);
            }

            $this->entityManager->persist($combination);
            $this->entityManager->flush();
        }
    }
}
