<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DC_Table;
use Contao\StringUtil;

class TwitterElementListener extends AbstractElementListener
{
    #[AsCallback('tl_content', 'fields.twitter_url.save')]
    public function validateOnSave(string $value, DC_Table $dc): string
    {
        return parent::validateOnSave($value, $dc);
    }

    protected function isSupported(array $activeRecord): bool
    {
        return \in_array($activeRecord['type'], ['embedded_tweet', 'user_timeline'], true)
            && !empty($activeRecord['twitter_url']);
    }

    protected function getHtml(array $activeRecord): string
    {
        return $this->getHtmlForQuery($this->prepareQueryForType($activeRecord));
    }

    protected function throwOnWidgetError(\Exception $exception): never
    {
        throw new \RuntimeException($this->translator->trans('ERR.twitter_url', [$exception->getCode()], 'contao_default'));
    }

    private function prepareQueryForType(array $data): array
    {
        $query = [
            'url' => $data['twitter_url'],
            'omit_script' => true,
            'aria_polite' => 'assertive',
            'dnt' => true,
        ];

        if ($data['twitter_theme'] ?? null) {
            $query['theme'] = $data['twitter_theme'];
        }

        if ('embedded_tweet' === $data['type']) {
            if (!($data['twitter_cards'] ?? false)) {
                $query['hide_media'] = '1';
            }

            if (!($data->twitter_conversation ?? false)) {
                $query['hide_thread'] = '1';
            }
        } elseif ('user_timeline' === $data['type']) {
            if (($data['twitter_limit'] ?? 0) > 0 && ($data['twitter_limit'] ?? 0) <= 20) {
                $query['limit'] = $data['twitter_limit'];
            }

            if (!empty($data['twitter_chrome']) && !empty($chrome = StringUtil::deserialize($data['twitter_chrome']))) {
                $query['chrome'] = implode(' ', $chrome);
            }
        } else {
            throw new \InvalidArgumentException(\sprintf('Unknown element type "%s"', $data['type']));
        }

        return $query;
    }

    /**
     * @throws \Exception
     */
    private function getHtmlForQuery(array $query): string
    {
        ksort($query, SORT_STRING);
        $parsedQuery = http_build_query($query);
        $hash = md5($parsedQuery);

        if (!isset($this->responseCache[$hash])) {
            $response = $this->httpClient->request(
                'GET',
                'https://publish.x.com/oembed?'.$parsedQuery,
            );

            if (($status = $response->getStatusCode()) < 200 || $status >= 300) {
                throw new \RuntimeException('Invalid Twitter response: '.$response->getContent(false), $status);
            }

            $this->responseCache[$hash] = $response->toArray()['html'];
        }

        return $this->responseCache[$hash];
    }
}
