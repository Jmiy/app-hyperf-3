<?php

namespace Business\Hyperf\Utils\Support\Facades\OpenAi;

use Business\Hyperf\Service\Traits\Base;
use Business\Hyperf\Service\Traits\BaseClient;
use GuzzleHttp\RequestOptions;
use Hyperf\Utils\Arr;

class BaseOpenAi
{

    use Base,
        BaseClient;

    public static $url = 'https://api.openai.com/v1/';

    public static function getHeaders(string $openAiApiKey = '', ?array $headers = [])
    {
        return Arr::collapse([
            [
                'Authorization' => 'Bearer ' . $openAiApiKey,
            ],
            $headers
        ]);
    }

    /**
     * 接口请求
     * @param string $url 请求的url
     * @param array|null $options 请求选项
     * @param string|null $method 请求方式
     * @param array|null $headers 请求头
     * @param array|null $config GuzzleHttp\Client配置
     * @param array|null $poolHandlerOption 连接池(Hyperf\Guzzle\PoolHandler) 配置
     * @param array|array[]|null $handlerStackMiddlewares 重试中间件，默认：每隔 10 milliseconds 重试一次，重试1次
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
//    #[HandleTaskData]
    public static function request(
        string $url,
        ?array $options = [],
        ?string $method = 'POST',
        ?array $headers = [],
        ?array $config = [],
        ?array $poolHandlerOption = [],
        ?array $handlerStackMiddlewares = []
    ): array|null
    {

        $url = static::$url . $url;

        //获取open ai 的api key
        $openAiApiKeys = config('open_ai.api_key', ['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']);
        $headers = static::getHeaders(Arr::first($openAiApiKeys), $headers);

        //代理
        $proxyData = config('common.proxy', []);
        $proxy = Arr::first($proxyData);

        $options = Arr::collapse(
            [
                [
//                    RequestOptions::PROXY => 'http://bluelans:JmLNakRKftcZ@192.168.88.23:18787',
                    //on_stats
//                    RequestOptions::ON_STATS => function (\GuzzleHttp\TransferStats $transferStats) use (&$responseData) {//请求回调方法
////                        var_dump($transferStats->getEffectiveUri()->__toString());
////                        var_dump($transferStats->getTransferTime());
////                        var_dump($transferStats->getHandlerStats());//curl_getinfo  (注意：stream=true 时 使用stream 发起请求 无法获取详细的响应数据)
////                        var_dump(\GuzzleHttp\Psr7\Message::toString($transferStats->getRequest()));
////                        $request = $transferStats->getRequest();
////                        $data = [
////                            'TransferTime' => $transferStats->getTransferTime(),//请求时间
////                            'transferStats' => $transferStats->getHandlerStats(),//curl_getinfo  (注意：stream=true 时 使用stream 发起请求 无法获取详细的响应数据)
////                            'requestMethod' => $request->getMethod(),//请求方式
////                            'requestUri' => $request->getUri(),//请求uri
////                            'requestProtocolVersion' => $request->getProtocolVersion(),//协议版本
////                            'requestHeaders' => $request->getHeaders(),//请求头
////                            'requestBody' => $request->getBody()->__toString(),//请求body
////                        ];
////                        var_dump($data);
////
////                        if ($transferStats->hasResponse()) {
////                            $response = $transferStats->getResponse();
////                            var_dump('hasResponse');
////                            var_dump($response->getStatusCode());
////
////                        } else {
////                            // Error data is handler specific. You will need to know what
////                            // type of error data your handler uses before using this
////                            // value.
////                            var_dump('!!!!!!!!!hasResponse');
////                            var_dump($transferStats->getHandlerErrorData());
////                        }
//
//                        $responseData[Constant::TRANSFER_TIME] = $transferStats->getTransferTime();
//                        $responseData[Constant::TRANSFER_STATS] = $transferStats->getHandlerStats();
//
//                    },
                ],
                $options
            ]);

        if ($proxy) {//如果使用代理，就设置代理（原则是不覆盖 $options 设置的代理）
            data_set($options, RequestOptions::PROXY, $proxy, false);
        }

        return static::httpRequest($url, $options, $method, $headers, $config, $poolHandlerOption, $handlerStackMiddlewares);

    }

}
