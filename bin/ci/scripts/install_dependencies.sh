#!/usr/bin/env bash

composer self-update
composer install
composer require doctrine/couchdb:dev-master phpfastcache/phpssdb:~1.1.0 predis/predis:~1.1.0 mongodb/mongodb:^1.9
