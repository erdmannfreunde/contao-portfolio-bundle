<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\Classes;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Environment;
use Contao\Frontend;
use Contao\PageModel;
use Contao\StringUtil;
use EuF\PortfolioBundle\Models\PortfolioArchiveModel;
use EuF\PortfolioBundle\Models\PortfolioModel;

class Portfolio extends Frontend
{
    /**
     * URL cache array.
     *
     * @var array
     */
    private static $arrUrlCache = [];

    /**
     * Generate a URL and return it as string.
     *
     * @param bool $blnAddArchive
     * @param bool $blnAbsolute
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function generatePortfolioUrl(PortfolioModel $objItem, $blnAddArchive = false, $blnAbsolute = false): ?string
    {
        $strCacheKey = 'id_'.$objItem->id.($blnAbsolute ? '_absolute' : '');

        // Load the URL from cache
        if (isset(self::$arrUrlCache[$strCacheKey])) {
            return self::$arrUrlCache[$strCacheKey];
        }

        // Initialize the cache
        self::$arrUrlCache[$strCacheKey] = null;

        switch ($objItem->source) {
            // Link to an external page
            case 'external':
                if (0 === strncmp($objItem->url, 'mailto:', 7)) {
                    self::$arrUrlCache[$strCacheKey] = StringUtil::encodeEmail($objItem->url);
                } else {
                    self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;', $objItem->url);
                }
                break;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objItem->getRelated('jumpTo')) instanceof PageModel) {
                    /** @var PageModel $objTarget */
                    self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;', $blnAbsolute ? $objTarget->getAbsoluteUrl() : $objTarget->getFrontendUrl());
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = ArticleModel::findByPk($objItem->articleId)) instanceof ArticleModel && ($objPid = $objArticle->getRelated('pid')) instanceof PageModel) {
                    $params = '/articles/'.($objArticle->alias ?: $objArticle->id);

                    /** @var PageModel $objPid */
                    self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;', $blnAbsolute ? $objPid->getAbsoluteUrl($params) : $objPid->getFrontendUrl($params));
                }
                break;
        }

        // Link to the default page
        if (null === self::$arrUrlCache[$strCacheKey]) {
            $objPage = PageModel::findByPk($objItem->getRelated('pid')->jumpTo);

            if (!$objPage instanceof PageModel) {
                self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;', Environment::get('request'));
            } else {
                $params = '/'.($objItem->alias ?: $objItem->id);
                self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;', $blnAbsolute ? $objPage->getAbsoluteUrl($params) : $objPage->getFrontendUrl($params));
            }
        }

        return self::$arrUrlCache[$strCacheKey];
    }

    /**
     * Check whether the detail page of a portfolio item has any content.
     *
     * Only content elements count, a teaser alone does not: it is list view
     * copy, not a page of its own (see hasText in portfolio_short).
     */
    public static function hasContent(PortfolioModel $objItem): bool
    {
        return ContentModel::countPublishedByPidAndTable((int) $objItem->id, 'tl_portfolio') > 0;
    }
}
