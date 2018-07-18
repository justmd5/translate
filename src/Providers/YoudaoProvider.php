<?php

namespace Yan\Translate\Providers;

use Yan\Translate\Contracts\ProviderInterface;
use Yan\Translate\Exceptions\TranslateException;
use Yan\Translate\Translate;

class YoudaoProvider extends AbstractProvider implements ProviderInterface
{
    const HTTP_URL = 'http://openapi.youdao.com/api';

    const HTTPS_URL = 'https://openapi.youdao.com/api';

    protected function getRequestParams(array $args): array
    {
        list($q, $from, $to) = $args;

        $salt = time();

        $params = [
            'q' => $q,
            'from' => $from ?? 'zh',
            'to' => $to ?? 'en',
            'appKey' => $this->appId,
            'salt' => $salt,
            'ext' => 'mp3',
            'voice' => 0,
        ];

        $params['sign'] = $this->makeSignature($params);

        return $params;
    }

    protected function makeSignature(array $params): string
    {
        return md5($this->appId.$params['q'].$params['salt'].$this->appKey);
    }

    /**
     * {@inheritdoc}
     */
    public function translate(string $q, $from = 'zh', $to = 'en')
    {
        $response = $this->post($this->getTranslateUrl(), $this->getRequestParams(func_get_args()));

        if ('0' != $response['errorCode']) {
            throw new TranslateException("请求接口错误，错误码：{$response['errorCode']}", $response['errorCode']);
        }

        return new Translate($this->mapTranslateResult($response));
    }

    protected function mapTranslateResult(array $translateResult): array
    {
        return [
            'src' => $translateResult['query'],
            'dst' => $translateResult['translation'],
            'original' => $translateResult,
        ];
    }
}