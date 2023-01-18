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

use EuF\PortfolioBundle\Models\PortfolioArchiveModel;
use EuF\PortfolioBundle\Models\PortfolioModel;
use Terminal42\ChangeLanguage\Helper\LabelCallback;

class MissingLanguageIconListener
{
    private static array $callbacks = [
        'tl_portfolio' => 'onPorfolioChildRecords',
    ];

    /**
     * Override core labels to show missing language information.
     */
    public function register(string $table): void
    {
        if (\array_key_exists($table, self::$callbacks)) {
            LabelCallback::createAndRegister(
                $table,
                fn (array $args, $previousResult) => $this->{self::$callbacks[$table]}($args, $previousResult)
            );
        }
    }

    /**
     * Generate missing translation warning for child records.
     */
    public function onPorfolioChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        $archive = PortfolioArchiveModel::findByPk($row['pid']);

        if (
            null !== $archive
            && $archive->master
            && (!$row['languageMain'] || null === PortfolioModel::findByPk($row['languageMain']))
        ) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    private function generateLabelWithWarning(string $label, string $imgStyle = ''): string
    {
        return $label.sprintf(
            '<span style="padding-left:3px"><img src="%s" alt="%s" title="%s" style="%s"></span>',
            'bundles/terminal42changelanguage/language-warning.png',
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $imgStyle
        );
    }
}
