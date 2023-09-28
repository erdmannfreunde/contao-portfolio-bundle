<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020-2023, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

use Contao\Date;
use Contao\Image;
use Contao\Input;
use Contao\Config;
use Contao\System;
use Contao\Backend;
use Contao\Database;
use Contao\DC_Table;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\BackendUser;
use Contao\DataContainer;
use EuF\PortfolioBundle\Models\PortfolioArchiveModel;

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_portfolio'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_portfolio_archive',
        'ctable' => ['tl_content'],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'headline',
        'onsubmit_callback' => [
            ['tl_portfolio', 'adjustTime'],
        ],
        'oninvalidate_cache_tags_callback' => [
            ['tl_portfolio', 'addSitemapCacheInvalidationTag'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'index',
                'pid,published,featured,start,stop' => 'index',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'panelLayout' => 'filter;sort,search,limit',
            'headerFields' => ['title'],
            'defaultSearchField' => 'headline',
            'child_record_callback' => ['tl_portfolio', 'listItems'],
            'paste_button_callback' => ['tl_portfolio', 'pasteElement'],
        ],
        'label' => [
            'fields' => ['headline'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['editmeta'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['edit'],
                'href'  => 'table=tl_content',
                'icon'  => 'children.svg',
            ],
            'copy',
            'cut',
            'delete',
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
                'showInHeader' => true
            ],
            'feature' => [
                'href' => 'act=toggle&amp;field=featured',
                'icon' => 'featured.svg',
            ],
            'show',
        ],
    ],

    'palettes' => [
        '__selector__' => ['addImage', 'source', 'overwriteMeta'],
        'default' => '{title_legend},headline,alias,categories,client;{meta_legend},pageTitle,robots,description,serpPreview;{teaser_legend},teaser;{date_legend},date;{image_legend},addImage;{source_legend:hide},source;{expert_legend:hide},cssClass,noComments,featured;{publish_legend},published,start,stop',
    ],

    'subpalettes' => [
        'addImage' => 'singleSRC,size,floating,fullsize,overwriteMeta',
        'source_internal' => 'jumpTo',
        'source_article' => 'articleId',
        'source_external' => 'url,target',
        'overwriteMeta' => 'alt,imageTitle,imageUrl,caption',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_portfolio_archive.title',
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['sorting'],
            'sorting' => true,
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'headline' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['headline'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['alias'],
            'exclude' => true,
            'search' => false,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [
                ['tl_portfolio', 'generateAlias'],
            ],
            'sql' => "varchar(255) BINARY NOT NULL default ''",
        ],
        'categories' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['categories'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_portfolio_category.title',
            'eval' => ['multiple' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
            'sql' => 'blob NULL',
        ],
        'client' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['client'],
            'exclude' => true,
            'search' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'pageTitle' => [

            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'robots' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'select',
            'options' => ['index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow'],
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true],
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'description' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => array('style' => 'height:60px', 'decodeEntities' => true, 'tl_class' => 'clr'),
            'sql' => "text NULL"
        ],
        'serpPreview' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['serpPreview'],
            'exclude' => true,
            'inputType' => 'serpPreview',
            'eval' => [
                'url_callback' => ['tl_portfolio', 'getSerpUrl'],
                'title_tag_callback' => ['tl_portfolio', 'getTitleTag'],
                'titleFields' => ['pageTitle', 'headline'],
                'descriptionFields' => ['description', 'teaser']
            ],
            'sql' => null
        ],
        'teaser' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['teaser'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr'],
            'explanation' => 'insertTags',
            'sql' => 'mediumtext NULL',
        ],
        'date' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['date'],
            'default' => time(),
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 8,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'addImage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['addImage'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'overwriteMeta' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['overwriteMeta'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'singleSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql' => 'binary(16) NULL',
        ],
        'alt' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['alt'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'imageTitle' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['imageTitle'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'size' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['size'],
            'exclude' => true,
            'inputType' => 'imageSize',
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => static function () {
                return System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance());
            },
            'sql' => "varchar(128) COLLATE ascii_bin NOT NULL default ''",
        ],
        'imageUrl' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['imageUrl'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'fullsize' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'caption' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['caption'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'floating' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['floating'],
            'default' => 'above',
            'exclude' => true,
            'inputType' => 'radioTable',
            'options' => ['above', 'left', 'right', 'below'],
            'eval' => ['cols' => 4, 'tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'sql' => "varchar(12) NOT NULL default ''",
        ],
        'source' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['source'],
            'default' => 'default',
            'exclude' => true,
            'filter' => true,
            'inputType' => 'radio',
            'options_callback' => ['tl_portfolio', 'getSourceOptions'],
            'reference' => &$GLOBALS['TL_LANG']['tl_portfolio'],
            'eval' => ['submitOnChange' => true, 'helpwizard' => true],
            'sql' => "varchar(12) NOT NULL default ''",
        ],
        'jumpTo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['jumpTo'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['mandatory' => true, 'fieldType' => 'radio'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'articleId' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['articleId'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => ['tl_portfolio', 'getArticleAlias'],
            'eval' => ['chosen' => true, 'mandatory' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'url' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['url'],
            'exclude' => true,
            'search' => false,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'target' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['target'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'cssClass' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['cssClass'],
            'exclude' => true,
            'inputType' => 'text',
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['published'],
            'exclude' => true,
            'filter' => true,
            'toggle' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'start' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['start'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['stop'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'featured' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['featured'],
            'exclude' => true,
            'filter' => true,
            'toggle' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
    ],
];

class tl_portfolio extends Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Check permissions to edit table tl_portfolio
     *
     * @throws AccessDeniedException
     */
    public function checkPermission(): void
    {
        if ($this->User->isAdmin) {
            return;
        }

        // Set the root IDs
        if (empty($this->User->portfolio) || !is_array($this->User->portfolio)) {
            $root = array(0);
        } else {
            $root = $this->User->portfolio;
        }

        $id = Input::get('id') !== '' ? Input::get('id') : CURRENT_ID;

        // Check current action
        switch (Input::get('act')) {
            case 'paste':
            case 'select':
                // Check CURRENT_ID here (see #247)
                if (!in_array(CURRENT_ID, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access portfolio archive ID ' . $id . '.');
                }
                break;

            case 'create':
                if (!Input::get('pid') || !in_array(Input::get('pid'), $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to create portfolio items in portfolio archive ID ' . Input::get('pid') . '.');
                }
                break;

            case 'cut':
            case 'copy':
                if (Input::get('act') === 'cut' && Input::get('mode') === 1) {
                    $objArchive = Database::getInstance()
                        ->prepare("SELECT pid FROM tl_portfolio WHERE id=?")
                        ->limit(1)
                        ->execute(Input::get('pid'));

                    if ($objArchive->numRows < 1) {
                        throw new AccessDeniedException('Invalid portfolio item ID ' . Input::get('pid') . '.');
                    }

                    $pid = $objArchive->pid;
                } else {
                    $pid = Input::get('pid');
                }

                if (!in_array($pid, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' portfolio item ID ' . $id . ' to portfolio archive ID ' . $pid . '.');
                }
            // no break

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = Database::getInstance()
                    ->prepare("SELECT pid FROM tl_portfolio WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid portfolio item ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' portfolio item ID ' . $id . ' of portfolio archive ID ' . $objArchive->pid . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access portfolio archive ID ' . $id . '.');
                }

                $objArchive = Database::getInstance()
                    ->prepare("SELECT id FROM tl_portfolio WHERE pid=?")
                    ->execute($id);

                /** @var SessionInterface $objSession */
                $objSession = System::getContainer()->get('session');

                $session = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect((array)$session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $objSession->replace($session);
                break;

            default:
                if (Input::get('act')) {
                    throw new AccessDeniedException('Invalid command "' . Input::get('act') . '".');
                }

                if (!in_array($id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access portfolio archive ID ' . $id . '.');
                }
                break;
        }
    }

    /**
     * Add the type of input field.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listItems($arrRow): string
    {
        return '<div class="tl_content_left">' . $arrRow['headline'] . ' <span style="color:#999;padding-left:3px">[' . Date::parse(Config::get('dateFormat'), $arrRow['date']) . ']</span></div>';
    }

    /**
     * Auto-generate the portfolio alias if it has not been set yet.
     *
     * @param mixed $varValue
     *
     * @param DataContainer $dc
     * @return string
     * @throws Exception
     */

    public function generateAlias($varValue, Contao\DataContainer $dc)
    {
        $aliasExists = function (string $alias) use ($dc): bool {
            return Database::getInstance()
                    ->prepare("SELECT id FROM tl_portfolio WHERE alias=? AND id!=?")
                    ->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate alias if there is none
        if (!$varValue) {
            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->headline, EuF\PortfolioBundle\Models\PortfolioArchiveModel::findByPk($dc->activeRecord->pid)->jumpTo, $aliasExists);
        } elseif (preg_match('/^[1-9]\d*$/', $varValue)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        } elseif ($aliasExists($varValue)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }

    /**
     * Return the SERP URL
     *
     * @param EuF\PortfolioBundle\Models\PortfolioModel $model
     *
     * @return string
     */
    public function getSerpUrl(EuF\PortfolioBundle\Models\PortfolioModel $model)
    {
        return EuF\PortfolioBundle\Classes\Portfolio::generatePortfolioUrl($model, false, true);
    }

    /**
     * Return the title tag from the associated page layout
     *
     * @param EuF\PortfolioBundle\Models\PortfolioModel $model
     *
     * @return string
     */
    public function getTitleTag(EuF\PortfolioBundle\Models\PortfolioModel $model)
    {
        /** @var EuF\PortfolioBundle\Models\PortfolioArchiveModel $archive */
        if (!$archive = $model->getRelated('pid')) {
            return '';
        }

        /** @var Contao\PageModel $page */
        if (!$page = $archive->getRelated('jumpTo')) {
            return '';
        }

        $page->loadDetails();

        /** @var Contao\LayoutModel $layout */
        if (!$layout = $page->getRelated('layout')) {
            return '';
        }

        $origObjPage = $GLOBALS['objPage'] ?? null;

        // Override the global page object, so we can replace the insert tags
        $GLOBALS['objPage'] = $page;

        $title = implode(
            '%s',
            array_map(
                static function ($strVal) {
                    return str_replace('%', '%%', System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strVal));
                },
                explode('{{page::pageTitle}}', $layout->titleTag ?: '{{page::pageTitle}} - {{page::rootPageTitle}}', 2)
            )
        );

        $GLOBALS['objPage'] = $origObjPage;

        return $title;
    }

    /**
     * Get all articles and return them as array.
     *
     * @param DataContainer
     *
     * @return array
     */
    public function getArticleAlias(DataContainer $dc): array
    {
        $arrPids = [];
        $arrAlias = [];

        if (!$this->User->isAdmin) {
            foreach ($this->User->pagemounts as $id) {
                $arrPids[] = $id;
                $arrPids = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
            }

            if (empty($arrPids)) {
                return $arrAlias;
            }

            $objAlias = Database::getInstance()
                ->prepare('SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(' . implode(',', array_map('intval', array_unique($arrPids))) . ') ORDER BY parent, a.sorting')
                ->execute($dc->id);
        } else {
            $objAlias = $this->Database->prepare('SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting')
                ->execute($dc->id);
        }

        if ($objAlias->numRows) {
            System::loadLanguageFile('tl_article');

            while ($objAlias->next()) {
                $arrAlias[$objAlias->parent][$objAlias->id] = $objAlias->title . ' (' . ($GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn] ?: $objAlias->inColumn) . ', ID ' . $objAlias->id . ')';
            }
        }

        return $arrAlias;
    }

    /**
     * Add the source options depending on the allowed fields (see #5498).
     *
     * @param DataContainer
     *
     * @return array
     */
    public function getSourceOptions(DataContainer $dc): array
    {
        if ($this->User->isAdmin) {
            return ['default', 'internal', 'article', 'external'];
        }

        $arrOptions = ['default'];

        // Add the "internal" option
        if ($this->User->hasAccess('tl_portfolio::jumpTo', 'alexf')) {
            $arrOptions[] = 'internal';
        }

        // Add the "article" option
        if ($this->User->hasAccess('tl_portfolio::articleId', 'alexf')) {
            $arrOptions[] = 'article';
        }

        // Add the "external" option
        if ($this->User->hasAccess('tl_portfolio::url', 'alexf') && $this->User->hasAccess('tl_portfolio::target', 'alexf')) {
            $arrOptions[] = 'external';
        }

        // Add the option currently set
        if ($dc->activeRecord && '' !== $dc->activeRecord->source) {
            $arrOptions[] = $dc->activeRecord->source;
            $arrOptions = array_unique($arrOptions);
        }

        return $arrOptions;
    }

    /**
     * Adjust start end end time of the event based on date, span, startTime and endTime.
     *
     * @param DataContainer
     */
    public function adjustTime(DataContainer $dc): void
    {
        // Return if there is no active record (override all)
        if (!$dc->activeRecord) {
            return;
        }

        $arrSet['date'] = strtotime(date('Y-m-d', ((int)$dc->activeRecord->date)));
        $this->Database->prepare('UPDATE tl_portfolio %s WHERE id=?')->set($arrSet)->execute($dc->id);
    }

    /**
     * @param DataContainer $dc
     * @param $row
     * @param $table
     * @param $cr
     * @param $arrClipboard
     *
     * @return string
     */
    public function pasteElement(DataContainer $dc, $row, $table, $cr, $arrClipboard): string
    {
        $imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));

        return '<a href="' . self::addToUrl('act=' . $arrClipboard['mode'] . '&mode=1&pid=' . $row['id']) . '" title="' . StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a> ';
    }

    /**
     * @param DataContainer $dc
     *
     * @return array
     */
    public function addSitemapCacheInvalidationTag($dc, array $tags)
    {
        $archiveModel = PortfolioArchiveModel::findByPk($dc->activeRecord->pid);

        if ($archiveModel === null) {
            return $tags;
        }

        $pageModel = PageModel::findWithDetails($archiveModel->jumpTo);

        if ($pageModel === null) {
            return $tags;
        }

        return array_merge($tags, array('contao.sitemap.' . $pageModel->rootId));
    }
}
