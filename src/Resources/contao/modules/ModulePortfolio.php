<?php

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace EuF\PortfolioBundle\Modules;

/**
 * Class ModulePortfolio
 *
 * Parent class for portfolio modules.
 */
abstract class ModulePortfolio extends \Module
{

    /**
     * URL cache array
     * @var array
     */
    private static $arrUrlCache = array();


    /**
     * Parse an item and return it as string
     * @param object
     * @param boolean
     * @param string
     * @param integer
     * @return string
     */
    protected function parseItem($objItem, $strClass='', $intCount=0)
    {
        global $objPage;

        $objTemplate = new \FrontendTemplate($this->portfolio_template);
        $objTemplate->setData($objItem->row());

        $objTemplate->class = (($objItem->cssClass != '') ? ' ' . $objItem->cssClass : '') . $strClass;
        $objTemplate->headline = $objItem->headline;
        $objTemplate->linkHeadline = $this->generateLink($objItem->headline, $objItem, $blnAddArchive);
        $objTemplate->link = $this->generatePortfolioUrl($objItem, $blnAddArchive);
        $objTemplate->count = $intCount; // see #5708
        $objTemplate->text = '';

        // Clean the RTE output
        if ($objItem->teaser != '')
        {
            if ($objPage->outputFormat == 'xhtml')
            {
                $objTemplate->teaser = \String::toXhtml($objItem->teaser);
            }
            else
            {
                $objTemplate->teaser = \String::toHtml5($objItem->teaser);
            }

            $objTemplate->teaser = \String::encodeEmail($objTemplate->teaser);
        }

        // Display the "read more" button for external/article links
        if ($objItem->source != 'default')
        {
            $objTemplate->text = true;
        }

        // Compile the portfolio text
        else
        {
            $objElement = \ContentModel::findPublishedByPidAndTable($objItem->id, 'tl_portfolio');
            if ($objElement !== null)
            {
                while ($objElement->next())
                {
                    $objTemplate->text .= $this->getContentElement($objElement->current());
                }
            }
        }

        // Add the meta information
        $objTemplate->date = $arrMeta['date'];
        $objTemplate->hasMetaFields = !empty($arrMeta);
        $objTemplate->numberOfComments = $arrMeta['ccount'];
        $objTemplate->commentCount = $arrMeta['comments'];
        $objTemplate->timestamp = $objItem->date;
        $objTemplate->author = $arrMeta['author'];
        $objTemplate->datetime = date('Y-m-d\TH:i:sP', $objItem->date);
        
		$objTemplate->addImage = false;

		// Add an image
		if ($objItem->addImage && $objItem->singleSRC != '')
		{
			$objModel = \FilesModel::findByUuid($objItem->singleSRC);

			if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
			{
				// Do not override the field now that we have a model registry (see #6303)
				$arrArticle = $objItem->row();

				// Override the default image size
				if ($this->imgSize != '')
				{
					$size = \StringUtil::deserialize($this->imgSize);

					if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
					{
						$arrArticle['size'] = $this->imgSize;
					}
				}

				$arrArticle['singleSRC'] = $objModel->path;
				$this->addImageToTemplate($objTemplate, $arrArticle, null, null, $objModel);
			}
		}

        return $objTemplate->parse();
    }


    /**
     * Parse one or more items and return them as array
     * @param object
     * @return array
     */
    protected function parseItems($objItems)
    {
        $limit = $objItems->count();

        if ($limit < 1)
        {
            return array();
        }

        $count = 0;
        $arrArticles = array();

        while ($objItems->next())
        {
            $strClass = ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even');
            $arrArticles[] = $this->parseItem($objItems, $strClass, $count);
        }

        return $arrArticles;
    }

    /**
     * Generate a URL and return it as string
     * @param object
     * @param boolean
     * @return string
     */
    protected function generatePortfolioUrl($objItem, $blnAddArchive=false)
    {
        $strCacheKey = 'id_' . $objItem->id;

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
                if (substr($objItem->url, 0, 7) == 'mailto:')
                {
                    self::$arrUrlCache[$strCacheKey] = \String::encodeEmail($objItem->url);
                }
                else
                {
                    self::$arrUrlCache[$strCacheKey] = ampersand($objItem->url);
                }
                break;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objItem->getRelated('jumpTo')) !== null)
                {
                    self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objTarget->row()));
                }
                break;

            // Link to an article
            case 'article':
                if (($objItem = \ArticleModel::findByPk($objItem->articleId, array('eager'=>true))) !== null && ($objPid = $objItem->getRelated('pid')) !== null)
                {
                    self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objPid->row(), '/articles/' . ((!\Config::get('disableAlias') && $objItem->alias != '') ? $objItem->alias : $objItem->id)));
                }
                break;
        }

        // Link to the default page
        if (self::$arrUrlCache[$strCacheKey] === null)
        {
            $objPage = \PageModel::findByPk($this->jumpTo);

            if ($objPage === null)
            {
                self::$arrUrlCache[$strCacheKey] = ampersand(\Environment::get('request'), true);
            }
            else
            {
                self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objPage->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/' : '/items/') . ((!\Config::get('disableAlias') && $objItem->alias != '') ? $objItem->alias : $objItem->id)));
            }
        }

        return self::$arrUrlCache[$strCacheKey];
    }


    /**
     * Generate a link and return it as string
     * @param string
     * @param object
     * @param boolean
     * @param boolean
     * @return string
     */
    protected function generateLink($strLink, $objItem, $blnAddArchive=false, $blnIsReadMore=false)
    {
        // Internal link
        if ($objItem->source != 'external')
        {
            return sprintf('<a href="%s" title="%s">%s%s</a>',
                $this->generatePortfolioUrl($objItem, $blnAddArchive),
                specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $objItem->headline), true),
                $strLink,
                ($blnIsReadMore ? ' <span class="invisible">'.$objItem->headline.'</span>' : ''));
        }

        // Ampersand URIs
        else
        {
            $strArticleUrl = ampersand($objItem->url);
        }

        global $objPage;

        // External link
        return sprintf('<a href="%s" title="%s"%s>%s</a>',
                        $strArticleUrl,
                        specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['open'], $strArticleUrl)),
                        ($objItem->target ? (($objPage->outputFormat == 'xhtml') ? ' onclick="return !window.open(this.href)"' : ' target="_blank"') : ''),
                        $strLink);
    }
}
