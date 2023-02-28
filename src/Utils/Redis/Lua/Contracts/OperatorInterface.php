<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Business\Hyperf\Util\Redis\Lua\Contracts;

interface OperatorInterface
{
    public function getScript(): string;

    public function parseResponse($data);
}
