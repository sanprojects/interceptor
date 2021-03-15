#!/usr/bin/env bash

set -o pipefail
set -o nounset

help() {
  echo "Usage: $0 COMMAND" >&2
  echo "Commands:"
  declare -F | sed "s/declare -f/ /g"
}

test() {
    ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests || exit
}

install() {
    composer install --ignore-platform-reqs
}

${1:-help} || help