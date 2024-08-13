<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TiktokElementListener
{
    private Connection $database;

    private HttpClientInterface $httpClient;

    private ?LoggerInterface $logger;

    private array $responseCache = [];

    public function __construct(Connection $database, HttpClientInterface $httpClient, ?LoggerInterface $logger = null)
    {
        $this->database = $database;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @Callback(table="tl_content", target="fields.oembed_url.save")
     */
    public function onSaveCallback(string $value, DataContainer $dc): string
    {
        if (
            !$dc->activeRecord
            || 'oembed_tiktok' !== $dc->activeRecord->type
            || empty($dc->activeRecord->oembed_url)
        ) {
            return $value;
        }

        try {
            $this->getHtmlForUrl($value);
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info($e->getMessage(), ['exception' => $e]);
            }

            throw new \RuntimeException(\sprintf($GLOBALS['TL_LANG']['ERR']['tiktok_url'], $e->getCode()));
        }

        return $value;
    }

    /**
     * @Callback(table="tl_content", target="config.onsubmit")
     */
    public function onSubmitCallback(DataContainer $dc): void
    {
        if (
            !$dc->activeRecord
            || 'oembed_tiktok' !== $dc->activeRecord->type
            || empty($dc->activeRecord->oembed_url)
        ) {
            return;
        }

        try {
            $html = $this->getHtmlForUrl($dc->activeRecord->oembed_url);

            $this->database->update(
                'tl_content',
                [
                    'html' => $html,
                ],
                ['id' => $dc->id],
            );
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @throws \Exception
     */
    private function getHtmlForUrl(string $url): string
    {
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
}
