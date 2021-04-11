# Interceptor
Intercept php functions

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