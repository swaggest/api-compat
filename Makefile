phar:
	composer install --no-dev;rm -rf tests/;rm ./api-compat;rm ./api-compat.tar.gz;phar-composer build;mv ./api-compat.phar ./api-compat;tar -zcvf ./api-compat.tar.gz ./api-compat;composer install
