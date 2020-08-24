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
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['portfoliolist'][0]).' ###';
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
     */
    protected function compile()
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

        // Maximum number of items
        if ($this->numberOfItems > 0) {
            $limit = $this->numberOfItems;
        }

        $arrColumns = ['tl_portfolio.published=?'];
        $arrValues  = ['1'];
        $arrOptions = [
            'order' => 'tl_portfolio.sorting ASC',
        ];

        // Handle featured/unfeatured items
        if ('featured' === $this->portfolio_featured || 'unfeatured' === $this->portfolio_featured) {
            $arrColumns[] = 'tl_portfolio.featured=?';
            $arrValues[]  = 'featured' === $this->portfolio_featured ? '1' : '';
        }

        $objItems = PortfolioModel::findBy($arrColumns, $arrValues, $arrOptions);

        if (null !== $objItems) {
            // Pre-filter items based on filter_categories
            if ($this->filter_categories) {
                $arrCategoryIds = \StringUtil::deserialize($this->filter_categories);
                $arrFilteredItems = [];
                while ($objItems->next()) {
                    if ($objItems->categories) {
                        $arrCategories = \StringUtil::deserialize($objItems->categories);
                        foreach ($arrCategories as $category) {
                            if (in_array($category, $arrCategoryIds, true)) {
                                $arrFilteredItems[] = $objItems->current();
                            }
                        }
                    }
                }
                if ($limit !== 0 && count($arrFilteredItems) > $limit) {
                    $arrFilteredItems = array_slice($arrFilteredItems, 0, $limit);
                }
            } else {
                $arrFilteredItems = $objItems->fetchAll();
            }

            $this->Template->items = $this->parseItems($arrFilteredItems);
        }
    }
}
