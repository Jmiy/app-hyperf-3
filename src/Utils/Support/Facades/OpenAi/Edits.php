<?php

namespace Business\Hyperf\Utils\Support\Facades\OpenAi;

use GuzzleHttp\RequestOptions;
use Hyperf\Utils\Arr;

class Edits extends BaseOpenAi
{

    /**
     * Given a prompt and an instruction, the model will return an edited version of the prompt. (https://platform.openai.com/docs/api-reference/edits/create)
     * @param string $model Required (ID of the model to use. You can use the text-davinci-edit-001 or code-davinci-edit-001 model with this endpoint.)
     * @param string $instruction Required (The instruction that tells the model how to edit the prompt.)
     * @param string|null $input Optional Defaults to ''(The input text to use as a starting point for the edit.)
     * @param int|null $n Optional Defaults to 1(How many edits to generate for the input and instruction.)
     * @param float|int|null $temperature Optional Defaults to 1(What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.We generally recommend altering this or top_p but not both.)
     * @param float|int|null $topP Optional Defaults to 1(An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered.We generally recommend altering this or temperature but not both.)
     * @param array|null $options 扩展参数
     * @param array $headers 请求头
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function createEdit(
        string $model = 'text-davinci-edit-001',
        string $instruction = '',
        ?string $input = '',
        ?int $n = 1,
        ?float $temperature = 1,
        ?float $topP = 1,
        ?array $options = [],
        $headers = []
    )
    {
        $url = 'edits';
        $options = [
            RequestOptions::JSON => Arr::collapse([
                [
                    "model" => $model,//string Required (ID of the model to use. You can use the text-davinci-edit-001 or code-davinci-edit-001 model with this endpoint.)
                    "input" => $input,//string Optional Defaults to ''(The input text to use as a starting point for the edit.)
                    "instruction" => $instruction,//string Required (The instruction that tells the model how to edit the prompt.)
                    "n" => $n,//integer Optional Defaults to 1(How many edits to generate for the input and instruction.)
                    "temperature" => $temperature,//number Optional Defaults to 1(What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.We generally recommend altering this or top_p but not both.)
                    "top_p" => $topP,//number Optional Defaults to 1(An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered.We generally recommend altering this or temperature but not both.)
                ],
                $options
            ])
        ];
        $method = 'POST';

        return static::request($url, $options, $method, $headers);
    }

}
