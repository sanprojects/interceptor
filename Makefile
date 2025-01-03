.ONESHELL:
.IGNORE:
.EXPORT_ALL_VARIABLES:

test:
	docker run --rm -ti -d --name=mysql -p3306:3306 -e MYSQL_ROOT_PASSWORD=toor yobasystems/alpine-mariadb
	docker run --rm -ti -d --name=redis -p6379:6379 redis
	docker run --rm -ti -d --name kafka -p9092:9092 apache/kafka:latest
	./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests || exit

install:
	composer install

update:
	composer update

tag:
	# get highest tag number
	VERSION=`git describe --abbrev=0 --tags`
	# replace . with space so it can split into an array
	VERSION_BITS=($${VERSION//./ })
	# get number parts and increase last one by 1
	VNUM1=$${VERSION_BITS[0]}
	VNUM2=$${VERSION_BITS[1]}
	VNUM3=$${VERSION_BITS[2]}
	VNUM3=$$((VNUM3+1))
	# create new tag
	NEW_TAG="$$VNUM1.$$VNUM2.$$VNUM3"
	echo "Updating $$VERSION to $$NEW_TAG"
	git tag $$NEW_TAG
	echo "Tagged with $$NEW_TAG"

php-cs-fixer:
	vendor/bin/php-cs-fixer fix -vvv --diff --using-cache=no --no-interaction --allow-risky=yes

phar:
	rm interceptor.phar*
	php -d phar.readonly=off create-phar.php
	chmod a+x interceptor.phar*
	ls -la | grep phar