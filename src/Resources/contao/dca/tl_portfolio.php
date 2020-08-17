<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-grid
 */

/*
 * Load tl_content language file
 */
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
            'categories' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_portfolio']['categories'],
                'href'       => 'table=tl_portfolio_category',
                'icon'       => 'bundles/eufportfolio/icon.png',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
            ],
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
        'addImage'        => 'singleSRC,imgSize,floating,imagemargin,fullsize,overwriteMeta',
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
            'sql' => "int(10) unsigned NOT NULL default '0'",
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
        'imgSize'              => [
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
     * Add the type of input field.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listItems($arrRow)
    {
        return '<div class="tl_content_left">'.$arrRow['headline'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('dateFormat'), $arrRow['date']).']</span></div>';
    }

    /**
     * Auto-generate the portfolio alias if it has not been set yet.
     *
     * @param mixed
     * @param \DataContainer
     * @param mixed $varValue
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ('' === $varValue) {
            $autoAlias = true;
            $varValue  = standardize(StringUtil::restoreBasicEntities($dc->activeRecord->headline));
        }

        $objAlias = $this->Database->prepare('SELECT id FROM tl_portfolio WHERE alias=?')
            ->execute($varValue);

        // Check whether the portfolio alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
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
     * @param \DataContainer
     *
     * @return array
     */
    public function getArticleAlias(DataContainer $dc)
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
     * @param \DataContainer
     *
     * @return array
     */
    public function getSourceOptions(DataContainer $dc)
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
     * @param \DataContainer
     */
    public function adjustTime(DataContainer $dc)
    {
        // Return if there is no active record (override all)
        if (!$dc->activeRecord) {
            return;
        }

        $arrSet['date'] = strtotime(date('Y-m-d', $dc->activeRecord->date).' '.date('H:i:s', $dc->activeRecord->time));
        $this->Database->prepare('UPDATE tl_portfolio %s WHERE id=?')->set($arrSet)->execute($dc->id);
    }

    /**
     * Return the "toggle visibility" button.
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param mixed $row
     * @param mixed $href
     * @param mixed $label
     * @param mixed $title
     * @param mixed $icon
     * @param mixed $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (Contao\Input::get('tid')) {
            $this->toggleVisibility(Contao\Input::get('tid'), (1 === Contao\Input::get('state')));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_portfolio::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Disable/enable a user group.
     *
     * @param int
     * @param bool
     * @param mixed $intId
     * @param mixed $blnVisible
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        $objVersions = new Versions('tl_portfolio', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_portfolio']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_portfolio']['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare('UPDATE tl_portfolio SET tstamp='.time().", published='".($blnVisible ? 1 : '')."' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_portfolio.id='.$intId.'" has been created'.$this->getParentEntries('tl_portfolio', $intId), __METHOD__, TL_GENERAL);
    }

    /**
     * @param $row
     * @param $table
     * @param $cr
     * @param $arrClipboard
     *
     * @return string
     */
    public function pasteElement(DataContainer $dc, $row, $table, $cr, $arrClipboard)
    {
        $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));

        return '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&mode=1&pid='.$row['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
    }

    /**
     * Return the "feature/unfeature element" button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
    {
        if (Contao\Input::get('fid')) {
            $this->toggleFeatured(Contao\Input::get('fid'), (1 === Contao\Input::get('state')), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;fid='.$row['id'].'&amp;state='.($row['featured'] ? '' : 1);

        if (!$row['featured']) {
            $icon = 'featured_.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.Contao\StringUtilUtil::specialchars($title).'"'.$attributes.'>'.Contao\Image::getHtml($icon, $label, 'data-state="'.($row['featured'] ? 1 : 0).'"').'</a> ';
    }

    /**
     * Feature/unfeature a news item.
     *
     * @param int                  $intId
     * @param bool                 $blnVisible
     * @param Contao\DataContainer $dc
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleFeatured($intId, $blnVisible, Contao\DataContainer $dc = null)
    {
        // Check permissions to edit
        Contao\Input::setGet('id', $intId);
        Contao\Input::setGet('act', 'feature');

        $objVersions = new Contao\Versions('tl_portfolio', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_portfolio']['fields']['featured']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_portfolio']['fields']['featured']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare('UPDATE tl_portfolio SET tstamp='.time().", featured='".($blnVisible ? 1 : '')."' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
    }
}
