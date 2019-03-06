<?php

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['embedded_tweet'] = '{type_legend},type;{include_legend},twitter_url,twitter_theme,twitter_cards,twitter_conversation;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['user_timeline'] = '{type_legend},type;{include_legend},twitter_url,twitter_theme,twitter_limit,twitter_chrome;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['embedded_instagram_post'] = '{type_legend},type;{include_legend},instagram_url,instagram_maxwidth,instagram_hidecaption;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_url'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_url'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_theme'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_theme'],
    'exclude'   => true,
    'default'   => 'light',
    'inputType' => 'radio',
    'options'   => ['light', 'dark'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['twitter_theme'],
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
    'sql'       => ['type' => 'string', 'length' => 5, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_cards'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_cards'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_conversation'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_conversation'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_limit'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_limit'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
    'sql'       => ['type' => 'integer', 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['twitter_chrome'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['twitter_chrome'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['noheader', 'nofooter', 'noborders', 'noscrollbar', 'transparent'],
    'eval'      => ['multiple' => true, 'tl_class' => 'clr'],
    'sql'       => ['type' => 'text', 'length' => 65000, 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['instagram_url'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['instagram_url'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['instagram_maxwidth'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['instagram_maxwidth'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['maxlength' => 64, 'tl_class' => 'w50', 'rgpxp' => 'natural'],
    'sql'       => ['type' => 'string', 'length' => 64, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['instagram_hidecaption'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['instagram_hidecaption'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50 m12'],
    'sql'       => ['type' => 'string', 'length' => 1, 'fixed' => true, 'default' => ''],
];
