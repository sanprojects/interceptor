<?php

$phar = new Phar('interceptor.phar', 0, 'interceptor.phar');
$phar->buildFromDirectory(__DIR__, '/\.php$/');
$phar->setDefaultStub('interceptor.php', 'interceptor.php');
$phar->stopBuffering();
echo "Phar archive created successfully.";
