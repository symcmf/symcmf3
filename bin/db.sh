#!/usr/bin/env bash

php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load
