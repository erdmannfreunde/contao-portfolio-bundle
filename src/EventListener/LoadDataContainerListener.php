<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use EuF\PortfolioBundle\EventListener\DataContainer\MissingLanguageIconListener;
use EuF\PortfolioBundle\EventListener\DataContainer\PortfolioChildTableListener;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly MissingLanguageIconListener $missingLanguageIconListener,
        private readonly PortfolioChildTableListener $portfolioChildTableListener,
        private readonly ?ParentTableListener $parentTableListener = null,
        private readonly ?ParentChildViewListener $parentChildViewListener = null,
    ) {
    }

    public function __invoke(string $table): void
    {
        $bundles = $this->params->get('kernel.bundles');

        if (isset($bundles['Terminal42ChangeLanguageBundle'])) {
            switch ($table) {
                case 'tl_portfolio_archive':
                    $this->parentTableListener?->register($table);
                    break;

                case 'tl_portfolio':
                    $this->missingLanguageIconListener->register($table);
                    $this->portfolioChildTableListener->register($table);
                    $this->parentChildViewListener?->register($table);
                    break;
            }
        }
    }
}
