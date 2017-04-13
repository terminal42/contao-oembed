<?php

namespace Terminal42\TwitterBundle\ContentElement;

use Contao\ContentElement;

class UserTimelineElement extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_user_timeline';

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
