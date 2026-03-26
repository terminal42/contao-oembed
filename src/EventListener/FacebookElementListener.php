<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookElementListener
{
    private array $responseCache = [];

    public function __construct(
        private readonly Connection $database,
        private readonly HttpClientInterface $httpClient,
        #[Autowire(param: 'terminal42_oembed.facebook_token')] private readonly string $facebookToken,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    #[AsCallback('tl_content', 'fields.facebook_url.save')]
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

    #[AsCallback('tl_content', 'config.onsubmit')]
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
        } catch (\Exception) {
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
        return match (true) {
            preg_match('{^https://www\.facebook\.com/([^/?]+)$}', $url) => 'https://graph.facebook.com/v23.0/oembed_page',
            preg_match('{^https://www\.facebook\.com/[^/]+/posts/.+$}', $url),
            preg_match('{^https://www\.facebook\.com/[^/]+/activity/.+$}', $url),
            preg_match('{^https://www\.facebook\.com/photo\.php\?fbid=.+$}', $url),
            preg_match('{^https://www\.facebook\.com/photos/.+$}', $url),
            preg_match('{^https://www\.facebook\.com/permalink\.php\?story_fbid=.+$}', $url),
            preg_match('{^https://www\.facebook\.com/media/set\?set=.+$}', $url),
            preg_match('{^https://www\.facebook\.com/questions/.+$}', $url),
            preg_match('{^https://www\.facebook\.com/notes/[^/]+/[^/]+/.+$}', $url) => 'https://graph.facebook.com/v23.0/oembed_post',
            preg_match('{^https://www\.facebook\.com/[^/]+/videos/[^/]+/$}', $url),
            preg_match('{^https://www\.facebook\.com/video\.php\?id=.+$}', $url),
            preg_match('{^https://www\.facebook\.com/video\.php\?v=.+$}', $url) => 'https://graph.facebook.com/v23.0/oembed_video',
            default => throw new \RuntimeException('Unknown Facebook URL format'),
        };
    }
}
