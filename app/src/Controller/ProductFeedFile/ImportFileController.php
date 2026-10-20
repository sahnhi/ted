<?php

declare(strict_types=1);

namespace App\Controller\ProductFeedFile;

use App\Processor\ProductFeedFile\ProductFeedFileImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ImportFileController extends AbstractController
{
    public function __construct(
        private readonly ProductFeedFileImporter $productFeedFileImporter,
    ) {
    }

    /**
     * @Route("/admin/product-feed-file/import-file", name="app_product_feed_file_import_file")
     */
    public function __invoke(Request $request): Response
    {
        $user = 'web';
        $file = $request->files->get('fileToUpload')->getPathName();
        $this->productFeedFileImporter->import($file, $user);

        return $this->redirectToRoute('app_admin_product_feed_file_index');
    }
}
