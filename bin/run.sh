#!/usr/bin/env sh

./composer.phar run migrate

php ./bin/worker.php
