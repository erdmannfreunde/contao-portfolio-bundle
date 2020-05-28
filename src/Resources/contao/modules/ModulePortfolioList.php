<?php

namespace EuF\PortfolioBundle\Modules;

use EuF\PortfolioBundle\Modules\ModulePortfolio;
use EuF\PortfolioBundle\Models\PortfolioModel;
use EuF\PortfolioBundle\Models\PortfolioCategoryModel;

/**
 * Class ModulePortfolioList
 *
 * Front end module "portfolio list".
 */
class ModulePortfolioList extends ModulePortfolio
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_portfoliolist';

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['portfoliolist'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $objCategories = PortfolioCategoryModel::findAll([
            'column' => 'published',
            'value'  => 1,
            'order'  => 'sorting ASC',
        ]);

        if ($objCategories !== null && $this->portfolio_filter)
        {
            $this->Template->categories = $objCategories;
        }

        // Maximum number of items
		if ($this->numberOfItems > 0)
		{
			$limit = $this->numberOfItems;
		}


        $arrColumns = ['tl_portfolio.published=?'];
        $arrValues = ['1'];
        $arrOptions = [
            'order' => 'tl_portfolio.sorting ASC',
            'limit' => $limit
        ];

        // Handle featured/unfeatured items
        if ($this->portfolio_featured === 'featured' || $this->portfolio_featured === 'unfeatured')
        {
            $arrColumns[] = 'tl_portfolio.featured=?';
            $arrValues[] = $this->portfolio_featured === 'featured' ? '1' : '';
        }

        $objItems = PortfolioModel::findBy($arrColumns, $arrValues, $arrOptions);

        if ($objItems !== null)
        {
            $this->Template->items = $this->parseItems($objItems);
        }
    }
}
