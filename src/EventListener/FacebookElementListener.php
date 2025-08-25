<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookElementListener
{
    private Connection $database;

    private HttpClientInterface $httpClient;

    private string $facebookToken;

    private ?LoggerInterface $logger;

    private array $responseCache = [];

    public function __construct(Connection $database, HttpClientInterface $httpClient, string $facebookToken, ?LoggerInterface $logger = null)
    {
        $this->database = $database;
        $this->httpClient = $httpClient;
        $this->facebookToken = $facebookToken;
        $this->logger = $logger;
    }

    /**
     * @Callback(table="tl_content", target="fields.facebook_url.save")
     */
    public function onSaveCallback(string $value): string
    {
        try {
            $this->getHtmlForUrl($value);
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info($e->getMessage(), ['exception' => $e]);
            }

            throw new \RuntimeException(\sprintf($GLOBALS['TL_LANG']['ERR']['facebook_url'], $e->getCode()));
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
            || 'embedded_facebook' !== $dc->activeRecord->type
            || '' === $dc->activeRecord->facebook_url
        ) {
            return;
        }

        try {
            $html = $this->getHtmlForUrl($dc->activeRecord->facebook_url);

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
                $this->getApiEndpointForUrl($url).'?omitscript=true&url='.$url,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->facebookToken,
                    ],
                ],
            );

            if (($status = $response->getStatusCode()) < 200 || $status > 301) {
                throw new \RuntimeException('Invalid Facebook response: '.$response->getContent(false), $status);
            }

            $this->responseCache[$url] = $response->toArray()['html'];
        }

        return $this->responseCache[$url];
    }

    /**
     * @see https://developers.facebook.com/docs/plugins/oembed
     */
    private function getApiEndpointForUrl(string $url): string
    {
        switch (true) {
            // Facebook page name or ID
            case preg_match('{^https://www\.facebook\.com/([^/?]+)$}', $url):
                return 'https://graph.facebook.com/v23.0/oembed_page';

            // Facebook Post
            case preg_match('{^https://www\.facebook\.com/[^/]+/posts/.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/[^/]+/activity/.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/photo\.php\?fbid=.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/photos/.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/permalink\.php\?story_fbid=.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/media/set\?set=.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/questions/.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/notes/[^/]+/[^/]+/.+$}', $url):
                return 'https://graph.facebook.com/v23.0/oembed_post';

            // Facebook Video
            case preg_match('{^https://www\.facebook\.com/[^/]+/videos/[^/]+/$}', $url):
            case preg_match('{^https://www\.facebook\.com/video\.php\?id=.+$}', $url):
            case preg_match('{^https://www\.facebook\.com/video\.php\?v=.+$}', $url):
                return 'https://graph.facebook.com/v23.0/oembed_video';
        }

        throw new \RuntimeException('Unknown Facebook URL format');
    }
}
