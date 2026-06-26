<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DC_Table;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InstagramElementListener extends AbstractElementListener
{
    public function __construct(
        HttpClientInterface $httpClient,
        TranslatorInterface $translator,
        #[Autowire(param: 'terminal42_oembed.facebook_token')] private readonly string $facebookToken,
        LoggerInterface|null $logger = null,
    ) {
        parent::__construct($httpClient, $translator, $logger);
    }

    #[AsCallback('tl_content', 'fields.instagram_url.save')]
    public function validateOnSave(string $value, DC_Table $dc): string
    {
        return parent::validateOnSave($value, $dc);
    }

    protected function isSupported(array $activeRecord): bool
    {
        return 'embedded_instagram_post' === $activeRecord['type']
            && !empty($activeRecord['instagram_url']);
    }

    protected function getHtml(array $activeRecord): string
    {
        return $this->getHtmlForQuery($this->prepareQuery($activeRecord));
    }

    protected function throwOnWidgetError(\Exception $exception): never
    {
        throw new \RuntimeException($this->translator->trans('ERR.instagram_url', [$exception->getCode()], 'contao_default'));
    }

    private function prepareQuery(array $data): array
    {
        $query = [
            'url' => $data['instagram_url'],
            'omitscript' => '1',
        ];

        if ($data['instagram_maxwidth'] ?? null) {
            $query['maxwidth'] = $data['instagram_maxwidth'];
        }

        if ($data['instagram_hidecaption'] ?? false) {
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
