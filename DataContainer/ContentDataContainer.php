<?php

namespace Terminal42\TwitterBundle\DataContainer;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use Monolog\Logger;

class ContentDataContainer
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param Connection $db
     * @param Logger     $logger
     */
    public function __construct(Connection $db, Logger $logger = null)
    {
        $this->db     = $db;
        $this->logger = $logger;
    }

    public function onSaveTwitterUrl($value)
    {
        try {

            $client = new Client();
            $response = $client->get(
                'https://publish.twitter.com/oembed',
                ['query' => $this->prepareQuery((object) ['twitter_url' => $value])]
            );

            if (!$response->json()['author_url']) {
                throw new \Exception('Invalid Twitter response: ' . $response->getBody(), 500);
            }

        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info($e->getMessage(), ['exception' => $e]);
            }

            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['twitter_url'], $e->getCode()));
        }

        return $value;
    }

    public function onSubmit(DataContainer $dc)
    {
        if (!$dc->activeRecord
            || 'embedded_tweet' !== $dc->activeRecord->type
            || '' === $dc->activeRecord->twitter_url
        ) {
            return;
        }


        try {
            $client   = new Client();
            $response = $client->get(
                'https://publish.twitter.com/oembed',
                ['query' => $this->prepareQuery($dc->activeRecord)]
            );

            $this->db->update('tl_content', ['html' => $response->json()['html']], ['id' => $dc->id]);
        } catch (\Exception $e) {
            return;
        }
    }

    private function prepareQuery($data)
    {
        $query = [
            'url'         => $data->twitter_url,
            'theme'       => $data->twitter_theme,
            'omit_script' => true,
        ];

        if (!$data->twitter_cards) {
            $query['hide_media'] = '1';
        }

        if (!$data->twitter_conversation) {
            $query['hide_thread'] = '1';
        }

        return $query;
    }
}
