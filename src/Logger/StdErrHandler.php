<?php declare(strict_types=1);

namespace Sanprojects\Interceptor\Logger;

use Monolog\Handler\StreamHandler;

/**
 * Stores to any stream resource
 *
 * Can be used to store into php://stderr, remote and local files, etc.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class StdErrHandler extends StreamHandler
{
    public function __construct()
    {
        parent::__construct('php://stderr');
        $this->setFormatter(new LineFormatter(null, null, true,  true));
    }
}
