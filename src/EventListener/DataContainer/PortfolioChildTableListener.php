<?php

declare(strict_types=1);

/*
 * Contao Portfolio Bundle for Contao Open Source CMS.
 * @copyright  Copyright (c) Erdmann & Freunde
 * @author     Erdmann & Freunde <https://erdmann-freunde.de>
 * @license    MIT
 * @link       http://github.com/erdmannfreunde/contao-portfolio-bundle
 */

namespace EuF\PortfolioBundle\EventListener\DataContainer;

use Contao\Model;
use Contao\Model\Collection;
use EuF\PortfolioBundle\Models\PortfolioModel;
use Terminal42\ChangeLanguage\EventListener\DataContainer\AbstractChildTableListener;

class PortfolioChildTableListener extends AbstractChildTableListener
{
    protected function getTitleField(): string
    {
        return 'headline';
    }

    protected function getSorting(): string
    {
        return 'sorting';
    }

    /**
     * @param PortfolioModel             $current
     * @param Collection<PortfolioModel> $models
     */
    protected function formatOptions(Model $current, Collection $models): array
    {
        $options = [];

        foreach ($models as $model) {
            $options[$model->id] = sprintf('%s [ID %s]', $model->headline, $model->id);
        }

        return $options;
    }
}
