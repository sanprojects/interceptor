# Interceptor
Intercepts external request in php scripts, and log it to `STDERR`. 
So you can better understand what doing your program. 

## Installation
```shell script
composer require sanprojects/interceptor:dev-main
```
## Basic Usage:
```php
// intercept newly included files
if (($_REQUEST['interceptor'] ?? '') || in_array('interceptor', $_SERVER['argv'] ?? [])) {
    Interceptor::interceptAll();
}
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
It use `stream_wrapper_register` to on the fly intercept included php files 
and `stream_filter_register` for rewrite source code.

##Support: 
curl, fwrite, fread, file_get_contents, file_put_contents, mysqli, Redis, PDO, AMQP.

## Caveats
- It turns off opcache.
- Because of source code injection, it can crush your app. Not all cases tested.

Use it only for debug environment.