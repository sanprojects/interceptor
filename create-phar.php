<?php

$phar = new Phar('interceptor.phar', 0, 'interceptor.phar');
$phar->buildFromDirectory(__DIR__, '/^(?!.*vendor).*php$/');
$phar->setDefaultStub('interceptor.php', 'interceptor.php');
$phar->compress(Phar::GZ);
$phar->stopBuffering();
echo 'Phar archive created successfully.';
