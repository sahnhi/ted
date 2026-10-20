#!/bin/bash

echo "Fix migrations errors"

bin/console doctrine:migrations:sync-metadata-storage
bin/console doctrine:migrations:version "Sylius\Bundle\CoreBundle\Migrations\Version20161209095131" --add --no-interaction
