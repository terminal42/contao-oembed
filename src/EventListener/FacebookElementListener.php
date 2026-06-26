<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DC_Table;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FacebookElementListener extends AbstractElementListener
{
    public function __construct(
        HttpClientInterface $httpClient,
        TranslatorInterface $translator,
        #[Autowire(param: 'terminal42_oembed.facebook_token')] private readonly string $facebookToken,
        LoggerInterface|null $logger = null,
    ) {
        parent::__construct($httpClient, $translator, $logger);
    }

    #[AsCallback('tl_content', 'fields.facebook_url.save')]
    public function validateOnSave(string $value, DC_Table $dc): string
    {
        return parent::validateOnSave($value, $dc);
    }

    protected function isSupported(array $activeRecord): bool
    {
        return 'embedded_facebook' === $activeRecord['type']
            && !empty($activeRecord['facebook_url']);
    }

    protected function getHtml(array $activeRecord): string
    {
        return $this->getHtmlForUrl($activeRecord['facebook_url']);
    }

    protected function throwOnWidgetError(\Exception $exception): never
    {
        throw new \RuntimeException($this->translator->trans('ERR.facebook_url', [$exception->getCode()], 'contao_default'));
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
