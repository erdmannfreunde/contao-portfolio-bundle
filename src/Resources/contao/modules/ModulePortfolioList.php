<?php

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace EuF\PortfolioBundle\Modules;

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
        $objItems = \PortfolioModel::findAll([
            'column' => 'published',
            'value' => 1,
            'order' => 'sorting ASC',
        ]);

        if ($objItems !== null)
        {
            $this->Template->items = $this->parseItems($objItems);
        }
    }
}
