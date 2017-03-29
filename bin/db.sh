#!/usr/bin/env bash

php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load --fixtures=src/AuthBundle/DataFixtures/ORM/LoadRoles.php --append
php bin/console doctrine:fixtures:load --fixtures=src/AuthBundle/DataFixtures/ORM/LoadAdmin.php --append
