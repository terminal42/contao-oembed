<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InstagramElementListener
{
    private Connection $database;
    private HttpClientInterface $httpClient;
    private string $facebookToken;
    private ?LoggerInterface $logger;
    private array $responseCache = [];

    public function __construct(Connection $database, HttpClientInterface $httpClient, string $facebookToken, LoggerInterface $logger = null)
    {
        $this->database = $database;
        $this->httpClient = $httpClient;
        $this->facebookToken = $facebookToken;
        $this->logger = $logger;
    }

    /**
     * @Callback(table="tl_content", target="fields.instagram_url.save")
     */
    public function onSaveCallback($value)
    {
        try {
            $this->getHtmlForQuery($this->prepareQuery(
                (object) ['instagram_url' => $value]
            ));
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info($e->getMessage(), ['exception' => $e]);
            }

            throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['instagram_url'], $e->getCode()));
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
            || 'embedded_instagram_post' !== $dc->activeRecord->type
            || '' === $dc->activeRecord->instagram_url
        ) {
            return;
        }

        try {
            $html = $this->getHtmlForQuery(
                $this->prepareQuery($dc->activeRecord)
            );

            $this->database->update(
                'tl_content',
                [
                    'html' => $html,
                ],
                ['id' => $dc->id]
            );
        } catch (\Exception $e) {
            return;
        }
    }

    private function prepareQuery(object $data): array
    {
        $query = [
            'url' => $data->instagram_url,
            'omitscript' => '1',
        ];

        if ($data->instagram_maxwidth ?? null) {
            $query['maxwidth'] = $data->instagram_maxwidth;
        }

        if ($data->instagram_hidecaption ?? false) {
            $query['hidecaption'] = '1';
        }

        return $query;
    }

    /**
     * @throws \Exception
     */
    private function getHtmlForQuery(array $query): string
    {
        $parsedQuery = http_build_query($query);
        $hash = md5($parsedQuery);

        if (!isset($this->responseCache[$hash])) {
            $response = $this->httpClient->request(
                'GET',
                'https://graph.facebook.com/v11.0/instagram_oembed?'.$parsedQuery,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->facebookToken,
                    ],
                ]
            );

            if (($status = $response->getStatusCode()) < 200 || $status > 301) {
                throw new \RuntimeException('Invalid Instagram response: '.$response->getContent(false), $status);
            }

            $this->responseCache[$hash] = $response->toArray()['html'];
        }

        return $this->responseCache[$hash];
    }
}
