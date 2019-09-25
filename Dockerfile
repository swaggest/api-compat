FROM php:cli

RUN apt-get update && apt-get install -y git && rm -rf /var/lib/apt/lists/*

COPY ./api-compat /bin/api-compat

WORKDIR /code