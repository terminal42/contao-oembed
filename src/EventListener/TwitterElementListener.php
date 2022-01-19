<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TwitterElementListener
{
    private Connection $database;
    private HttpClientInterface $httpClient;
    private ?LoggerInterface $logger;
    private array $responseCache = [];

    public function __construct(Connection $database, HttpClientInterface $httpClient, LoggerInterface $logger = null)
    {
        $this->database = $database;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @Callback(table="tl_content", target="fields.twitter_url.save")
     */
    public function onSaveCallback($value, DataContainer $dc)
    {
        try {
            $this->getHtmlForQuery($this->prepareQueryForType(
                $dc->activeRecord->type,
                (object) ['twitter_url' => $value]
            ));
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info($e->getMessage(), ['exception' => $e]);
            }

            throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['twitter_url'], $e->getCode()));
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
            || '' === $dc->activeRecord->twitter_url
            || !\in_array($dc->activeRecord->type, ['embedded_tweet', 'user_timeline'], true)
        ) {
            return;
        }

        try {
            $html = $this->getHtmlForQuery(
                $this->prepareQueryForType($dc->activeRecord->type, $dc->activeRecord)
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

    private function prepareQueryForType($type, $data): array
    {
        $query = [
            'url' => $data->twitter_url,
            'omit_script' => true,
            'aria_polite' => 'assertive',
            'dnt' => true,
        ];

        if ($data->twitter_theme) {
            $query['theme'] = $data->twitter_theme;
        }

        if ('embedded_tweet' === $type) {
            if (!$data->twitter_cards) {
                $query['hide_media'] = '1';
            }

            if (!$data->twitter_conversation) {
                $query['hide_thread'] = '1';
            }
        } elseif ('user_timeline' === $type) {
            if ($data->twitter_limit > 0 && $data->twitter_limit <= 20) {
                $query['limit'] = $data->twitter_limit;
            }

            if (!empty($chrome = StringUtil::deserialize($data->twitter_chrome))) {
                $query['chrome'] = implode(' ', $chrome);
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Unknown element type "%s"', $type));
        }

        return $query;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    private function getHtmlForQuery(array $query)
    {
        ksort($query, SORT_STRING);
        $parsedQuery = http_build_query($query);
        $hash = md5($parsedQuery);

        if (!isset($this->responseCache[$hash])) {
            $response = $this->httpClient->request(
                'GET',
                'https://publish.twitter.com/oembed?'.$parsedQuery
            );

            if (($status = $response->getStatusCode()) < 200 || $status >= 300) {
                throw new \RuntimeException('Invalid Twitter response: '.$response->getContent(), $status);
            }

            $this->responseCache[$hash] = $response->toArray()['html'];
        }

        return $this->responseCache[$hash];
    }
}
