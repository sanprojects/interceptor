#!/usr/bin/env bash

set -o pipefail
set -o nounset

help() {
  echo "Usage: $0 COMMAND" >&2
  echo "Commands:"
  declare -F | sed "s/declare -f/ /g"
}

test() {
    docker run --rm -ti -d --name=mysql -p3306:3306 -e MYSQL_ROOT_PASSWORD=toor yobasystems/alpine-mariadb
    docker run --rm -ti -d --name=redis -p6379:6379 redis
    ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests || exit
}

install() {
    composer install
}

update() {
    composer update
}

${1:-help} "${@:2}" || help