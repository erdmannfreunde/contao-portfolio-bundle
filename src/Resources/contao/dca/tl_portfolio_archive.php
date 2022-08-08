<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

use Contao\CoreBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

$GLOBALS['TL_DCA']['tl_portfolio_archive'] = [
    // Config
    'config' => [
        'dataContainer'               => 'Table',
        'ctable'                      => ['tl_portfolio'],
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_portfolio_archive', 'checkPermission')
        ),
        'oncreate_callback' => array
        (
            array('tl_portfolio_archive', 'adjustPermissions')
        ),
        'oncopy_callback' => array
        (
            array('tl_portfolio_archive', 'adjustPermissions')
        ),
        'oninvalidate_cache_tags_callback' => array
        (
            array('tl_portfolio_archive', 'addSitemapCacheInvalidationTag'),
        ),
        'sql'                         => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => 1,
            'fields'                  => ['title'],
            'flag'                    => 1,
            'panelLayout'             => 'search,limit',
        ],
        'label' => [
            'fields'                  => ['title'],
            'format'                  => '%s',
        ],
        'global_operations' => [
            'categories' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['categories'],
                'href'       => 'table=tl_portfolio_category',
                'icon'       => 'bundles/eufportfolio/icon.png',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
            ],
            'all' => [
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['edit'],
                'href'                => 'table=tl_portfolio',
                'icon'                => 'edit.svg',
            ],
            'editheader' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.svg',
                'button_callback'     => ['tl_portfolio_archive', 'editHeader'],
            ],
            'copy' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg',
                'button_callback'     => ['tl_portfolio_archive', 'copyArchive'],
            ],
            'delete' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => ['tl_portfolio_archive', 'deleteArchive'],
            ],
            'show' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__'                => ['protected'],
        'default'                     => '{title_legend},title,jumpTo;{protected_legend:hide},protected;',
    ],


    // Subpalettes
    'subpalettes' => [
        'protected'                   => 'groups',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'                     => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'jumpTo' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['jumpTo'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => ['mandatory'=>true, 'fieldType'=>'radio', 'tl_class'=>'clr'],
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => ['type'=>'hasOne', 'load'=>'lazy']
        ),
        'protected' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['protected'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['submitOnChange'=>true, 'isBoolean'=>true],
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'groups' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['groups'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_member_group.name',
            'eval'                    => ['mandatory'=>true, 'multiple'=>true],
            'sql'                     => "blob NULL",
            'relation'                => ['type'=>'hasMany', 'load'=>'lazy']
        ),
    ],
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Dennis Erdmann
 */
class tl_portfolio_archive extends Backend
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
     * Check permissions to edit table tl_portfolio_archive
     *
     * @throws AccessDeniedException
     * @return void
     */
    public function checkPermission(): void
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (empty($this->User->portfolio) || !is_array($this->User->portfolio))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->portfolio;
        }

        $GLOBALS['TL_DCA']['tl_portfolio_archive']['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$this->User->hasAccess('create', 'portfoliop'))
        {
            $GLOBALS['TL_DCA']['tl_portfolio_archive']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_portfolio_archive']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_portfolio_archive']['config']['notCopyable'] = true;
        }

        // Check permissions to delete calendars
        if (!$this->User->hasAccess('delete', 'portfoliop'))
        {
            $GLOBALS['TL_DCA']['tl_portfolio_archive']['config']['notDeletable'] = true;
        }

        /** @var SessionInterface $objSession */
        $objSession = System::getContainer()->get('session');

        // Check current action
        switch (Input::get('act'))
        {
            case 'select':
                // Allow
                break;

            case 'create':
                if (!$this->User->hasAccess('create', 'portfoliop'))
                {
                    throw new AccessDeniedException('Not enough permissions to create portfolio archives.');
                }
                break;

            case 'edit':
            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root, true) || (Input::get('act') === 'delete' && !$this->User->hasAccess('delete', 'portfoliop')))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' portfolio archive ID ' . Input::get('id') . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'copyAll':
                $session = $objSession->all();

                if (Input::get('act') === 'deleteAll' && !$this->User->hasAccess('delete', 'portfoliop'))
                {
                    $session['CURRENT']['IDS'] = array();
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if (Input::get('act'))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' portfolio archives.');
                }
                break;
        }
    }

    /**
     * Add the new archive to the permissions
     *
     * @param $insertId
     */
    public function adjustPermissions($insertId): void
    {
        // The oncreate_callback passes $insertId as second argument
        if (func_num_args() === 4)
        {
            $insertId = func_get_arg(1);
        }

        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (empty($this->User->portfolio) || !is_array($this->User->portfolio))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->portfolio;
        }

        // The archive is enabled already
        if (in_array($insertId, $root, true))
        {
            return;
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $arrNew = $objSessionBag->get('new_records');

        if (is_array($arrNew['tl_portfolio_archive']) && in_array($insertId, $arrNew['tl_portfolio_archive'], true))
        {
            // Add the permissions on group level
            if ($this->User->inherit !== 'custom')
            {
                $objGroup = $this->Database->execute("SELECT id, portfolio, portfoliop FROM tl_user_group WHERE id IN(" . implode(',', array_map('\intval', $this->User->groups)) . ")");

                while ($objGroup->next())
                {
                    $arrPortfoliop = StringUtil::deserialize($objGroup->portfoliop);

                    if (is_array($arrPortfoliop) && in_array('create', $arrPortfoliop, true))
                    {
                        $arrPortfolio = StringUtil::deserialize($objGroup->portfolio, true);
                        $arrPortfolio[] = $insertId;

                        $this->Database->prepare("UPDATE tl_user_group SET portfolio=? WHERE id=?")
                            ->execute(serialize($arrPortfolio), $objGroup->id);
                    }
                }
            }

            // Add the permissions on user level
            if ($this->User->inherit !== 'group')
            {
                $objUser = $this->Database->prepare("SELECT portfolio, portfoliop FROM tl_user WHERE id=?")
                    ->limit(1)
                    ->execute($this->User->id);

                $arrPortfoliop = StringUtil::deserialize($objUser->portfoliop);

                if (is_array($arrPortfoliop) && in_array('create', $arrPortfoliop, true))
                {
                    $arrPortfolio = StringUtil::deserialize($objUser->portfolio, true);
                    $arrPortfolio[] = $insertId;

                    $this->Database->prepare("UPDATE tl_user SET portfolio=? WHERE id=?")
                        ->execute(serialize($arrPortfolio), $this->User->id);
                }
            }

            // Add the new element to the user object
            $root[] = $insertId;
            $this->User->portfolio = $root;
        }
    }

    /**
     * Return the edit header button.
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function editHeader(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->canEditFieldsOf('tl_portfolio_archive') ? '<a href="'.self::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the copy archive button.
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function copyArchive(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->hasAccess('create', 'portfoliop') ? '<a href="'.self::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the delete archive button.
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteArchive(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->hasAccess('delete', 'portfoliop') ? '<a href="'.self::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * @param DataContainer $dc
     *
     * @param array $tags
     * @return array
     */
    public function addSitemapCacheInvalidationTag($dc, array $tags): array
    {
        $pageModel = PageModel::findWithDetails($dc->activeRecord->jumpTo);

        if ($pageModel === null)
        {
            return $tags;
        }

        return array_merge($tags, array('contao.sitemap.' . $pageModel->rootId));
    }
}
