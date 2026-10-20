<?php

namespace App\Api\Odoo\Controller;

use App\Api\Odoo\Model\Product as ProductModel;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionTranslation;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use App\Doctrine\Entity\Sylius\ProductOptionValueTranslation;
use App\Model\DefaultLocale;
use App\Services\Mail\Mailer\ProductUpdatedAlert;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Repository\ProductRepositoryInterface;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Product extends AbstractOdooController
{
    public function __construct(
        string $apiKey,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductFactoryInterface $productFactory,
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly TaxonFactoryInterface $taxonFactory,
        private readonly Factory $productTaxonFactory,
        private readonly RepositoryInterface $productOptionRepository,
        private readonly EntityRepository $productOptionValueRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DefaultLocale $defaultLocale,
        LoggerInterface $odooLogger,
        private readonly ProductUpdatedAlert $productUpdatedAlert,
        private readonly FactoryInterface $productOptionTranslationFactory,
        private readonly FactoryInterface $productOptionFactory,
    ) {
        parent::__construct($odooLogger, $apiKey);
    }

    public function __invoke(Request $request): Response
    {
        $this->logRequest($request);

        try {
            $this->authenticate($request);
            $productModel = new ProductModel($request);
            $locale = $this->defaultLocale->getLocale();

            $taxon = $this->taxonRepository->findOneBy(['code' => strtolower($productModel->taxonomy)]);

            if (!$taxon) {
                $taxon = $this->taxonFactory->createNew();
                $taxon->setCode(strtolower($productModel->taxonomy));
                $taxon->setName($productModel->taxonomy);
                $taxon->setSlug(strtolower($productModel->taxonomy));

                $this->entityManager->persist($taxon);
            }

            /** @var ProductInterface $product */
            $product = $this->productRepository->findOneBy(['code' => strtolower($productModel->code)]);
            $productTaxon = null;

            $new = false;

            if (!$product) {
                $new = true;
                $product = $this->productFactory->createNew();
                $productTaxon = $this->productTaxonFactory->createNew();
                $productTaxon->setTaxon($taxon);
                $product->setEnabled(false);
                $product->setWeighting(1);
            }
            $product->setName($productModel->name);
            $product->setCode(strtolower($productModel->code));
            $product->setSlug(strtolower($productModel->slug));
            $product->setMainTaxon($taxon);
            $product->setCanGenerateBundle(true);

            if (null !== $productTaxon) {
                $productTaxon->setProduct($product);
                $this->entityManager->persist($productTaxon);
            }

            /* ProductOption */
            if (!empty($productModel->options)) {
                foreach ($productModel->options as $key => $opt) {
                    $option = $this->productOptionRepository->findOneBy([
                        'code' => $opt['code'],
                    ]);

                    if (!$option) {
                        /** @var ProductOption $option */
                        $option = $this->productOptionFactory->createNew();
                        /** @var ProductOptionTranslation $translation */
                        $translation = $this->productOptionTranslationFactory->createNew();
                        $option->setCode($opt['code']);
                        $option->setPosition((int) $key);
                        $translation->setLocale($locale);
                        $translation->setName($opt['name']);
                        $option->setCreatedAt(new \DateTime());
                        $option->setUpdatedAt(new \DateTime());
                        $option->addTranslation($translation);

                        $this->entityManager->persist($option);
                    } else {
                        $translation = $option->getTranslation($locale);
                        $translation->setName($opt['name']);
                    }

                    $product->addOption($option);

                    /* ProductOptionValue */
                    foreach ($opt['values'] as $value) {
                        $optionValue = $this->productOptionValueRepository->findOneBy([
                            'code' => $value['value'],
                            'option' => $option,
                        ]);

                        if (str_contains((string) $value['value'], ' ')) {
                            return new Response('Option code ' . $value['value'] . ' should not contain space', Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                        if (!$optionValue) {
                            $optionValue = new ProductOptionValue();
                            $optionValue->setCode($value['value']);
                            $optionValue->setOption($option);
                            $optionValue->setEnabled(false);

                            $translation = new ProductOptionValueTranslation();
                            $translation->setLocale($locale);
                            $translation->setValue($value['name']);
                            $optionValue->addTranslation($translation);
                            $this->entityManager->persist($optionValue);
                        }
                    }
                }
            }

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            if (!$new) {
                $this->productUpdatedAlert->generateEmail(ProductUpdatedAlert::TYPE_PRODUCT, $product->getCode(), 'Odoo');
            }
        } catch (\Exception $exception) {
            parent::logError($request, $exception->getCode(), $exception->getMessage());

            return new Response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('ok', Response::HTTP_OK);
    }
}
