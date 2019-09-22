FROM php:cli

COPY ./api-compat /bin/api-compat

WORKDIR /code