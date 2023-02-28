<?php

namespace Business\Hyperf\Utils\Support\Facades\OpenAi;

use GuzzleHttp\RequestOptions;
use Hyperf\Utils\Arr;

class Images extends BaseOpenAi
{
    /**
     * Given a prompt and/or an input image, the model will generate a new image.
     * (https://platform.openai.com/docs/api-reference/images/create)
     * @param string $prompt Required (A text description of the desired image(s). The maximum length is 1000 characters.)
     * @param int|null $n Optional Defaults to 1 (The number of images to generate. Must be between 1 and 10.)
     * @param string|null $size Optional Defaults to 1024x1024(The size of the generated images. Must be one of 256x256, 512x512, or 1024x1024.)
     * @param string|null $responseFormat Optional Defaults to url(The format in which the generated images are returned. Must be one of url or b64_json)
     * @param string|null $user Optional (A unique identifier representing your end-user, which can help OpenAI to monitor and detect abuse)
     * @param array|null $options
     * @param array $headers
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function createImage(
        string $prompt = '',
        ?int $n = 1,
        ?string $size = '1024x1024',
        ?string $responseFormat = 'url',
        ?string $user = '',
        ?array $options = [],
        ?array $headers = []
    )
    {
        $url = 'images/generations';
        $options = [
            RequestOptions::JSON => Arr::collapse([
                [
                    "prompt" => $prompt,
                    "size" => $size,
                    "n" => $n,
                    "response_format" => $responseFormat,
//                    "user" => $user,
                ],
                $options
            ])
        ];
        $method = 'POST';

        return static::request($url, $options, $method, $headers);
    }

    /**
     * Creates an edited or extended image given an original image and a prompt.
     * (https://platform.openai.com/docs/api-reference/images/create-edit)
     * @param string $imageFile Required (The image to edit. Must be a valid PNG file, less than 4MB, and square. If mask is not provided, image must have transparency, which will be used as the mask.)
     * @param string $prompt Required (A text description of the desired image(s). The maximum length is 1000 characters.)
     * @param string|null $maskFile Optional (An additional image whose fully transparent areas (e.g. where alpha is zero) indicate where image should be edited. Must be a valid PNG file, less than 4MB, and have the same dimensions as image.)
     * @param int|null $n Optional Defaults to 1 (The number of images to generate. Must be between 1 and 10.)
     * @param string|null $size Optional Defaults to 1024x1024(The size of the generated images. Must be one of 256x256, 512x512, or 1024x1024.)
     * @param string|null $responseFormat Optional Defaults to url(The format in which the generated images are returned. Must be one of url or b64_json)
     * @param string|null $user Optional (A unique identifier representing your end-user, which can help OpenAI to monitor and detect abuse)
     * @param array|null $options
     * @param array|null $headers
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function editImage(
        string $imageFile,
        string $prompt = '添加  鲨鱼',
        ?string $maskFile = '',
        ?int $n = 1,
        ?string $size = '1024x1024',
        ?string $responseFormat = 'url',
        ?string $user = '',
        ?array $options = [],
        ?array $headers = []
    )
    {
        $url = 'images/edits';
        $options = [
            RequestOptions::MULTIPART => [//表单上传文件  Sending form files Sets (the body of the request to a multipart/form-data form.)
                [
                    'name' => 'image',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($imageFile, 'r'),
//                    'filename' => 'img-x9ivHnpi9DKQHAnOPfx3aVPA.png',
                ],
                [
                    'name' => 'prompt',
                    'contents' => $prompt
                ],
                [
                    'name' => 'n',
                    'contents' => $n
                ],
                [
                    'name' => 'size',
                    'contents' => $size
                ],
                [
                    'name' => 'response_format',
                    'contents' => $responseFormat
                ],
                [
                    'name' => 'user',
                    'contents' => $user
                ]
            ],
        ];

        if ($maskFile) {
            $options[RequestOptions::MULTIPART][] = [
                'name' => $maskFile,
                'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($maskFile, 'r'),
            ];
        }

        $method = 'POST';

        return static::request($url, $options, $method, $headers);
    }

}
