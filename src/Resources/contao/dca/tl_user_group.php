<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Extend the default palette
PaletteManipulator::create()
    ->addLegend('portfolio_legend', 'amg_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(['portfolio', 'portfoliop'], 'portfolio_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group')
;

// Add fields to tl_user_group
$GLOBALS['TL_DCA']['tl_user_group']['fields']['portfolio'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['portfolio'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_portfolio_archive.title',
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['portfoliop'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['portfoliop'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];
