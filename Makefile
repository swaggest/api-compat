phar:
	composer install --no-dev;rm -rf tests/;rm ./api-compat;rm ./api-compat.tar.gz;phar-composer build;mv ./api-compat.phar ./api-compat;tar -zcvf ./api-compat.tar.gz ./api-compat;git reset --hard;composer install

docker56-composer-update:
	test -f ./composer.phar || wget https://getcomposer.org/composer.phar
	docker run -v $$(pwd):/code php:5.6-cli bash -c "apt-get update;apt-get install -y unzip;cd /code;php composer.phar update --prefer-source"

docker56-test:
	docker run -v $$(pwd):/code php:5.6-cli bash -c "cd /code;php vendor/bin/phpunit"
