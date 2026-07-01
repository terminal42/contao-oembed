<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DC_Table;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ResetInterface;

abstract class AbstractElementListener implements ResetInterface
{
    protected array $responseCache = [];

    public function __construct(protected readonly LoggerInterface|null $logger = null)
    {
    }

    public function reset(): void
    {
        $this->responseCache = [];
    }

    #[AsCallback('tl_content', 'fields.oembed_url.save')]
    public function validateOnSave(string $value, DC_Table $dc): string
    {
        if (
            !($activeRecord = $dc->getActiveRecord())
            || !$this->isSupported($activeRecord['type'])
        ) {
            return $value;
        }

        try {
            $this->getHtml($activeRecord);
        } catch (\Exception $e) {
            $this->logger?->info($e->getMessage(), ['exception' => $e]);
            $this->throwOnWidgetError($e);
        }

        return $value;
    }

    #[AsCallback('tl_content', 'config.onbeforesubmit')]
    public function updateOnSubmit(array $values, DC_Table $dc): array
    {
        if (
            !($activeRecord = $dc->getActiveRecord())
            || !$this->isSupported($activeRecord)
        ) {
            return $values;
        }

        try {
            $values['html'] = $this->getHtml($activeRecord);
        } catch (\Exception) {
            // this should never happen, error has already been logged by save_callback
        }

        return $values;
    }

    abstract protected function isSupported(array $activeRecord): bool;

    abstract protected function getHtml(array $activeRecord): string;

    abstract protected function throwOnWidgetError(\Exception $exception): never;
}
