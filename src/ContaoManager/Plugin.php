<?php 
namespace EuF\PortfolioBundle\ContaoManager; 


use Contao\ManagerPlugin\Bundle\BundlePluginInterface; 
use Contao\ManagerPlugin\Bundle\Config\BundleConfig; 
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface; 

use Contao\CoreBundle\ContaoCoreBundle;
use EuF\PortfolioBundle\EuFPortfolioBundle;

class Plugin implements BundlePluginInterface 
{ 
    public function getBundles(ParserInterface $parser) 
    { 
        return [ 
            BundleConfig::create(EuFPortfolioBundle::class) 
                ->setLoadAfter([ContaoCoreBundle::class]) 
                ->setReplace(['portfolio']) 
        ]; 
    } 
}