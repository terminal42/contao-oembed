<?php

namespace Terminal42\TwitterBundle\DataContainer;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;

class ContentDataContainer
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * Constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Save oembedd HTML from Twitter URL to tl_content.html field.
     *
     * @param string        $value
     * @param DataContainer $dc
     *
     * @return string
     */
    public function onSaveTwitterUrl($value, DataContainer $dc)
    {
        return $value;
    }
}
