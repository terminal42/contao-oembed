<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TiktokElementListener extends AbstractElementListener
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly TranslatorInterface $translator,
        LoggerInterface|null $logger = null,
    ) {
        parent::__construct($logger);
    }

    protected function isSupported(array $activeRecord): bool
    {
        return 'oembed_tiktok' === $activeRecord['type']
            && !empty($activeRecord['oembed_url']);
    }

    protected function getHtml(array $activeRecord): string
    {
        $url = (string) $activeRecord['oembed_url'];

        if (!isset($this->responseCache[$url])) {
            $response = $this->httpClient->request(
                'GET',
                'https://www.tiktok.com/oembed?url='.urlencode($url),
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ],
            );

            if (($status = $response->getStatusCode()) < 200 || $status > 301) {
                throw new \RuntimeException('Invalid TikTok response: '.$response->getContent(false), $status);
            }

            $this->responseCache[$url] = $response->toArray()['html'];
        }

        return $this->responseCache[$url];
    }

    protected function throwOnWidgetError(\Exception $exception): never
    {
        throw new \RuntimeException($this->translator->trans('ERR.tiktok_url', [$exception->getCode()], 'contao_default'));
    }
}
