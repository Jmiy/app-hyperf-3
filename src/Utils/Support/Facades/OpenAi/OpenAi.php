<?php

namespace Business\Hyperf\Utils\Support\Facades\OpenAi;

use Business\Hyperf\Service\Traits\Base;

class OpenAi
{
    use Base;

    /**
     * OpenAi接口统一入口
     * @param string $method
     * @param array $args
     * @return array|mixed|null
     */
    public static function __callStatic(string $method, array $args)
    {
        $serviceData = [
            Completions::class,
            Models::class,
            Edits::class,
        ];

        foreach ($serviceData as $service) {
            if (method_exists($service, $method)) {
                return call([$service, $method], $args);
            }
        }

        return [];
    }

}
