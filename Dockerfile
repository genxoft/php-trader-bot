FROM php:8.1-cli-alpine

RUN apk update && apk upgrade --available && sync
RUN apk --no-cache add --virtual .ext-deps libmcrypt-dev postgresql-dev autoconf build-base

RUN docker-php-source extract \
  && docker-php-ext-install \
    bcmath \
    pcntl \
  && pecl install trader \
  && docker-php-ext-enable trader \
  && docker-php-source delete

RUN mkdir /app
WORKDIR /app
ADD ./ /app
RUN rm -rf ./vendor
RUN rm -rf ./data/*

RUN chmod +x ./composer.phar
RUN chmod +x ./bin/run.sh

RUN ./composer.phar install --no-dev --no-interaction

ENTRYPOINT ["./bin/run.sh"]
