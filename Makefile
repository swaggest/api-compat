phar:
	@test -f $$HOME/.cache/composer/phar-composer.phar || (mkdir -p $$HOME/.cache/composer/ && wget https://github.com/clue/phar-composer/releases/download/v1.0.0/phar-composer.phar -O $$HOME/.cache/composer/phar-composer.phar)
	@composer install --no-dev;rm -rf tests/;rm ./api-compat;rm ./api-compat.tar.gz;php -d phar.readonly=off $$HOME/.cache/composer/phar-composer.phar build;mv ./api-compat.phar ./api-compat;tar -zcvf ./api-compat.tar.gz ./api-compat;composer install

docker-build:
	@docker build . -t swaggest/api-compat:$(shell git describe --abbrev=0 --tags)

docker-push:
	@docker push swaggest/api-compat

lint:
	@test -f $$HOME/.cache/composer/phpstan-0.11.8.phar || wget https://github.com/phpstan/phpstan/releases/download/0.11.8/phpstan.phar -O $$HOME/.cache/composer/phpstan-0.11.8.phar
	@php $$HOME/.cache/composer/phpstan-0.11.8.phar analyze -l 7 -c phpstan.neon ./src

docker-lint:
	@docker run -v $$PWD:/app --rm phpstan/phpstan analyze -l 7 -c phpstan.neon ./src

test:
	@php -derror_reporting="E_ALL & ~E_DEPRECATED" vendor/bin/phpunit

test-coverage:
	@php -derror_reporting="E_ALL & ~E_DEPRECATED" -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-text

docker56-composer-update:
	test -f ./composer.phar || wget https://getcomposer.org/composer.phar
	docker run -v $$(pwd):/code php:5.6-cli bash -c "apt-get update;apt-get install -y unzip;cd /code;php composer.phar update --prefer-source"

docker56-test:
	docker run -v $$(pwd):/code php:5.6-cli bash -c "cd /code;php vendor/bin/phpunit"
