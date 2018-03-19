<?php 
namespace EuF\PorfolioBundle\ContaoManager; 


use Contao\CoreBundle\ContaoCoreBundle; 
use Contao\ManagerPlugin\Bundle\BundlePluginInterface; 
use Contao\ManagerPlugin\Bundle\Config\BundleConfig; 
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface; 

use Contao\CoreBundle\ContaoCoreBundle;
use EuF\PorfolioBundle\EuFPorfolioBundle;

class Plugin implements BundlePluginInterface 
{ 
    public function getBundles(ParserInterface $parser) 
    { 
        return [ 
            BundleConfig::create(EuFPorfolioBundle::class) 
                ->setLoadAfter(ContaoCoreBundle::class) 
                ->setReplace(['portfolio']) 
        ]; 
    } 
}