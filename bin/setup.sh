#!/usr/bin/env bash

echo "------- Start setup.sh -------- "
./bin/db.sh
php bin/console assets:install web/
