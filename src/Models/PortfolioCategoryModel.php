<?php

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace EuF\PorfolioBundle\Models;

/**
 * Reads and writes portfolio items.
 */
class PortfolioCategoryModel extends \Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_portfolio_category';
}
