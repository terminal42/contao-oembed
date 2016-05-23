<?php

namespace Terminal42\TwitterBundle\DataContainer;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;

class ContentDataContainer
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * Constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function onSubmit(DataContainer $dc)
    {
        if (!$dc->activeRecord || 'embedded_tweet' !== $dc->activeRecord->type) {
            return;
        }

        $query = ['url' => $dc->activeRecord->twitter_url];

        if (!$dc->activeRecord->twitter_cards) {
            $query['hide_media'] = '1';
        }

        if (!$dc->activeRecord->twitter_conversation) {
            $query['hide_thread'] = '1';
        }

        $client = new Client();
        $response = $client->get('https://publish.twitter.com/oembed', [
            'query' => $query
        ]);

        $this->db->update('tl_content', ['html' => $response->getBody()], ['id' => $dc->id]);
    }
}
