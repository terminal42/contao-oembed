<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('embedded_tweet', category: 'media')]
#[AsContentElement('user_timeline', category: 'media')]
#[AsContentElement('embedded_instagram_post', category: 'media')]
#[AsContentElement('oembed_facebook', category: 'media')]
#[AsContentElement('oembed_podigee', category: 'media')]
#[AsContentElement('oembed_tiktok', category: 'media')]
class OEmbedController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        // Let Contao render the template
        return $template->getResponse();
    }
}
