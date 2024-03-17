#!/bin/sh

composer install && \
php services/consumer.php > /dev/null & \
php-fpm
