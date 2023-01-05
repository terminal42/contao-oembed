<?php

declare(strict_types=1);

namespace Terminal42\OEmbedBundle\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("embedded_tweet", category="media")
 * @ContentElement("user_timeline", category="media")
 * @ContentElement("embedded_instagram_post", category="media")
 * @ContentElement("oembed_facebook", category="media")
 * @ContentElement("oembed_podigee", category="media")
 */
class OEmbedController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        // Let Contao render the template
        return $template->getResponse();
    }
}
