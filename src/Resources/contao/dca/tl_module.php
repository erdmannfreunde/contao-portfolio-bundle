<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['portfoliolist'] = '{title_legend},name,headline,type;{config_legend},portfolio_featured,numberOfItems,portfolio_filter,portfolio_filter_reset,filter_categories;{redirect_legend},jumpTo;{template_legend:hide},portfolio_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['portfolioreader'] = '{title_legend},name,headline,type;{template_legend:hide},portfolio_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/*
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_template'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['portfolio_template'],
    'default'          => 'portfolio_short',
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => ['tl_module_portfolio', 'getPortfolioTemplates'],
    'eval'             => ['tl_class' => 'w50'],
    'sql'              => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_featured'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['portfolio_featured'],
    'default'   => 'all_items',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['all_items', 'featured', 'unfeatured'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval'      => ['tl_class' => 'w50 clr'],
    'sql'       => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_filter'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['portfolio_filter'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50 clr'],
    'sql'       => ['type' => 'boolean', 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_filter_reset'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['portfolio_filter_reset'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => ['type' => 'boolean', 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['filter_categories']    = [
    'label'      => &$GLOBALS['TL_LANG']['tl_portfolio']['categories'],
    'exclude'    => true,
    'filter'     => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_portfolio_category.title',
    'eval'       => array('multiple' => true, 'chosen' => true, 'tl_class' => 'clr w50'),
    'sql'        => "blob NULL"
];

/**
 * Class tl_module_portfolio.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_module_portfolio extends Backend
{
    /**
     * Return all portfolio templates as array.
     *
     * @return array
     */
    public function getPortfolioTemplates()
    {
        return $this->getTemplateGroup('portfolio_');
    }
}
