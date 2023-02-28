<?php

namespace Business\Hyperf\Utils\Support\Facades\OpenAi;

use GuzzleHttp\RequestOptions;

class Models extends BaseOpenAi
{

    public static function list(?array $headers = [])
    {
        $url = 'models';

        $options = [
            RequestOptions::QUERY => [],
        ];
        $method = 'GET';

        return static::request($url, $options, $method, $headers);
    }

    public static function retrieveModel(string $model = 'text-davinci-003', ?array $headers = [])
    {
        $url = 'models/' . $model;

        $options = [
            RequestOptions::QUERY => [],
        ];
        $method = 'GET';

        return static::request($url, $options, $method, $headers);
    }

}
