<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\EventListener\Navigation;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageModel;
use EuF\PortfolioBundle\Models\PortfolioArchiveModel;
use EuF\PortfolioBundle\Models\PortfolioModel;
use Terminal42\ChangeLanguage\EventListener\Navigation\AbstractNavigationListener;

/**
 * @Hook("changelanguageNavigation")
 */
class PortfolioNavigationListener extends AbstractNavigationListener
{
    protected function getUrlKey(): string
    {
        return 'items';
    }

    protected function findCurrent(): ?PortfolioModel
    {
        $alias = $this->getAutoItem();

        if ('' === $alias) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (null === ($archives = PortfolioArchiveModel::findBy('jumpTo', $objPage->id))) {
            return null;
        }

        // Fix Contao bug that returns a collection (see contao-changelanguage#71)
        $options = ['limit' => 1, 'return' => 'Model'];

        return PortfolioModel::findPublishedByParentAndIdOrAlias($alias, $archives->fetchEach('id'), $options);
    }

    protected function findPublishedBy(array $columns, array $values = [], array $options = []): ?PortfolioModel
    {
        return PortfolioModel::findOneBy(
            $this->addPublishedConditions($columns, PortfolioModel::getTable()),
            $values,
            $options
        );
    }
}
