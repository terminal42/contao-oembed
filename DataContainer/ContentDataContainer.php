<?php

namespace Terminal42\TwitterBundle\DataContainer;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class ContentDataContainer
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $responseCache;

    /**
     * Constructor.
     *
     * @param Connection      $db
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $db, LoggerInterface $logger = null)
    {
        $this->db     = $db;
        $this->logger = $logger;
    }

    public function onSaveTwitterUrl($value, DataContainer $dc)
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

    public function onSubmit(DataContainer $dc)
    {
        if (!$dc->activeRecord
            || !in_array($dc->activeRecord->type, ['embedded_tweet', 'user_timeline'])
            || '' === $dc->activeRecord->twitter_url
        ) {
            return;
        }

        try {
            $html = $this->getHtmlForQuery(
                $this->prepareQueryForType($dc->activeRecord->type, $dc->activeRecord)
            );

            $this->db->update(
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

    private function prepareQueryForType($type, $data)
    {
        if ('embedded_tweet' === $type) {
            $query = [
                'url'         => $data->twitter_url,
                'theme'       => $data->twitter_theme,
                'omit_script' => true,
                'aria_polite' => 'assertive',
                'dnt'         => true,
            ];

            if (!$data->twitter_cards) {
                $query['hide_media'] = '1';
            }

            if (!$data->twitter_conversation) {
                $query['hide_thread'] = '1';
            }
        } elseif ('user_timeline' === $type) {
            $query = [
                'url'         => $data->twitter_url,
                'theme'       => $data->twitter_theme,
                'omit_script' => true,
                'aria_polite' => 'assertive',
                'dnt'         => true,
            ];

            if ($data->twitter_limit > 0 && $data->twitter_limit <= 20) {
                $query['limit'] = $data->twitter_limit;
            }

            if (!empty($chrome = deserialize($data->twitter_chrome))) {
                $query['chrome'] = implode(' ', $chrome);
            }

        } else {
            throw new \InvalidArgumentException(sprintf('Unknown element type "%s"', $type));
        }

        return $query;
    }

    /**
     * @param array $query
     *
     * @return string
     * @throws \Exception
     */
    private function getHtmlForQuery(array $query)
    {
        ksort($query, SORT_STRING);
        $hash = md5(http_build_query($query));

        if (!isset($this->responseCache[$hash])) {
            $client   = new Client();
            $response = $client->get(
                'https://publish.twitter.com/oembed',
                ['query' => $query]
            );

            if (($status = $response->getStatusCode()) < 200 || $status >= 300) {
                throw new \RuntimeException('Invalid Twitter response: ' . $response->getBody(), $status);
            }

            $this->responseCache[$hash] = $response->json()['html'];
        }

        return $this->responseCache[$hash];
    }
}
