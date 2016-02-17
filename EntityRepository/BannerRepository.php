<?php
namespace Acilia\Bundle\BannerBundle\EntityRepository;

use Acilia\Bundle\BannerBundle\Library\BannerTag;
use Doctrine\ORM\EntityRepository;

class BannerRepository extends EntityRepository
{
    public function fillBannerTag(BannerTag $bannerTag, $currentUrl, $type)
    {
        // Fetch Banners
        $dql = 'SELECT b '
            . 'FROM AciliaBannerBundle:Banner b '
            . 'WHERE b.status = true '
            . '  AND b.resourceId = :resourceId '
            . '  AND b.type = :typeId '
            . '  AND b.place = :place '
            . '  AND (b.publishSince <= :publishSince OR b.publishSince IS NULL OR b.publishSince = \'0000-00-00\') '
            . '  AND (b.publishUntil >= :publishUntil OR b.publishUntil IS NULL OR b.publishUntil = \'0000-00-00\') '
            . ($bannerTag->getReferenceId() !== null ? '  AND b.referenceId = :referenceId ' : '  AND b.referenceId IS NULL ')
            . '  AND (b.context IS NULL OR b.context = :context) '
            . 'ORDER BY b.context DESC , b.modifiedAt DESC';

        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('resourceId', $bannerTag->getResource())
            ->setParameter('typeId', $type)
            ->setParameter('place', $bannerTag->getPlace())
            ->setParameter('publishSince', date('Y-m-d'))
            ->setParameter('publishUntil', date('Y-m-d'))
            ->setParameter('context', $bannerTag->getContext());

        if ($bannerTag->getReferenceId() !== null) {
            $query->setParameter('referenceId', $bannerTag->getReferenceId());
        }

        // Iterate Banners
        $banners = $query->getResult();
        foreach ($banners as $banner) {
            if (trim($banner->getUrlInclude()) == '' && trim($banner->getUrlExclude()) == '' && $bannerTag->isEmpty()) {
                $bannerTag->setTag($banner->getTag())
                    ->setId($banner->getId())
                    ->setName($banner->getName());
            } else {
                // Check if URL is in the Includes
                if ($this->compareUrl($currentUrl, $banner->getUrlInclude())) {
                    $bannerTag->setTag($banner->getTag())
                        ->setId($banner->getId())
                        ->setName($banner->getName());
                    break;
                }

                // Check if URL is in the Excludes
                if ($this->compareUrl($currentUrl, $banner->getUrlExclude())) {
                    $bannerTag->clear();
                }
            }
        }

        // For Debugging
        $bannerTag->addDebug(sprintf("Resource Id: %s | Context: %s | Place: %s " . (($bannerTag->getReferenceId() !== null) ? "| ReferenceId: {$bannerTag->getReferenceId()} " : ''). "| Type: %s",
                $bannerTag->getResource(),
                ($bannerTag->getContext() == null ? 'all' : $bannerTag->getContext()),
                $bannerTag->getPlace(),
                $bannerTag->getBannerType())
        );
    }


    public function isPageAvailable(BannerTag $bannerTag, $currentUrl, $type)
    {
        // Fetch Disabling Banners
        $dql = 'SELECT b '
            . 'FROM AciliaBannerBundle:Banner b '
            . 'WHERE b.status = true '
            . '  AND b.resourceId = :resourceId '
            . '  AND b.type = :typeId '
            . '  AND b.publishSince <= :publishSince '
            . '  AND (b.publishUntil >= :publishUntil OR b.publishUntil IS NULL OR b.publishUntil = \'0000-00-00\') '
            . 'ORDER BY b.modifiedAt DESC ';

        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('resourceId', $bannerTag->getResource())
            ->setParameter('typeId', $type)
            ->setParameter('publishSince', date('Y-m-d'))
            ->setParameter('publishUntil', date('Y-m-d'));

        // Iterate Banners
        $banners = $query->getResult();
        foreach ($banners as $banner) {
            if ($this->compareUrl($currentUrl, $banner->getUrlInclude())) {
                return true;
            }
        }

        return false;
    }

    protected function compareUrl($currentUrl, $pattern)
    {
        $check = false;

        if ($pattern != '') {
            $_includes = explode(PHP_EOL, $pattern);
            foreach ($_includes as $_include) {
                $_include = '@^' . trim(str_replace('*', '.*', $_include)) . '$@i';
                if (preg_match($_include, $currentUrl)) {
                    $check = true;
                }
            }
        }

        return $check;
    }
}
