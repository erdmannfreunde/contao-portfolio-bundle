<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

/*
 * Load tl_content language file
 */

use Contao\CoreBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_portfolio'] = [
    // Config
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_portfolio_archive',
        'ctable'            => ['tl_content'],
        'switchToEdit'      => true,
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['tl_portfolio', 'adjustTime'],
        ],
        'sql'               => [
            'keys' => [
                'id'                       => 'primary',
                'alias'                    => 'index',
                'pid,start,stop,published' => 'index',
            ],
        ],
    ],

    // List
    'list'        => [
        'sorting'           => [
            'mode'                    => 4,
            'fields'                  => ['sorting'],
            'panelLayout'             => 'filter;sort,search,limit',
            'headerFields'            => ['title'],
            'child_record_callback'   => ['tl_portfolio', 'listItems'],
            'paste_button_callback'   => ['tl_portfolio', 'pasteElement'],
        ],
        'label'             => [
            'fields' => ['headline'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all'        => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['edit'],
                'href'  => 'table=tl_content',
                'icon'  => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['editmeta'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['copy'],
                'href'  => 'act=paste&amp;mode=copy',
                'icon'  => 'copy.gif',
            ],
            'cut'        => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_portfolio']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_portfolio']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_portfolio', 'toggleIcon'],
            ],
            'feature'    => [
                'label'           => &$GLOBALS['TL_LANG']['tl_portfolio']['feature'],
                'icon'            => 'featured.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFeatured(this,%s)"',
                'button_callback' => ['tl_portfolio', 'iconFeatured'],
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes'    => [
        '__selector__' => ['addImage', 'source', 'overwriteMeta'],
        'default'      => '{title_legend},headline,alias,categories,client;{teaser_legend},teaser;{date_legend},date;{image_legend},addImage;{source_legend:hide},source;{expert_legend:hide},cssClass,noComments,featured;{publish_legend},published,start,stop',
    ],

    // Subpalettes
    'subpalettes' => [
        'addImage'        => 'singleSRC,size,floating,imagemargin,fullsize,overwriteMeta',
        'source_internal' => 'jumpTo',
        'source_article'  => 'articleId',
        'source_external' => 'url,target',
        'overwriteMeta'   => 'alt,imageTitle,imageUrl,caption',
    ],

    // Fields
    'fields'      => [
        'id'            => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid'           => [
            'foreignKey' => 'tl_portfolio_archive.title',
            'sql'        => "int(10) unsigned NOT NULL default 0",
            'relation'   => ['type'=>'belongsTo', 'load'=>'lazy']
        ],
        'tstamp'        => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting'       => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'headline'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['headline'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'alias'         => [
            'label'         => &$GLOBALS['TL_LANG']['tl_portfolio']['alias'],
            'exclude'       => true,
            'search'        => false,
            'inputType'     => 'text',
            'eval'          => ['rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [
                ['tl_portfolio', 'generateAlias'],
            ],
            'sql'           => "varchar(128) COLLATE utf8_bin NOT NULL default ''",
        ],
        'categories'    => [
            'label'      => &$GLOBALS['TL_LANG']['tl_portfolio']['categories'],
            'exclude'    => true,
            'filter'     => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_portfolio_category.title',
            'eval'       => ['multiple' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
            'sql'        => 'blob NULL',
        ],
        'client'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['client'],
            'exclude'   => true,
            'search'    => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class'  => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'teaser'     => [
            'label'       => &$GLOBALS['TL_LANG']['tl_portfolio']['teaser'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr'],
            'explanation' => 'insertTags',
            'sql'         => 'mediumtext NULL',
        ],
        'date'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['date'],
            'default'   => time(),
            'exclude'   => true,
            'filter'    => true,
            'sorting'   => true,
            'flag'      => 8,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'addImage'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['addImage'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'overwriteMeta' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['overwriteMeta'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'singleSRC'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql'       => 'binary(16) NULL',
        ],
        'alt'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['alt'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'imageTitle'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imageTitle'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'size'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['size'],
            'exclude'          => true,
            'inputType'        => 'imageSize',
            'reference'        => &$GLOBALS['TL_LANG']['MSC'],
            'eval'             => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => function () {
                return System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(BackendUser::getInstance());
            },
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imagemargin'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
            'exclude'   => true,
            'inputType' => 'trbl',
            'options'   => $GLOBALS['TL_CSS_UNITS'],
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'imageUrl'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['imageUrl'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'fullsize'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'caption'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['caption'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'floating'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['floating'],
            'default'   => 'above',
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => ['above', 'left', 'right', 'below'],
            'eval'      => ['cols' => 4, 'tl_class' => 'w50'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'source'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_portfolio']['source'],
            'default'          => 'default',
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'radio',
            'options_callback' => ['tl_portfolio', 'getSourceOptions'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_portfolio'],
            'eval'             => ['submitOnChange' => true, 'helpwizard' => true],
            'sql'              => "varchar(12) NOT NULL default ''",
        ],
        'jumpTo'        => [
            'label'      => &$GLOBALS['TL_LANG']['tl_portfolio']['jumpTo'],
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['mandatory' => true, 'fieldType' => 'radio'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'articleId'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_portfolio']['articleId'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['tl_portfolio', 'getArticleAlias'],
            'eval'             => ['chosen' => true, 'mandatory' => true],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'url'           => [
            'label'     => &$GLOBALS['TL_LANG']['MSC']['url'],
            'exclude'   => true,
            'search'    => false,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'target'        => [
            'label'     => &$GLOBALS['TL_LANG']['MSC']['target'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'cssClass'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'published'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['published'],
            'exclude'   => true,
            'filter'    => true,
            'flag'      => 1,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'featured'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_portfolio']['featured'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_portfolio.
 */
class tl_portfolio extends Backend
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
     * Check permissions to edit table tl_portfolio
     *
     * @throws AccessDeniedException
     */
    public function checkPermission(): void
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Set the root IDs
        if (empty($this->User->portfolio) || !is_array($this->User->portfolio))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->portfolio;
        }

        $id = Input::get('id') !== '' ? Input::get('id') : CURRENT_ID;

        // Check current action
        switch (Input::get('act'))
        {
            case 'paste':
            case 'select':
                // Check CURRENT_ID here (see #247)
                if (!in_array(CURRENT_ID, $root, true))
                {
                    throw new AccessDeniedException('Not enough permissions to access portfolio archive ID ' . $id . '.');
                }
                break;

            case 'create':
                if (!Input::get('pid') || !in_array(Input::get('pid'), $root, true))
                {
                    throw new AccessDeniedException('Not enough permissions to create portfolio items in portfolio archive ID ' . Input::get('pid') . '.');
                }
                break;

            case 'cut':
            case 'copy':
                if (Input::get('act') === 'cut' && Input::get('mode') === 1)
                {
                    $objArchive = $this->Database->prepare("SELECT pid FROM tl_portfolio WHERE id=?")
                        ->limit(1)
                        ->execute(Input::get('pid'));

                    if ($objArchive->numRows < 1)
                    {
                        throw new AccessDeniedException('Invalid portfolio item ID ' . Input::get('pid') . '.');
                    }

                    $pid = $objArchive->pid;
                }
                else
                {
                    $pid = Input::get('pid');
                }

                if (!in_array($pid, $root, true))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' portfolio item ID ' . $id . ' to portfolio archive ID ' . $pid . '.');
                }
            // no break

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $this->Database->prepare("SELECT pid FROM tl_portfolio WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new AccessDeniedException('Invalid portfolio item ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root, true))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' portfolio item ID ' . $id . ' of portfolio archive ID ' . $objArchive->pid . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root, true))
                {
                    throw new AccessDeniedException('Not enough permissions to access portfolio archive ID ' . $id . '.');
                }

                $objArchive = $this->Database->prepare("SELECT id FROM tl_portfolio WHERE pid=?")
                    ->execute($id);

                /** @var SessionInterface $objSession */
                $objSession = System::getContainer()->get('session');

                $session = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $objSession->replace($session);
                break;

            default:
                if (Input::get('act'))
                {
                    throw new AccessDeniedException('Invalid command "' . Input::get('act') . '".');
                }

                if (!in_array($id, $root, true))
                {
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
        return '<div class="tl_content_left">'.$arrRow['headline'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('dateFormat'), $arrRow['date']).']</span></div>';
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
    public function generateAlias($varValue, DataContainer $dc): string
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ('' === $varValue) {
            $autoAlias = true;
            $varValue  = StringUtil::standardize(StringUtil::restoreBasicEntities($dc->activeRecord->headline));
        }

        $objAlias = $this->Database->prepare('SELECT id FROM tl_portfolio WHERE alias=?')
            ->execute($varValue);

        // Check whether the portfolio alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-'.$dc->id;
        }

        return $varValue;
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
        $arrPids  = [];
        $arrAlias = [];

        if (!$this->User->isAdmin) {
            foreach ($this->User->pagemounts as $id) {
                $arrPids[] = $id;
                $arrPids   = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
            }

            if (empty($arrPids)) {
                return $arrAlias;
            }

            $objAlias = $this->Database->prepare('SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN('.implode(',', array_map('intval', array_unique($arrPids))).') ORDER BY parent, a.sorting')
                ->execute($dc->id);
        } else {
            $objAlias = $this->Database->prepare('SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting')
                ->execute($dc->id);
        }

        if ($objAlias->numRows) {
            System::loadLanguageFile('tl_article');

            while ($objAlias->next()) {
                $arrAlias[$objAlias->parent][$objAlias->id] = $objAlias->title.' ('.($GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn] ?: $objAlias->inColumn).', ID '.$objAlias->id.')';
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
            $arrOptions   = array_unique($arrOptions);
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

        $arrSet['date'] = strtotime(date('Y-m-d', ((int) $dc->activeRecord->date)));
        $this->Database->prepare('UPDATE tl_portfolio %s WHERE id=?')->set($arrSet)->execute($dc->id);
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string|null $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon(array $row, ?string $href, string $label, string $title, string $icon, string $attributes): string
    {
        if (Input::get('tid'))
        {
            $this->toggleVisibility(Contao\Input::get('tid'), (Contao\Input::get('state') == 1), (func_num_args() <= 12 ? null : func_get_arg(12)));
            self::redirect(self::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_portfolio::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="' . self::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    /**
     * Disable/enable a portfolio item
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer|null $dc
     */
    public function toggleVisibility($intId, $blnVisible, Contao\DataContainer $dc=null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_portfolio']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_portfolio']['config']['onload_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_portfolio::published', 'alexf'))
        {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish portfolio item ID ' . $intId . '.');
        }

        $objRow = $this->Database->prepare("SELECT * FROM tl_portfolio WHERE id=?")
            ->limit(1)
            ->execute($intId);

        if ($objRow->numRows < 1)
        {
            throw new AccessDeniedException('Invalid portfolio item ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $dc->activeRecord = $objRow;
        }

        $objVersions = new Versions('tl_portfolio', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_portfolio']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_portfolio']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_portfolio SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_portfolio']['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_portfolio']['config']['onsubmit_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
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
        $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));

        return '<a href="'.self::addToUrl('act='.$arrClipboard['mode'].'&mode=1&pid='.$row['id']).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
    }

    /**
     * Return the "feature/unfeature element" button
     *
     * @param array $row
     * @param string|null $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function iconFeatured(array $row, ?string $href, string $label, string $title, string $icon, string $attributes): string
    {
        if (Input::get('fid'))
        {
            $this->toggleFeatured(Input::get('fid'), (Input::get('state') === 1), (@func_get_arg(12) ?: null));
            self::redirect(self::getReferer());
        }

        // Check permissions AFTER checking the fid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_portfolio::featured', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;fid=' . $row['id'] . '&amp;state=' . ($row['featured'] ? '' : 1);

        if (!$row['featured'])
        {
            $icon = 'featured_.svg';
        }

        return '<a href="' . self::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['featured'] ? 1 : 0) . '"') . '</a> ';
    }

    /**
     * Feature/unfeature a portfolio item
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer|null $dc
     *
     */
    public function toggleFeatured(int $intId, bool $blnVisible, DataContainer $dc=null): void
    {
        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'feature');

        $this->checkPermission();

        // Check permissions to feature
        if (!$this->User->hasAccess('tl_portfolio::featured', 'alexf'))
        {
            throw new AccessDeniedException('Not enough permissions to feature/unfeature portfolio item ID ' . $intId . '.');
        }

        $objVersions = new Versions('tl_portfolio', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_portfolio']['fields']['featured']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_portfolio']['fields']['featured']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_portfolio SET tstamp=" . time() . ", featured='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
    }
}
