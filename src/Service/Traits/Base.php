<?php

/**
 * Base trait
 * User: Jmiy
 * Date: 2020-09-03
 * Time: 09:27
 */

namespace Business\Hyperf\Service\Traits;

use Business\Hyperf\Constants\Constant;
use Business\Hyperf\Utils\Curl;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Arr;

trait Base
{

    /**
     * 获取类名
     * @return string
     */
    public static function getCustomClassName()
    {
        $class = explode('\\', get_called_class());
        $trans = array("Service" => "");
        return strtr(end($class), $trans);
    }

    /**
     * 获取当前类的绝对路径
     * @return string
     */
    public static function getNamespaceClass()
    {
        return implode('', ['\\', static::class]);
    }

    /**
     * 获取服务提供者
     * @param string $platform 平台
     * @param string|array $serviceProvider 服务提供者
     * @return string 服务
     */
    public static function getServiceProvider(string $platform = '', string|array $serviceProvider = [])
    {
        $class = explode('\\', get_called_class());
        $trans = [
            '\\' . end($class) => ""
        ];

        $serviceData = Arr::collapse(
            [
                [strtr(static::getNamespaceClass(), $trans), $platform],
                (is_array($serviceProvider) ? $serviceProvider : [$serviceProvider])
            ]
        );

        return implode('\\', array_filter($serviceData));
    }

    /**
     * 执行服务
     * @param string $platform 平台
     * @param string|array $serviceProvider 服务提供者
     * @param string $method 执行方法
     * @param array|null $parameters 参数
     * @return mixed
     */
    public static function managerHandle(string $platform = '', string|array $serviceProvider = '', string $method = '', ?array $parameters = []): mixed
    {
        $service = static::getServiceProvider($platform, $serviceProvider);
//        if (!($service && $method && method_exists($service, $method))) {
//            return null;
//        }

        return call([$service, $method], $parameters);
    }

    /**
     * 包装数据
     * @param mixed $data 要包装的数据
     * @return false|string
     */
    public static function pack(mixed $data): string|false
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 解析数据
     * @param mixed $data 要解析的数据
     * @return mixed
     */
    public static function unpack(mixed $data): mixed
    {
        return json_decode($data, true);
    }

    /**
     * 强制转换为字符串
     * @param mix $value
     * @return string $value
     */
    public static function castToString($value)
    {
        return (string)$value;
    }

    /**
     * @param string $connection 数据库连接
     * @return mixed|null
     */
    public static function getServiceEnv($connection = 2)
    {
        $request = getApplicationContainer()->get(RequestInterface::class);
        $appEnv = $request->input('app_env', null); //开发环境 $request->route('app_env', null)
        return $appEnv === null ? $connection : ($appEnv . '_' . $connection);
    }

}
