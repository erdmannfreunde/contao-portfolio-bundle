<?php

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], 2, array
(
    'portfolio' => array
    (
        'tables'      => array('tl_portfolio', 'tl_portfolio_category', 'tl_content'),
        'icon'        => 'system/modules/portfolio/assets/icon.png',
    )
));

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 3, array
(
    'portfolio' => array
    (
        'portfoliolist'     => 'EuF\PortfolioBundle\Modules\ModulePortfolioList',
        'portfolioreader'   => 'EuF\PortfolioBundle\Modules\ModulePortfolioReader',
    )
));
