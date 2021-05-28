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

tag() {
  #get highest tag number
  VERSION=`git describe --abbrev=0 --tags`

  #replace . with space so can split into an array
  VERSION_BITS=(${VERSION//./ })

  #get number parts and increase last one by 1
  VNUM1=${VERSION_BITS[0]}
  VNUM2=${VERSION_BITS[1]}
  VNUM3=${VERSION_BITS[2]}
  VNUM3=$((VNUM3+1))

  #create new tag
  NEW_TAG="$VNUM1.$VNUM2.$VNUM3"
  echo "Updating $VERSION to $NEW_TAG"

  git tag $NEW_TAG
  echo "Tagged with $NEW_TAG"
  #git push --tags
}

${1:-help} "${@:2}" || help