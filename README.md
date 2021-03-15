# Interceptor
Intercept php functions

## Usage
```shell script
composer require sanprojects/interceptor
```
In php bootstrap:
```php
// intercept newly included files
Interceptor::interceptAll();
```