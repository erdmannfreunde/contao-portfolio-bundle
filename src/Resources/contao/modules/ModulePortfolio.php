<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-grid
 */

namespace EuF\PortfolioBundle\Modules;

use Contao\Date;
use Contao\StringUtil;
use EuF\PortfolioBundle\Models\PortfolioCategoryModel;

/**
 * Class ModulePortfolio.
 *
 * Parent class for portfolio modules.
 */
abstract class ModulePortfolio extends \Module
{
    /**
     * URL cache array.
     *
     * @var array
     */
    private static $arrUrlCache = [];

    /**
     * Parse an item and return it as string.
     *
     * @param object
     * @param bool
     * @param string
     * @param int
     * @param mixed $objItem
     * @param mixed $strClass
     * @param mixed $intCount
     *
     * @return string
     */
    protected function parseItem($objItem, $strClass='', $intCount=0)
    {
        global $objPage;

        $objTemplate = new \FrontendTemplate($this->portfolio_template);
        $objTemplate->setData($objItem->row());

        $objTemplate->class        = (('' !== $objItem->cssClass) ? ' '.$objItem->cssClass : '').$strClass;
        $objTemplate->headline     = $objItem->headline;
        $objTemplate->linkHeadline = $this->generateLink($objItem->headline, $objItem, $blnAddArchive);
        $objTemplate->link         = $this->generatePortfolioUrl($objItem, $blnAddArchive);
        $objTemplate->count        = $intCount; // see #5708
        $objTemplate->text         = '';

        // Clean the RTE output
        if ('' !== $objItem->teaser) {
            if ('xhtml' === $objPage->outputFormat) {
                $objTemplate->teaser = \String::toXhtml($objItem->teaser);
            } else {
                $objTemplate->teaser = \String::toHtml5($objItem->teaser);
            }

            $objTemplate->teaser = \String::encodeEmail($objTemplate->teaser);
        }

        // Display the "read more" button for external/article links
        if ('default' !== $objItem->source) {
            $objTemplate->text = true;
        }

        // Compile the portfolio text
        else {
            $objElement = \ContentModel::findPublishedByPidAndTable($objItem->id, 'tl_portfolio');
            if (null !== $objElement) {
                while ($objElement->next()) {
                    $objTemplate->text .= $this->getContentElement($objElement->current());
                }
            }
        }

        // Add the meta information
        $objTemplate->date      = Date::parse($objPage->dateFormat, $objItem->date);
        $objTemplate->timestamp = $objItem->date;
        $objTemplate->author    = $arrMeta['author'];
        $objTemplate->datetime  = date('Y-m-d\TH:i:sP', $objItem->date);

        if ($objItem->categories) {
            $objTemplate->categories = '';
            $categories              = unserialize($objItem->categories);
            foreach ($categories as $category) {
                $objPortfolioCategoryModel = PortfolioCategoryModel::findByPk($category);
                $objCategories[]           = $objPortfolioCategoryModel->alias;
                if (!$objTemplate->category_titles) {
                    $objTemplate->category_titles = '<ul class="level_1"><li>'.$objPortfolioCategoryModel->title.'</li>';
                } else {
                    $objTemplate->category_titles .= '<li>'.$objPortfolioCategoryModel->title.'</li>';
                }
            }
            $objTemplate->category_titles .= '</ul>';
            $objTemplate->categories .= implode(',', $objCategories);
        }

        $objTemplate->addImage = false;

        // Add an image
        if ($objItem->addImage && '' !== $objItem->singleSRC) {
            $objModel = \FilesModel::findByUuid($objItem->singleSRC);

            if (null !== $objModel && is_file(TL_ROOT.'/'.$objModel->path)) {
                // Do not override the field now that we have a model registry (see #6303)
                $arrArticle = $objItem->row();

                // Override the default image size
                if ('' !== $objItem->imgSize) {
                    $size = \StringUtil::deserialize($objItem->imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                        $arrArticle['size'] = $objItem->imgSize;
                    }
                }

                $arrArticle['singleSRC'] = $objModel->path;
                $this->addImageToTemplate($objTemplate, $arrArticle, null, null, $objModel);

                // Link to the portfolio reader if no image link has been defined (see #30)
                if (!$objTemplate->fullsize && !$objTemplate->imageUrl && $objTemplate->text) {
                    // Unset the image title attribute
                    $picture = $objTemplate->picture;
                    unset($picture['title']);
                    $objTemplate->picture = $picture;

                    // Link to the portfolio reader
                    $objTemplate->href      = $objTemplate->link;
                    $objTemplate->linkTitle = StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $objArticle->headline), true);

                    // If the external link is opened in a new window, open the image link in a new window, too
                    if ('external' === $objTemplate->source && $objTemplate->target && false === strpos($objTemplate->attributes, 'target="_blank"')) {
                        $objTemplate->attributes .= ' target="_blank"';
                    }
                }
            }
        }

        return $objTemplate->parse();
    }

    /**
     * Parse one or more items and return them as array.
     *
     * @param object
     * @param mixed $objItems
     *
     * @return array
     */
    protected function parseItems($objItems)
    {
        $limit = $objItems->count();

        if ($limit < 1) {
            return [];
        }

        $count       = 0;
        $arrArticles = [];

        while ($objItems->next()) {
            $strClass      = ((1 === ++$count) ? ' first' : '').(($count === $limit) ? ' last' : '').((0 === ($count % 2)) ? ' odd' : ' even');
            $arrArticles[] = $this->parseItem($objItems, $strClass, $count);
        }

        return $arrArticles;
    }

    /**
     * Generate a URL and return it as string.
     *
     * @param object
     * @param bool
     * @param mixed $objItem
     * @param mixed $blnAddArchive
     *
     * @return string
     */
    protected function generatePortfolioUrl($objItem, $blnAddArchive=false)
    {
        $strCacheKey = 'id_'.$objItem->id;

        // Load the URL from cache
        if (isset(self::$arrUrlCache[$strCacheKey])) {
            return self::$arrUrlCache[$strCacheKey];
        }

        // Initialize the cache
        self::$arrUrlCache[$strCacheKey] = null;

        switch ($objItem->source) {
            // Link to an external page
            case 'external':
                if ('mailto:' === substr($objItem->url, 0, 7)) {
                    self::$arrUrlCache[$strCacheKey] = \String::encodeEmail($objItem->url);
                } else {
                    self::$arrUrlCache[$strCacheKey] = ampersand($objItem->url);
                }
                break;

            // Link to an internal page
            case 'internal':
                if (null !== ($objTarget = $objItem->getRelated('jumpTo'))) {
                    self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objTarget->row()));
                }
                break;

            // Link to an article
            case 'article':
                if (null !== ($objItem = \ArticleModel::findByPk($objItem->articleId, ['eager'=>true])) && null !== ($objPid = $objItem->getRelated('pid'))) {
                    self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objPid->row(), '/articles/'.((!\Config::get('disableAlias') && '' !== $objItem->alias) ? $objItem->alias : $objItem->id)));
                }
                break;
        }

        // Link to the default page
        if (null === self::$arrUrlCache[$strCacheKey]) {
            $objPage = \PageModel::findByPk($this->jumpTo);

            if (null === $objPage) {
                self::$arrUrlCache[$strCacheKey] = ampersand(\Environment::get('request'), true);
            } else {
                self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objPage->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/' : '/items/').((!\Config::get('disableAlias') && '' !== $objItem->alias) ? $objItem->alias : $objItem->id)));
            }
        }

        return self::$arrUrlCache[$strCacheKey];
    }

    /**
     * Generate a link and return it as string.
     *
     * @param string
     * @param object
     * @param bool
     * @param bool
     * @param mixed $strLink
     * @param mixed $objItem
     * @param mixed $blnAddArchive
     * @param mixed $blnIsReadMore
     *
     * @return string
     */
    protected function generateLink($strLink, $objItem, $blnAddArchive=false, $blnIsReadMore=false)
    {
        // Internal link
        if ('external' !== $objItem->source) {
            return sprintf('<a href="%s" title="%s">%s%s</a>',
                $this->generatePortfolioUrl($objItem, $blnAddArchive),
                specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $objItem->headline), true),
                $strLink,
                ($blnIsReadMore ? ' <span class="invisible">'.$objItem->headline.'</span>' : ''));
        }

        // Ampersand URIs

        $strArticleUrl = ampersand($objItem->url);

        global $objPage;

        // External link
        return sprintf('<a href="%s" title="%s"%s>%s</a>',
                        $strArticleUrl,
                        specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['open'], $strArticleUrl)),
                        ($objItem->target ? (('xhtml' === $objPage->outputFormat) ? ' onclick="return !window.open(this.href)"' : ' target="_blank"') : ''),
                        $strLink);
    }
}
