<?php

/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['portfoliolist'] = '{title_legend},name,headline,type;{config_legend},portfolio_featured,numberOfItems,portfolio_filter;{redirect_legend},jumpTo;{template_legend:hide},portfolio_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['portfolioreader'] = '{title_legend},name,headline,type;{template_legend:hide},portfolio_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_template'] = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['portfolio_template'],
    'default'          => 'portfolio_short',
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => array('tl_module_portfolio', 'getPortfolioTemplates'),
    'eval'             => array('tl_class' => 'w50'),
    'sql'              => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_featured'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['portfolio_featured'],
    'default'   => 'all_items',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('all_items', 'featured', 'unfeatured'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval'      => array('tl_class' => 'w50 clr'),
    'sql'       => "varchar(16) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_filter'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['portfolio_filter'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => ['type' => 'boolean', 'default' => 0],
];

/**
 * Class tl_module_portfolio
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_module_portfolio extends Backend
{
    /**
     * Return all portfolio templates as array
     * @return array
     */
    public function getPortfolioTemplates()
    {
        return $this->getTemplateGroup('portfolio_');
    }
}
