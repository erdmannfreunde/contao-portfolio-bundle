<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\Modules;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Environment;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use EuF\PortfolioBundle\Models\PortfolioModel;

/**
 * Class ModulePortfolioReader.
 *
 * Front end module "portfolio reader".
 */
class ModulePortfolioReader extends ModulePortfolio
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_portfolioreader';

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.mb_strtoupper($GLOBALS['TL_LANG']['FMD']['portfolioreader'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = StringUtil::specialcharsUrl(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'themes', 'table' => 'tl_module', 'act' => 'edit', 'id' => $this->id]));

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && isset($_GET['auto_item']) && Config::get('useAutoItem')) {
            Input::setGet('items', Input::get('auto_item'));
        }

        // Do not index or cache the page if no portfolio item has been specified
        if (!Input::get('items')) {
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        $this->portfolio_archives = $this->sortOutProtected(StringUtil::deserialize($this->portfolio_archives));

        if (empty($this->portfolio_archives) || !\is_array($this->portfolio_archives)) {
            throw new InternalServerErrorException('The news reader ID '.$this->id.' has no archives specified.', $this->id);
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile(): void
    {
        global $objPage;

        $this->Template->items = '';
        $this->Template->referer = 'javascript:history.go(-1)';
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        // Get the portfolio item
        $objItem = PortfolioModel::findPublishedByParentAndIdOrAlias(Input::get('items'), $this->portfolio_archives);

        if (null === $objItem) {
            throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
        }

        $arrItem = $this->parseItem($objItem);
        $this->Template->items = $arrItem;

        // Overwrite the page title (see #2853 and #4955 and #87)
        if ($objItem->pageTitle) {
            $objPage->pageTitle = $objItem->pageTitle;
        } elseif ($objItem->headline) {
            $objPage->pageTitle = strip_tags(StringUtil::stripInsertTags($objItem->headline));
        }

        // Overwrite the page description
        if ($objItem->description) {
            $objPage->description = $objItem->description;
        } elseif ($objItem->teaser) {
            $objPage->description = $this->prepareMetaDescription($objItem->teaser);
        }

        if ($objItem->robots) {
            $objPage->robots = $objItem->robots;
        }
    }
}
