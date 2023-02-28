<?php
declare(strict_types=1);

namespace App\Log;

use Psr\Container\ContainerInterface;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return Loger::get('sys','sys');
    }
}
