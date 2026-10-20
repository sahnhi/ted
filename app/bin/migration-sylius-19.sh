#!/bin/bash

echo "Adding executed migrations from Sylius Invoicing Plugin"

bin/console doctrine:migrations:sync-metadata-storage
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20180626120743" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20180711070226" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20180711084815" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20180801083047" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20180826064735" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20190103134228" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20190604071441" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20190607001446" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20190607002945" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20191016124548" --add --no-interaction
bin/console doctrine:migrations:version "Sylius\InvoicingPlugin\Migrations\Version20191216134233" --add --no-interaction

echo "Adding executed adjustments taxation details from Sylius"

bin/console doctrine:migrations:version 'Sylius\Bundle\CoreBundle\Migrations\Version20201208105207' --add --no-interaction
