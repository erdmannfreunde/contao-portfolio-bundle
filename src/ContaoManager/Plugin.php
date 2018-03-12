<?php 
namespace EuF\PorfolioBundle\ContaoManager; 


use Contao\CoreBundle\ContaoCoreBundle; 
use Contao\ManagerPlugin\Bundle\BundlePluginInterface; 
use Contao\ManagerPlugin\Bundle\Config\BundleConfig; 
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface; 
use Vendor\NameBundle\NameBundle; 

class Plugin implements BundlePluginInterface 
{ 
    public function getBundles(ParserInterface $parser) 
    { 
        return [ 
            BundleConfig::create(PorfolioBundle::class) 
                ->setLoadAfter(ContaoCoreBundle::class) 
                ->setReplace(['portfolio']) 
        ]; 
    } 
}