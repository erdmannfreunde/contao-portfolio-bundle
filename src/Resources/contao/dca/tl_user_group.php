<?php

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Extend the default palette
PaletteManipulator::create()
    ->addLegend('portfolio_legend', 'amg_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(array('portfolio', 'portfoliop'), 'portfolio_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group')
;

// Add fields to tl_user_group
$GLOBALS['TL_DCA']['tl_user_group']['fields']['portfolio'] = array
(
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['portfolio'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_portfolio_archive.title',
    'eval'       => array('multiple'=>true),
    'sql'        => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['portfoliop'] = array
(
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['portfoliop'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'options'    => array('create', 'delete'),
    'reference'  => &$GLOBALS['TL_LANG']['MSC'],
    'eval'       => array('multiple'=>true),
    'sql'        => "blob NULL"
);
