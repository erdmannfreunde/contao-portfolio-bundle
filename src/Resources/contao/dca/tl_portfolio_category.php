<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

use Contao\DC_Table;
use Contao\Backend;
use Contao\Database;
use Contao\StringUtil;
use Contao\BackendUser;
use Contao\DataContainer;

$GLOBALS['TL_DCA']['tl_portfolio_category'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,

        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'alias' => 'index',
            ],
        ],
        'backlink' => 'do=portfolio',
    ],

    'list' => [
        'sorting' => [
            'mode' => 1,
            'flag' => 1,
            'panelLayout' => 'sort,filter;search,limit',
            'fields' => ['title'],
        ],
        'label' => [
            'fields' => ['title'],
        ],
        'global_operations' => [
            'toggleNodes' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href' => 'ptg=all',
                'class' => 'header_toggle',
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_category']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_category']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_category']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_category']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},title,alias,frontendTitle,cssClass;{modules_legend:hide},hideInList,hideInReader,excludeInRelated;{redirect_legend:hide},jumpTo;{publish_legend},published',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio_category']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio_category']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'unique' => true, 'spaceToUnderscore' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [
                ['tl_portfolio_category', 'generateAlias'],
            ],
            'sql' => "varbinary(128) NOT NULL default ''",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio_category']['published'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_portfolio.
 */
class tl_portfolio_category extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Auto-generate the portfolio alias if it has not been set yet.
     *
     * @param mixed $varValue
     *
     * @throws Exception
     */
    public function generateAlias($varValue, DataContainer $dc): string
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ('' === $varValue) {
            $autoAlias = true;
            $varValue = StringUtil::generateAlias($dc->activeRecord->title);
        }

        $objAlias = Database::getInstance()
            ->prepare('SELECT id FROM tl_portfolio_category WHERE alias=?')
            ->execute($varValue)
        ;

        // Check whether the portfolio alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-'.$dc->id;
        }

        return $varValue;
    }
}
