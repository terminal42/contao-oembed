<?php

/**
 * Config
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = ['terminal42_twitter.data_container.content', 'onSubmit'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['embedded_tweet'] = '{type_legend},type;{include_legend},twitter_url,twitter_theme,twitter_cards,twitter_conversation;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['user_timeline'] = '{type_legend},type;{include_legend},twitter_url,twitter_theme;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_url'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_url'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql'       => "varchar(255) NOT NULL default ''",
    'save_callback' => [['terminal42_twitter.data_container.content', 'onSaveTwitterUrl']]
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_theme'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_theme'],
    'exclude'   => true,
    'default'   => 'light',
    'inputType' => 'radio',
    'options'   => ['light', 'dark'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['twitter_theme'],
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
    'sql'       => "varchar(5) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_cards'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_cards'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_conversation'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_conversation'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''",
];
