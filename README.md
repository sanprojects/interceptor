# Interceptor
Intercepts external request in php scripts, and log it to `STDERR`. 
So you can better understand what doing your program. 

## Installation
```shell script
composer require sanprojects/interceptor
```
## Basic Usage:
```php
// Intercept all kind of requests
\Sanprojects\Interceptor\Interceptor::interceptAll();
```
Now in the console you'll see something like this:
```
Interceptor.DEBUG: curl -vX POST 'https://www.example.com/' \ 
 --data 'postvar1=value1&postvar2=value2&postvar3=value3'
Interceptor.DEBUG: CURL> <!doctype html> ...
Interceptor.DEBUG: fopen /Users/san/PhpstormProjects/interceptor/tests/test.txt w+b [resource(stream)] 
Interceptor.DEBUG: fwrite /Users/san/PhpstormProjects/interceptor/tests/test.txt test 4 
Interceptor.DEBUG: fread /Users/san/PhpstormProjects/interceptor/tests/test.txt 100 test 
Interceptor.DEBUG: file_put_contents /Users/san/PhpstormProjects/interceptor/tests/test.txt test 4 
Interceptor.DEBUG: Redis::__construct NULL 
Interceptor.DEBUG: Redis tcp://127.0.0.1:6379 set test {"jsonKey":123} Predis\Response\Status 
Interceptor.DEBUG: Redis tcp://127.0.0.1:6379 get test {"jsonKey":123} 
Interceptor.DEBUG: mysqli_connect ensembldb.ensembl.org anonymous  mysqli 
Interceptor.DEBUG: mysqli_query ensembldb.ensembl.org SELECT 123 mysqli_result 
Interceptor.DEBUG: PDO::__construct mysql:dbname=;host=ensembldb.ensembl.org anonymous  NULL 
Interceptor.DEBUG: PDO::query SELECT 123 Sanprojects\Interceptor\Hooks\PDOStatement 
Interceptor.DEBUG: PDOStatement::execute SELECT 123; true
```

## How it works
It use `stream_wrapper_register` to intercept included php files 
and `stream_filter_register` for rewrite source code.

## Support 
curl, fwrite, fread, file_get_contents, file_put_contents, mysqli, Redis, PDO, AMQP.

## Caveats
- It turns off opcache.
- Because of source code injection, it can crush your app. Not all cases tested.

## How to inject
Option 1: Run interceptor.phar
```bash
curl -O https://sanprojects.github.io/interceptor/interceptor.phar
php -d opcache.enable=0 interceptor.phar <yourScript.php>
```

Option 2: Use `auto_prepend_file`
```bash
curl -O https://sanprojects.github.io/interceptor/interceptor.phar
php -d opcache.enable=0 -d auto_prepend_file=interceptor.php <yourScript.php>
```

Option 1: Include in your script
```php
\Sanprojects\Interceptor\Interceptor::interceptAll();
```
Option 2: Include php file in your script
```php
require 'vendor/bin/interceptor.php';
```

Use it only for debug environment.