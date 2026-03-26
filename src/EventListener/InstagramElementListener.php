<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InstagramElementListener
{
    private array $responseCache = [];

    public function __construct(
        private readonly Connection $database,
        private readonly HttpClientInterface $httpClient,
        #[Autowire(param: 'terminal42_oembed.facebook_token')] private readonly string $facebookToken,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    #[AsCallback('tl_content', 'fields.instagram_url.save')]
    public function onSaveCallback(string $value): string
    {
        try {
            $this->getHtmlForQuery($this->prepareQuery(
                (object) ['instagram_url' => $value],
            ));
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info($e->getMessage(), ['exception' => $e]);
            }

            throw new \RuntimeException(\sprintf($GLOBALS['TL_LANG']['ERR']['instagram_url'], $e->getCode()));
        }

        return $value;
    }

    #[AsCallback('tl_content', 'config.onsubmit')]
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
                $this->prepareQuery($dc->activeRecord),
            );

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
                'https://graph.facebook.com/v23.0/instagram_oembed?'.$parsedQuery,
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->facebookToken,
                    ],
                ],
            );

            if (($status = $response->getStatusCode()) < 200 || $status > 301) {
                throw new \RuntimeException('Invalid Instagram response: '.$response->getContent(false), $status);
            }

            $this->responseCache[$hash] = $response->toArray()['html'];
        }

        return $this->responseCache[$hash];
    }
}
