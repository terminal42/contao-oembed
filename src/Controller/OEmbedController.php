<?php

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
 */
class OEmbedController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        return new Response($template->parse());
    }
}
