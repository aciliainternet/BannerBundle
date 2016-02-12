<?php
namespace Acilia\Bundle\BannerBundle\Service;

use Acilia\Bundle\BannerBundle\Event\ResourceBannerEvent;
use Acilia\Bundle\BannerBundle\Library\BannerTag;
use Acilia\Component\Memcached\Service\MemcachedService;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BannerService implements EventSubscriberInterface
{
    /**
     * Event Dispatcher
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Memcache Service
     * @var \Acilia\Component\Memcached\Service\MemcachedService
     */
    protected $memcache;

    /**
     * Request Stack
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * Doctrine
     */
    protected $doctrine;

    protected $types;

    protected $fallbacks;

    // Options
    protected $place;
    protected $referenceId;

    // Context
    protected $context;

    public function __construct(EventDispatcherInterface $dispatcher, RequestStack $requestStack, Doctrine $doctrine, MemcachedService $memcache, array $fallbacks)
    {
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->memcache = $memcache;
        $this->fallbacks = $fallbacks;

        $this->place = null;
        $this->referenceId = null;
        $this->context = null;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getCode($bannerType, $place = null, $referenceId = null)
    {
        $bannerTag = '';

        if ($place == null) {
            $place = $this->place;
        }

        // If Place is not defined, Ad can't be shown
        if (null === $place) {
            return $bannerTag;
        }

        if ($referenceId === false) {
            $referenceId = null;
        } elseif ($referenceId == null) {
            $referenceId = $this->referenceId;
        }

        $context = $this->getContext();

        // Get URL
        $currentUrl = $this->requestStack->getMasterRequest()->getPathInfo();

        // Get resource and context
        $event = new ResourceBannerEvent();

        $this->dispatcher->dispatch(ResourceBannerEvent::NAME, $event);
        if ($event->isAvailable()) {
            $resource = $event->getResource();

            if ($event->getContext() != null) {
                $context = $event->getContext();
            }

            $key = 'Banner:' . $resource . ':' . $context . ':' . $place . ':' . $bannerType . ':' . $this->getReferenceId($place, $referenceId) . ':' . sha1($currentUrl);
            $bannerTag = $this->memcache->get($key);
            if ($this->memcache->notFound()) {

                // Create Banner Tag
                $bannerTag = new BannerTag();
                $bannerTag->setResource($resource)
                    ->setBannerType($bannerType)
                    ->setPlace($place)
                    ->setContext($context)
                    ->setReferenceId($this->getReferenceId($place, $referenceId));

                if ($this->isPageAvailable($bannerTag, $currentUrl)) {
                    return '<!-- BANNER BEGIN - This page has it\'s Ads Disabled - BANNER END -->';
                }

                // Fill Banner Tag
                $this->fillBannerTag($bannerTag, $currentUrl);

                // If Banner is Empty, process with the places fallback
                if ($bannerTag->isEmpty()) {
                    $bannerTag->setReferenceId(null);
                    $this->processFallbacks($bannerTag, $currentUrl, $referenceId);
                }

                // Save on Memcache
                $this->memcache->set($key, $bannerTag, 60);
            }
        }

        return $bannerTag;
    }

    public function getType($slug)
    {
        $key = 'Banner:Types';

        if (!is_array($this->types)) {
            $types = $this->memcache->get($key);
            if ($this->memcache->notFound()) {
                $bannerTypes = $this->doctrine->getManager()->getRepository('AciliaBannerBundle:BannerType')->findAll();
                $types = array();

                foreach ($bannerTypes as $bannerType) {
                    $types[$bannerType->getSlug()] = $bannerType->getId();
                }

                $this->memcache->set($key, $types, 1440);
            }

            $this->types = $types;
        }

        if (isset($this->types[$slug])) {
            return $this->types[$slug];
        } elseif (isset($this->types['none'])) {
            return $this->types['none'];
        } else {
            return 0;
        }
    }

    public function configure(array $options)
    {
        if (isset($options['place'])) {
            $this->place = $options['place'];
        }

        if (isset($options['referenceId'])) {
            $this->referenceId = $options['referenceId'];
        }
    }

    protected function processFallbacks(BannerTag $bannerTag, $currentUrl, $referenceId)
    {
        if (isset($this->fallbacks[$bannerTag->getPlace()])) {
            $bannerTag->setPlace($this->fallbacks[$bannerTag->getPlace()]);
            $bannerTag->setReferenceId($this->getReferenceId($bannerTag->getPlace(), $referenceId));
            $this->fillBannerTag($bannerTag, $currentUrl);

            // If still Empty, fallback to fallback
            if ($bannerTag->isEmpty()) {
                $this->processFallbacks($bannerTag, $currentUrl, $referenceId);
            }
        }
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

    protected function fillBannerTag(BannerTag $bannerTag, $currentUrl)
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

        $query = $this->doctrine->getManager()->createQuery($dql)
            ->setParameter('resourceId', $bannerTag->getResource())
            ->setParameter('typeId', $this->getType($bannerTag->getBannerType()))
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


    protected function isPageAvailable(BannerTag $bannerTag, $currentUrl)
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

        $query = $this->doctrine->getManager()->createQuery($dql)
            ->setParameter('resourceId', $bannerTag->getResource())
            ->setParameter('typeId', $this->getType('none'))
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

    protected function getReferenceId($place, $referenceId)
    {
        if (is_array ($referenceId)) {

            return (isset ($referenceId[$place])) ? $referenceId[$place] : null;
        } elseif ($referenceId === false) {
            return null;
        } else {
            return $referenceId;
        }
    }

    /**
     * Get the Subscribed Events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -128),
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}
