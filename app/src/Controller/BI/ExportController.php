<?php

namespace App\Controller\BI;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private ProductReviewRepository $productReviewRepository
    ) {
    }

    public function indexAction(): Response
    {
        return $this->render('@App/Export/index.html.twig');
    }

    public function exportProductReviewAction(): StreamedResponse
    {
        $repository = $this->productReviewRepository;

        $response = new StreamedResponse(function () use ($repository): void {
            $results = $repository->findAll();
            $handle = fopen('php://output', 'w+');

            fputcsv($handle, ['ID avis', 'N° commande', 'Produit', 'Note', 'Commentaire', 'Date de création', 'Traduit', 'Statut', 'Langue', 'Avant Tediber', 'Position', 'Nb dormeur'], ';');

            foreach ($results as $review) {
                $order = $review->getOrder();
                $product = $review->getReviewSubject();
                fputcsv($handle, [$review->getId(), isset($order) ? $order->getNumber() : null, $product->getName(), $review->getRating(), $review->getComment(), $review->getCreatedAt()->format('Y-m-d H:i:s'), isset($review->getParent) ? 1 : 0, $review->getStatus(), $review->getLocale(), '', '', '']);
            }

            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-product-review.csv"');

        return $response;
    }
}
