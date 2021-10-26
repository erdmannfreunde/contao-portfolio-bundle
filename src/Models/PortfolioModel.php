<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\Models;

use Contao\Date;
use Contao\StringUtil;
use Contao\Model\Collection;

/**
 * Reads and writes portfolio items.
 */
class PortfolioModel extends \Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_portfolio';

    /**
     * Find a published portfolio item from one or more portfolio archives by its ID or alias
     *
     * @param mixed $varId      The numeric ID or alias name
     * @param array $arrPids    An array of parent IDs
     * @param array $arrOptions An optional options array
     *
     * @return PortfolioModel|null The model or null if there are no portfolio items
     */
    public static function findPublishedByParentAndIdOrAlias($varId, array $arrPids, array $arrOptions=array()): ?PortfolioModel
    {
        if (empty($arrPids) || !\is_array($arrPids))
        {
            return null;
        }

        $t = static::$strTable;
        $arrColumns = !preg_match('/^[1-9]\d*$/', $varId) ? array("BINARY $t.alias=?") : array("$t.id=?");
        $arrColumns[] = "$t.pid IN(" . implode(',', array_map('\intval', $arrPids)) . ")";

        if (!static::isPreviewMode($arrOptions))
        {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        return static::findOneBy($arrColumns, $varId, $arrOptions);
    }

    /**
     * Find published portfolio items by their parent ID
     *
     * @param array $arrPids An array of portfolio archive IDs
     * @param bool|null $blnFeatured If true, return only featured portfolio items, if false, return only unfeatured portfolio items
     * @param integer $intLimit An optional limit
     * @param integer $intOffset An optional offset
     * @param array $arrOptions An optional options array
     *
     * @return Collection|PortfolioModel[]|PortfolioModel|null A collection of models or null if there are no portfolio items
     */
    public static function findPublishedByPids(array $arrPids, ?bool $blnFeatured=null, int $intLimit=0, int $intOffset=0, array $arrOptions=array(), array $arrCategories=array())
    {
        if (empty($arrPids) || !\is_array($arrPids))
        {
            return null;
        }

        $t = static::$strTable;
        $arrColumns = array("$t.pid IN(" . implode(',', array_map('\intval', $arrPids)) . ")");

        if ($blnFeatured === true)
        {
            $arrColumns[] = "$t.featured='1'";
        }
        elseif ($blnFeatured === false)
        {
            $arrColumns[] = "$t.featured=''";
        }

        if (!BE_USER_LOGGED_IN || TL_MODE === 'BE')
        {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order']  = "$t.date DESC";
        }

        // check if categories are selected and filter by them
        // not working because $t.categories is still a serialized array
        if ($arrCategories) {
            $stringCategories = StringUtil::deserialize($arrCategories);
            // $arrColumns[] = "$t.categories IN(" . implode(',', array_map('\intval', $stringCategories)) . ")";
        }

        $arrOptions['limit']  = $intLimit;
        $arrOptions['offset'] = $intOffset;

        return static::findBy($arrColumns, null, $arrOptions);
    }

    /**
     * Count published portfolio items by their parent ID
     *
     * @param array $arrPids An array of portfolio archive IDs
     * @param boolean|null $blnFeatured If true, return only featured portfolio items, if false, return only unfeatured portfolio items
     * @param array $arrOptions An optional options array
     *
     * @return integer The number of portfolio items
     */
    public static function countPublishedByPids(array $arrPids, ?bool $blnFeatured=null, array $arrOptions=array()): int
    {
        if (empty($arrPids) || !\is_array($arrPids))
        {
            return 0;
        }

        $t = static::$strTable;
        $arrColumns = array("$t.pid IN(" . implode(',', array_map('\intval', $arrPids)) . ")");

        if ($blnFeatured === true)
        {
            $arrColumns[] = "$t.featured='1'";
        }
        elseif ($blnFeatured === false)
        {
            $arrColumns[] = "$t.featured=''";
        }

        if (!static::isPreviewMode($arrOptions))
        {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        return static::countBy($arrColumns, null, $arrOptions);
    }

    /**
     * Find published portfolio items by their parent ID
     *
     * @param integer $intId      The portfolio archive ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     *
     * @return Collection|PortfolioModel[]|PortfolioModel|null A collection of models or null if there are no portfolio items
     */
    public static function findPublishedByPid(int $intId, int $intLimit=0, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.pid=?");

        if (!static::isPreviewMode($arrOptions))
        {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order'] = "$t.date DESC";
        }

        if ($intLimit > 0)
        {
            $arrOptions['limit'] = $intLimit;
        }

        return static::findBy($arrColumns, $intId, $arrOptions);
    }

    /**
     * Find published portfolio items with the default redirect target by their parent ID
     *
     * @param integer $intPid     The portfolio archive ID
     * @param array   $arrOptions An optional options array
     *
     * @return Collection|PortfolioModel[]|PortfolioModel|null A collection of models or null if there are no portfolio items
     */
    public static function findPublishedDefaultByPid(int $intPid, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.pid=? AND $t.source='default'");

        if (!static::isPreviewMode($arrOptions))
        {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order'] = "$t.date DESC";
        }

        return static::findBy($arrColumns, $intPid, $arrOptions);
    }
}
