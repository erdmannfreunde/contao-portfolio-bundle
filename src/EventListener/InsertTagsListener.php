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

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use EuF\PortfolioBundle\Classes\Portfolio;
use EuF\PortfolioBundle\Models\PortfolioModel;

/**
 * @Hook("replaceInsertTags")
 */
class InsertTagsListener
{
    private const SUPPORTED_TAGS = ['portfolio_url'];
    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    public function __invoke(string $insertTag, bool $useCache, string $cachedValue, array $flags, array $tags, array $cache, int $_rit, int $_cnt)
    {
        $elements = explode('::', $insertTag);
        $key = strtolower($elements[0]);

        if (\in_array($key, self::SUPPORTED_TAGS, true)) {
            return $this->replaceInsertTags($key, $elements[1], $flags);
        }

        return false;
    }

    private function replaceInsertTags(string $insertTag, string $idOrAlias, array $flags): string
    {
        $this->framework->initialize();

        /** @var PortfolioModel $adapter */
        $adapter = $this->framework->getAdapter(PortfolioModel::class);
        $portfolio = $adapter->findByIdOrAlias($idOrAlias);

        if (null === $portfolio) {
            return '';
        }

        if ('portfolio_url' === $insertTag) {
            /** @var Portfolio $adapter */
            $adapter = $this->framework->getAdapter(Portfolio::class);

            return $adapter->generatePortfolioUrl($portfolio, false, \in_array('absolute', $flags, true));
        }

        return '';
    }
}
