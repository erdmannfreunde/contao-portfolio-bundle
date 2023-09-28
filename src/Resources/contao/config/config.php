<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

use EuF\PortfolioBundle\Classes\Portfolio;
use EuF\PortfolioBundle\Models\PortfolioModel;
use EuF\PortfolioBundle\Modules\ModulePortfolioList;
use EuF\PortfolioBundle\Models\PortfolioArchiveModel;
use EuF\PortfolioBundle\Models\PortfolioCategoryModel;
use EuF\PortfolioBundle\Modules\ModulePortfolioReader;

$GLOBALS['BE_MOD']['content']['portfolio'] = [
    'tables' => ['tl_portfolio_archive', 'tl_portfolio', 'tl_portfolio_category', 'tl_content'],
];

/*
 * Front end modules
 */
$GLOBALS['FE_MOD']['portfolio'] = [
    'portfoliolist' => ModulePortfolioList::class,
    'portfolioreader' => ModulePortfolioReader::class,
];

$GLOBALS['TL_MODELS']['tl_portfolio'] = PortfolioModel::class;
$GLOBALS['TL_MODELS']['tl_portfolio_archive'] = PortfolioArchiveModel::class ;
$GLOBALS['TL_MODELS']['tl_portfolio_category'] = PortfolioCategoryModel::class;

/*
 * Register hooks
 */
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = [Portfolio::class, 'getSearchablePages'];

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'portfolio';
$GLOBALS['TL_PERMISSIONS'][] = 'portfoliop';
