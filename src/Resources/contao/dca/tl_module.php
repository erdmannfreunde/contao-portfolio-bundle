<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['portfoliolist'] = '{title_legend},name,headline,type;{config_legend},portfolio_archives,portfolio_readerModule,portfolio_featured,numberOfItems,filter_categories,perPage;{nav_legend},portfolio_filter,portfolio_filter_reset;{redirect_legend},jumpTo;{template_legend:hide},portfolio_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['portfolioreader'] = '{title_legend},name,headline,type;{config_legend},portfolio_archives;{template_legend:hide},portfolio_template,customTpl;{protected_legend:hide},{image_legend:hide},imgSize;protected;{expert_legend:hide},guests,cssID,space';

/*
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_archives'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['portfolio_archives'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => ['tl_module_portfolio', 'getPortfolioArchives'],
    'eval' => ['multiple' => true, 'mandatory' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_template'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['portfolio_template'],
    'default' => 'portfolio_short',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['tl_module_portfolio', 'getPortfolioTemplates'],
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(32) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_featured'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['portfolio_featured'],
    'default' => 'all_items',
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['all_items', 'featured', 'unfeatured'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => ['tl_class' => 'w50 clr'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_filter'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['portfolio_filter'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 clr'],
    'sql' => ['type' => 'boolean', 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_filter_reset'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['portfolio_filter_reset'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean', 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['filter_categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['filter_categories'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_portfolio_category.title',
    'eval' => ['multiple' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['portfolio_readerModule'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['portfolio_readerModule'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['tl_module_portfolio', 'getReaderModules'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

/**
 * Class tl_module_portfolio.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_module_portfolio extends \Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Return all portfolio templates as array.
     */
    public function getPortfolioTemplates(): array
    {
        return $this->getTemplateGroup('portfolio_');
    }

    /**
     * Get all portfolio archives and return them as array.
     */
    public function getPortfolioArchives(): array
    {
        if (!$this->User->isAdmin && !is_array($this->User->portfolio)) {
            return [];
        }

        $arrArchives = [];
        $objArchives = $this->Database->execute('SELECT id, title FROM tl_portfolio_archive ORDER BY title');

        while ($objArchives->next()) {
            if ($this->User->hasAccess($objArchives->id, 'portfolio')) {
                $arrArchives[$objArchives->id] = $objArchives->title;
            }
        }

        return $arrArchives;
    }

    /**
     * Get all portfolio reader modules and return them as array.
     */
    public function getReaderModules(): array
    {
        $arrModules = [];
        $objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='portfolioreader' ORDER BY t.name, m.name");

        while ($objModules->next()) {
            $arrModules[$objModules->theme][$objModules->id] = $objModules->name.' (ID '.$objModules->id.')';
        }

        return $arrModules;
    }
}
