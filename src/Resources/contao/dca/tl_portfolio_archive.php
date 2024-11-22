<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DC_Table;
use Contao\Image;
use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

$GLOBALS['TL_DCA']['tl_portfolio_archive'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_portfolio'],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'onload_callback' => [
            ['tl_portfolio_archive', 'adjustDca'],
        ],
        'oncreate_callback' => [
            ['tl_portfolio_archive', 'adjustPermissions'],
        ],
        'oncopy_callback' => [
            ['tl_portfolio_archive', 'adjustPermissions'],
        ],
        'oninvalidate_cache_tags_callback' => [
            ['tl_portfolio_archive', 'addSitemapCacheInvalidationTag'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DC_Table::MODE_SORTED,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'categories' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['categories'],
                'href' => 'table=tl_portfolio_category',
                'icon' => 'bundles/eufportfolio/icon.png',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="c"',
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['editheader'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
                'button_callback' => ['tl_portfolio_archive', 'editHeader'],
            ],
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['edit'],
                'href' => 'table=tl_portfolio',
                'icon' => 'children.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
                'button_callback' => ['tl_portfolio_archive', 'copyArchive'],
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => ['tl_portfolio_archive', 'deleteArchive'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        '__selector__' => ['protected'],
        'default' => '{title_legend},title,jumpTo;{protected_legend:hide},protected;',
    ],

    'subpalettes' => [
        'protected' => 'groups',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'jumpTo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['jumpTo'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'protected' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['protected'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'isBoolean' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'groups' => [
            'label' => &$GLOBALS['TL_LANG']['tl_portfolio_archive']['groups'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true],
            'sql' => 'blob NULL',
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
    ],
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_portfolio_archive extends Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Set the root IDs.
     */
    public function adjustDca()
    {
        $user = BackendUser::getInstance();

        if ($user->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (empty($user->news) || !is_array($user->news))
        {
            $root = array(0);
        }
        else
        {
            $root = $user->news;
        }

        $GLOBALS['TL_DCA']['tl_news_archive']['list']['sorting']['root'] = $root;
    }

    /**
     * Add the new archive to the permissions.
     */
    public function adjustPermissions($insertId): void
    {
        // The oncreate_callback passes $insertId as second argument
        if (4 === func_num_args()) {
            $insertId = func_get_arg(1);
        }

        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (empty($this->User->portfolio) || !is_array($this->User->portfolio)) {
            $root = [0];
        } else {
            $root = $this->User->portfolio;
        }

        // The archive is enabled already
        if (in_array($insertId, $root, true)) {
            return;
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $arrNew = $objSessionBag->get('new_records');

        if (is_array($arrNew['tl_portfolio_archive']) && in_array($insertId, $arrNew['tl_portfolio_archive'], true)) {
            // Add the permissions on group level
            if ('custom' !== $this->User->inherit) {
                $objGroup = $this->Database::getInstance()
                    ->execute('SELECT id, portfolio, portfoliop FROM tl_user_group WHERE id IN('.implode(',', array_map('\intval', $this->User->groups)).')')
                ;

                while ($objGroup->next()) {
                    $arrPortfoliop = StringUtil::deserialize($objGroup->portfoliop);

                    if (is_array($arrPortfoliop) && in_array('create', $arrPortfoliop, true)) {
                        $arrPortfolio = StringUtil::deserialize($objGroup->portfolio, true);
                        $arrPortfolio[] = $insertId;

                        $this->Database::getInstance()
                            ->prepare('UPDATE tl_user_group SET portfolio=? WHERE id=?')
                            ->execute(serialize($arrPortfolio), $objGroup->id)
                        ;
                    }
                }
            }

            // Add the permissions on user level
            if ('group' !== $this->User->inherit) {
                $objUser = $this->Database::getInstance()
                    ->prepare('SELECT portfolio, portfoliop FROM tl_user WHERE id=?')
                    ->limit(1)
                    ->execute($this->User->id)
                ;

                $arrPortfoliop = StringUtil::deserialize($objUser->portfoliop);

                if (is_array($arrPortfoliop) && in_array('create', $arrPortfoliop, true)) {
                    $arrPortfolio = StringUtil::deserialize($objUser->portfolio, true);
                    $arrPortfolio[] = $insertId;

                    $this->Database::getInstance()
                        ->prepare('UPDATE tl_user SET portfolio=? WHERE id=?')
                        ->execute(serialize($arrPortfolio), $this->User->id)
                    ;
                }
            }

            // Add the new element to the user object
            $root[] = $insertId;
            $this->User->portfolio = $root;
        }
    }

    /**
     * Return the edit header button.
     */
    public function editHeader(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        //return $this->User->canEditFieldsOf('tl_portfolio_archive') ? '<a href="'.self::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
        //if (!$this->User->canEditFieldsOf('tl_portfolio_archive')) {
        //    return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
        //}

        return '<a href="'.self::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a>';
    }

    /**
     * Return the copy archive button.
     */
    public function copyArchive(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->hasAccess('create', 'portfoliop') ? '<a href="'.self::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    /**
     * Return the delete archive button.
     */
    public function deleteArchive(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        return $this->User->hasAccess('delete', 'portfoliop') ? '<a href="'.self::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    public function addSitemapCacheInvalidationTag($dc, array $tags): array
    {
        $pageModel = PageModel::findWithDetails($dc->activeRecord->jumpTo);

        if (null === $pageModel) {
            return $tags;
        }

        return array_merge($tags, ['contao.sitemap.'.$pageModel->rootId]);
    }
}
