<?php

namespace Business\Hyperf\Utils\Support\Facades\OpenAi;

use Business\Hyperf\Constants\Constant;
use GuzzleHttp\RequestOptions;
use Hyperf\Utils\Arr;

class Completions extends BaseOpenAi
{

    public static function createCompletion($options = [], $headers = [])
    {
        $url = 'completions';
        $options = [
            RequestOptions::JSON => Arr::collapse([
                [//json

                    "model" => 'text-davinci-003',//model:https://platform.openai.com/docs/models/gpt-3   text-davinci-003 text-curie-001 text-babbage-001 text-ada-001
                    "prompt" => "Say this is a test",//提示
                    "temperature" => 1,//温度（预测人性化程度：值越大就越人性化）是一个介于 0 和 1 之间的值，基本上可以让您控制模型在进行这些预测时的置信度。降低温度意味着它将承担更少的风险，并且完成将更加准确和确定。升高温度将导致更多样化的完成。
                    "max_tokens" => 2048,//单个请求（提示和完成）中处理的令牌总数不能超过模型的最大上下文长度。对于大多数模型，这是 2,048 个标记或大约 1,500 个单词
                ],
                $options
            ])
        ];
        $method = 'POST';

        $choices = [];
        $finishReason = [];

        beginning:

        $responseData = static::request($url, $options, $method, $headers);

        $responseStatusCode = data_get($responseData, Constant::RESPONSE_STATUS_CODE);
        if ($responseStatusCode !== 200) {
            return $choices;
        }

        $responseBody = data_get($responseData, Constant::RESPONSE_BODY);
        $responseBody = static::unpack($responseBody);

        $choices = Arr::collapse([$choices, data_get($responseBody, 'choices.*.text', [])]);
        $finishReason = Arr::collapse([$finishReason, data_get($responseBody, 'choices.*.finish_reason', [])]);

        if (!in_array('stop', $finishReason)) {//如果当前会话没有回答完整，就继续请求
            goto beginning;
        }

        return $choices;
    }

}
