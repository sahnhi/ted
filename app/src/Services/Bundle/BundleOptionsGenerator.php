<?php

namespace App\Services\Bundle;

use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\BundleGeneratorOption;
use App\Doctrine\Entity\BundleGeneratorOptionInterface;
use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;

class BundleOptionsGenerator implements BundleOptionsGeneratorInterface
{
    public function generateOptions(ResourceControllerEvent $event): void
    {
        /** @var BundleGeneratorInterface $generator */
        $generator = $event->getSubject();
        $product = $generator->getProduct();
        $optionsGenerator = [];

        foreach ($product->getOptions() as $option) {
            $optionsGenerator[] = $this->generateOption($generator, $option);
        }

        $this->clearOtherOptions($generator, $optionsGenerator);
    }

    private function generateOption(BundleGeneratorInterface $generator, ProductOptionInterface $option): BundleGeneratorOptionInterface
    {
        if (!($optionGenerator = $generator->getGeneratorOptionForOption($option)) instanceof BundleGeneratorOptionInterface) {
            $optionGenerator = new BundleGeneratorOption();
            $optionGenerator->setBundleGenerator($generator);
            $optionGenerator->setProductOption($option);
            $generator->addOption($optionGenerator);
        }

        return $optionGenerator;
    }

    private function clearOtherOptions(BundleGeneratorInterface $generator, array $optionsGenerator): void
    {
        foreach ($generator->getOptions() as $optionGenerator) {
            if (!in_array($optionGenerator, $optionsGenerator, true)) {
                $generator->removeOption($optionGenerator);
            }
        }
    }
}
