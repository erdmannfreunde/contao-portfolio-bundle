<?php

namespace EuF\PortfolioBundle\Modules;

use EuF\PortfolioBundle\Modules\ModulePortfolio;
use EuF\PortfolioBundle\Models\PortfolioModel;

/**
 * Class ModulePortfolioReader
 *
 * Front end module "portfolio reader".
 */
class ModulePortfolioReader extends ModulePortfolio
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_portfolioreader';


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['portfolioreader'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && \Config::get('useAutoItem') && isset($_GET['auto_item']))
        {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        // Do not index or cache the page if no portfolio item has been specified
        if (!\Input::get('items'))
        {
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        global $objPage;

        $this->Template->items = '';
        $this->Template->referer = 'javascript:history.go(-1)';
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        // Get the portfolio item
        $objItem = PortfolioModel::findByIdOrAlias(\Input::get('items'));

        if ($objItem === null)
        {
            // Do not index or cache the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            // Send a 404 header
            header('HTTP/1.1 404 Not Found');
            $this->Template->items = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], \Input::get('items')) . '</p>';
            return;
        }

        $arrItem = $this->parseItem($objItem);
        $this->Template->items = $arrItem;

        // Overwrite the page title (see #2853 and #4955)
        if ($objItem->headline != '')
        {
            $objPage->pageTitle = strip_tags(strip_insert_tags($objItem->headline));
        }
    }
}
