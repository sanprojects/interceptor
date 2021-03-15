# interceptor
Intercept php functions

## Usage
```
$transformer = new Interceptor();
$transformer->addHook([CurlHook::class, 'filter']);
$transformer->addHook([FileHook::class, 'filter']);
$transformer->addHook([PdoHook::class, 'filter']);
$transformer->intercept();
```