<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Log\LoggerInterface;

class InstagramElementListener
{
    private Connection $database;
    private ?LoggerInterface $logger;
    private ?HttpClient $httpClient;
    private MessageFactory $requestFactory;
    private array $responseCache;

    public function __construct(Connection $database, LoggerInterface $logger = null, HttpClient $httpClient = null, MessageFactory $messageFactory = null)
    {
        $this->database = $database;
        $this->logger = $logger;

        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $messageFactory ?: MessageFactoryDiscovery::find();
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

    private function prepareQuery($data): array
    {
        $query = [
            'url' => $data->instagram_url,
            'omitscript' => '1',
        ];

        if ($data->instagram_maxwidth) {
            $query['maxwidth'] = $data->instagram_maxwidth;
        }

        if ($data->instagram_hidecaption) {
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
            $response = $this->httpClient->sendRequest(
                $this->requestFactory->createRequest('GET', 'https://api.instagram.com/oembed/?'.$parsedQuery)
            );

            if (($status = $response->getStatusCode()) < 200 || $status > 301) {
                throw new \RuntimeException('Invalid Instagram response: '.$response->getBody(), $status);
            }

            $json = json_decode((string) $response->getBody(), true);

            $this->responseCache[$hash] = $json['html'];
        }

        return $this->responseCache[$hash];
    }
}
