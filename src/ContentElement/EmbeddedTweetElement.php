<?php

namespace Terminal42\OEmbedBundle\ContentElement;

use Contao\ContentElement;

class EmbeddedTweetElement extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_embedded_tweet';

    /**
     * Compile the content element
     */
    protected function compile()
    {
        if ('FE' === TL_MODE) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'https://platform.twitter.com/widgets.js|async';
        }
    }
}
