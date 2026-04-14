<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\DependencyInjection;

use EuF\PortfolioBundle\EventListener\DataContainer\MissingLanguageIconListener;
use EuF\PortfolioBundle\EventListener\DataContainer\PortfolioChildTableListener;
use EuF\PortfolioBundle\EventListener\LoadDataContainerListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EuFPortfolioExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('migrations.yml');
        $loader->load('services.yaml');

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['Terminal42ChangeLanguageBundle'])) {
            $container->autowire(MissingLanguageIconListener::class)->setAutoconfigured(true);
            $container->autowire(PortfolioChildTableListener::class)->setAutoconfigured(true);
            $container->autowire(LoadDataContainerListener::class)->setAutoconfigured(true);
        }
    }
}
