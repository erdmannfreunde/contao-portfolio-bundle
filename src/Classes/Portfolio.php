<?php

namespace EuF\PortfolioBundle\Classes;

use Contao\ArticleModel;
use Contao\Config;
use Contao\Environment;
use Contao\Frontend;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use EuF\PortfolioBundle\Models\PortfolioArchiveModel;
use EuF\PortfolioBundle\Models\PortfolioModel;

class Portfolio extends Frontend {
    /**
     * URL cache array
     * @var array
     */
    private static $arrUrlCache = array();

    /**
     * Add news items to the indexer
     *
     * @param array $arrPages
     * @param integer $intRoot
     * @param boolean $blnIsSitemap
     *
     * @return array
     * @throws \Exception
     */
    public function getSearchablePages(array $arrPages, $intRoot=0, $blnIsSitemap=false): array
    {
        $arrRoot = array();

        if ($intRoot > 0)
        {
            $arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');
        }

        $arrProcessed = array();
        $time = time();

        // Get all news archives
        $objArchive = PortfolioArchiveModel::findByProtected('');

        // Walk through each archive
        if ($objArchive !== null)
        {
            while ($objArchive->next())
            {
                // Skip news archives without target page
                if (!$objArchive->jumpTo)
                {
                    continue;
                }

                // Skip news archives outside the root nodes
                if (!empty($arrRoot) && !\in_array($objArchive->jumpTo, $arrRoot, true))
                {
                    continue;
                }

                // Get the URL of the jumpTo page
                if (!isset($arrProcessed[$objArchive->jumpTo]))
                {
                    $objParent = PageModel::findWithDetails($objArchive->jumpTo);

                    // The target page does not exist
                    if ($objParent === null)
                    {
                        continue;
                    }

                    // The target page has not been published (see #5520)
                    if (!$objParent->published || ($objParent->start && $objParent->start > $time) || ($objParent->stop && $objParent->stop <= $time))
                    {
                        continue;
                    }

                    if ($blnIsSitemap)
                    {
                        // The target page is protected (see #8416)
                        if ($objParent->protected)
                        {
                            continue;
                        }

                        // The target page is exempt from the sitemap (see #6418)
                        if ($objParent->robots === 'noindex,nofollow')
                        {
                            continue;
                        }
                    }

                    // Generate the URL
                    $arrProcessed[$objArchive->jumpTo] = $objParent->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
                }

                $strUrl = $arrProcessed[$objArchive->jumpTo];

                // Get the items
                $objArticle = PortfolioModel::findPublishedDefaultByPid($objArchive->id);

                if ($objArticle !== null)
                {
                    while ($objArticle->next())
                    {
                        if ($blnIsSitemap && $objArticle->robots === 'noindex,nofollow')
                        {
                            continue;
                        }

                        $arrPages[] = $this->getLink($objArticle->current(), $strUrl);
                    }
                }
            }
        }

        return $arrPages;
    }

    /**
     * Generate a URL and return it as string
     *
     * @param PortfolioModel $objItem
     * @param boolean $blnAddArchive
     * @param boolean $blnAbsolute
     *
     * @return string
     * @throws \Exception
     */
    public static function generatePortfolioUrl(PortfolioModel $objItem, $blnAddArchive=false, $blnAbsolute=false): ?string
    {
        $strCacheKey = 'id_' . $objItem->id . ($blnAbsolute ? '_absolute' : '');

        // Load the URL from cache
        if (isset(self::$arrUrlCache[$strCacheKey]))
        {
            return self::$arrUrlCache[$strCacheKey];
        }

        // Initialize the cache
        self::$arrUrlCache[$strCacheKey] = null;

        switch ($objItem->source)
        {
            // Link to an external page
            case 'external':
                if (0 === strncmp($objItem->url, 'mailto:', 7))
                {
                    self::$arrUrlCache[$strCacheKey] = StringUtil::encodeEmail($objItem->url);
                }
                else
                {
                    self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;', $objItem->url);
                }
                break;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objItem->getRelated('jumpTo')) instanceof PageModel)
                {
                    /** @var PageModel $objTarget */
                    self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;',$blnAbsolute ? $objTarget->getAbsoluteUrl() : $objTarget->getFrontendUrl());
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = ArticleModel::findByPk($objItem->articleId)) instanceof ArticleModel && ($objPid = $objArticle->getRelated('pid')) instanceof PageModel)
                {
                    $params = '/articles/' . ($objArticle->alias ?: $objArticle->id);

                    /** @var PageModel $objPid */
                    self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;',$blnAbsolute ? $objPid->getAbsoluteUrl($params) : $objPid->getFrontendUrl($params));
                }
                break;
        }

        // Link to the default page
        if (self::$arrUrlCache[$strCacheKey] === null)
        {
            $objPage = PageModel::findByPk($objItem->getRelated('pid')->jumpTo);

            if (!$objPage instanceof PageModel)
            {
                self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;', Environment::get('request'));
            }
            else
            {
                $params = (Config::get('useAutoItem') ? '/' : '/items/') . ($objItem->alias ?: $objItem->id);

                self::$arrUrlCache[$strCacheKey] = preg_replace('/&(amp;)?/i', '&amp;',$blnAbsolute ? $objPage->getAbsoluteUrl($params) : $objPage->getFrontendUrl($params));
            }
        }

        return self::$arrUrlCache[$strCacheKey];
    }

    /**
     * Return the link of a portfolio article
     *
     * @param PortfolioModel $objItem
     * @param string $strUrl
     * @param string $strBase
     *
     * @return string
     * @throws \Exception
     */
    protected function getLink(PortfolioModel $objItem, string $strUrl, $strBase=''): string
    {
        switch ($objItem->source)
        {
            // Link to an external page
            case 'external':
                return $objItem->url;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objItem->getRelated('jumpTo')) instanceof PageModel)
                {
                    /** @var PageModel $objTarget */
                    return $objTarget->getAbsoluteUrl();
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = ArticleModel::findByPk($objItem->articleId)) instanceof ArticleModel && ($objPid = $objArticle->getRelated('pid')) instanceof PageModel)
                {
                    /** @var PageModel $objPid */
                    return ampersand($objPid->getAbsoluteUrl('/articles/' . ($objArticle->alias ?: $objArticle->id)));
                }
                break;
        }

        // Backwards compatibility (see #8329)
        if ($strBase && !preg_match('#^https?://#', $strUrl))
        {
            $strUrl = $strBase . $strUrl;
        }

        // Link to the default page
        return sprintf(preg_replace('/%(?!s)/', '%%', $strUrl), ($objItem->alias ?: $objItem->id));
    }
}
