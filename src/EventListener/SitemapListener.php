<?php

namespace EuF\PortfolioBundle\EventListener;

use Contao\ArticleModel;
use Contao\CoreBundle\Event\SitemapEvent;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Database;
use Contao\PageModel;
use EuF\PortfolioBundle\Models\PortfolioArchiveModel;
use EuF\PortfolioBundle\Models\PortfolioModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapListener
{
    public function __construct(
        protected ContentUrlGenerator $urlGenerator,
    ) {
    }

    #[AsEventListener(event: 'contao.sitemap')]
    public function onSitemap(SitemapEvent $event): void
    {
        $rootIds = $event->getRootPageIds();
        $database = Database::getInstance();

        foreach ($rootIds as $id) {
            $arrRoot = $database->getChildRecords($id, 'tl_page');
            $arrProcessed = [];
            $time = time();

            // Get all news archives
            $objArchive = PortfolioArchiveModel::findByProtected('');

            // Walk through each archive
            if (null !== $objArchive) {
                while ($objArchive->next()) {
                    // Skip news archives without target page
                    if (!$objArchive->jumpTo) {
                        continue;
                    }

                    // Skip news archives outside the root nodes
                    if (!empty($arrRoot) && !\in_array($objArchive->jumpTo, $arrRoot, true)) {
                        continue;
                    }

                    // Get the URL of the jumpTo page
                    if (!isset($arrProcessed[$objArchive->jumpTo])) {
                        $objParent = PageModel::findWithDetails($objArchive->jumpTo);

                        // The target page does not exist
                        if (null === $objParent) {
                            continue;
                        }

                        // The target page has not been published (see #5520)
                        if (!$objParent->published || ($objParent->start && $objParent->start > $time) || ($objParent->stop && $objParent->stop <= $time)) {
                            continue;
                        }

                        // The target page is protected (see #8416)
                        if ($objParent->protected) {
                            continue;
                        }

                        // The target page is exempt from the sitemap (see #6418)
                        if ('noindex,nofollow' === $objParent->robots) {
                            continue;
                        }

                        // Generate the URL
                        $arrProcessed[$objArchive->jumpTo] = $objParent;
                    }

                    $strUrl = $arrProcessed[$objArchive->jumpTo];

                    // Get the items
                    $objArticle = PortfolioModel::findPublishedDefaultByPid($objArchive->id);

                    if (null !== $objArticle) {
                        while ($objArticle->next()) {
                            if ('noindex,nofollow' === $objArticle->robots) {
                                continue;
                            }

                            $event->addUrlToDefaultUrlSet($this->getLink($objArticle->current(), $strUrl));
                        }
                    }
                }
            }
        }
    }

    /**
     * Return the link of a portfolio article.
     *
     * @param string $strBase
     *
     * @throws \Exception
     */
    protected function getLink(PortfolioModel $objItem, PageModel $page, $strBase = ''): string
    {
        switch ($objItem->source) {
            // Link to an external page
            case 'external':
                return $objItem->url;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objItem->getRelated('jumpTo')) instanceof PageModel) {
                    /** @var PageModel $objTarget */
                    return $this->urlGenerator->generate($objTarget, referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = ArticleModel::findByPk($objItem->articleId)) instanceof ArticleModel && ($objPid = $objArticle->getRelated('pid')) instanceof PageModel) {
                    /** @var PageModel $objPid */
                    return $this->urlGenerator->generate($objPid, ['parameters' => '/articles/'.($objArticle->alias ?: $objArticle->id)], UrlGeneratorInterface::ABSOLUTE_URL);
                }
                break;
        }

        return $this->urlGenerator->generate($page, ['parameters' => '/'.($objItem->alias ?: $objItem->id)], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}