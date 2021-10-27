<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) 2020, Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\Modules;

use Contao\StringUtil;
use Patchwork\Utf8;
use EuF\PortfolioBundle\Models\PortfolioCategoryModel;
use EuF\PortfolioBundle\Models\PortfolioModel;

/**
 * Class ModulePortfolioList.
 *
 * Front end module "portfolio list".
 */
class ModulePortfolioList extends ModulePortfolio
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_portfoliolist';

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate(): string
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['portfoliolist'][0]).' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     * @throws \Exception
     */
    protected function compile(): void
    {
        // Add the "reset categories" link
        if ($this->portfolio_filter_reset) {
            $this->Template->portfolio_filter_reset = $GLOBALS['TL_LANG']['MSC']['filter_reset'];
        }

        $objCategories = PortfolioCategoryModel::findAll([
            'column' => 'published',
            'value'  => 1,
            'order'  => 'sorting ASC',
        ]);

        if (null !== $objCategories && $this->portfolio_filter) {
            $this->Template->categories = $objCategories;
        }

        $limit = null;
        $offset = (int) $this->skipFirst;

        // Maximum number of items
        if ($this->numberOfItems > 0) {
            $limit = $this->numberOfItems;
        }

        // Handle featured portfolio-items
        if ($this->portfolio_featured == 'featured')
        {
            $blnFeatured = true;
        }
        elseif ($this->portfolio_featured == 'unfeatured')
        {
            $blnFeatured = false;
        }
        else
        {
            $blnFeatured = null;
        }

        $arrColumns = ['tl_portfolio.published=?'];
        $arrValues  = ['1'];
        $arrOptions = [
            'order' => 'tl_portfolio.sorting ASC'
        ];
        if (!$this->filter_categories && !empty($limit)) {
            $arrOptions['limit'] = $limit;
        }

        // Handle featured/unfeatured items
        if ('featured' === $this->portfolio_featured || 'unfeatured' === $this->portfolio_featured) {
            $arrColumns[] = 'tl_portfolio.featured=?';
            $arrValues[]  = 'featured' === $this->portfolio_featured ? '1' : '';
        }

        $arrPids = StringUtil::deserialize($this->portfolio_archives);
        $arrColumns[] = 'tl_portfolio.pid IN(' . implode(',', array_map('\intval', $arrPids)) . ')';

        $arrCategoryIds = array();

        // Pre-filter items based on filter_categories
        if ($this->filter_categories) {
            $arrCategoryIds = StringUtil::deserialize($this->filter_categories);
        }

        // add portfolio pagination
        // Get the total number of items
        $intTotal = $this->countItems($arrPids, $blnFeatured, $arrCategoryIds);

        if ($intTotal < 1)
        {
            return;
        }

        $total = $intTotal - $offset;

        // Split the results
        if ($this->perPage > 0 && (!isset($limit) || $this->numberOfItems > $this->perPage))
        {
            // Adjust the overall limit
            if (isset($limit))
            {
                $total = min($limit, $total);
            }

            // Get the current page
            $id = 'page_n' . $this->id;
            $page = \Contao\Input::get($id) ?? 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total/$this->perPage), 1))
            {
                throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
            }

            // Set limit and offset
            $limit = (int) $this->perPage;
            $offset += (max($page, 1) - 1) * $this->perPage;
            $skip = (int) $this->skipFirst;

            // Overall limit
            if ($offset + $limit > $total + $skip)
            {
                $limit = $total + $skip - $offset;
            }


            // Add the pagination menu
            $objPagination = new \Contao\Pagination($total, $this->perPage, \Contao\Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        $objItems = $this->fetchItems($arrPids, $blnFeatured, ($limit ?: 0), $offset, $arrCategoryIds);


        if (null !== $objItems) {
            $this->Template->items = $this->parseItems($objItems);
        }
    }

    /**
     * Count the total matching items
     *
     * @param array   $portfolioArchives
     * @param boolean $blnFeatured
     *
     * @return integer
     */
    protected function countItems($portfolioArchives, $blnFeatured, $arrCategories)
    {
        return PortfolioModel::countPublishedByPids($portfolioArchives, $blnFeatured, $arrCategories);
    }

    /**
     * Fetch the matching items
     *
     * @param array   $portfolioArchives
     * @param boolean $blnFeatured
     * @param integer $limit
     * @param integer $offset
     *
     * @return Collection|NewsModel|null
     */
    protected function fetchItems($portfolioArchives, $blnFeatured, $limit, $offset, $arrCategories)
    {
        $order .= 'tl_portfolio.sorting ASC';

        return PortfolioModel::findPublishedByPids($portfolioArchives, $blnFeatured, $limit, $offset, array('order'=>$order), $arrCategories);
    }
}
