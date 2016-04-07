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
    /** @var EventDispatcherInterface $dispatcher */
    protected $dispatcher;

    /** @var MemcachedService $memcache */
    protected $memcache;

    /** @var RequestStack $requestStack */
    protected $requestStack;

    /** @var Doctrine $doctrine */
    protected $doctrine;

    protected $types;
    protected $fallbacks;

    // Options
    protected $place;
    protected $referenceId;

    // Context
    protected $context;
    protected $instances;

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
        $this->instances = [];
    }

    public function configure(array $options)
    {
        if (isset($options['place'])) {
            $this->place = $options['place'];
        }

        if (isset($options['referenceId'])) {
            $this->referenceId = $options['referenceId'];
        }

        if (isset($options['fallbacks'])) {
            $this->fallbacks = $options['fallbacks'];
        }
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
        $bannerRepository = $this->doctrine->getRepository('AciliaBannerBundle:Banner');

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

            // Overwrite ad context if required
            if ($event->getContext() != null) {
                $context = $event->getContext();
            }

            // Banner identifier key
            $key = 'Banner:' . $resource . ':' . $context . ':' . $place . ':' . $bannerType . ':' . $referenceId . ':' . sha1($currentUrl);

            if (isset($this->instances[$key])) {
                return $this->instances[$key];
            }

            $bannerTag = $this->memcache->get($key);
            if ($this->memcache->notFound()) {

                // Create Banner Tag
                $bannerTag = new BannerTag();
                $bannerTag->setResource($resource)
                    ->setBannerType($bannerType)
                    ->setPlace($place)
                    ->setContext($context)
                    ->setReferenceId($referenceId)
                    ->setCacheKey($key);

                if ($bannerRepository->isPageAvailable($bannerTag, $currentUrl, $this->getType('none'))) {
                    return '<!-- BANNER BEGIN - This page has it\'s Ads Disabled - BANNER END -->';
                }

                // Fill Banner Tag
                $bannerRepository->fillBannerTag($bannerTag, $currentUrl, $this->getType($bannerTag->getBannerType()));

                $fallbacks = $this->fallbacks;
                while ($bannerTag->isEmpty() && count($fallbacks) > 0) {

                    $fallback = array_slice($fallbacks, 0, 1);
                    array_shift($fallbacks);

                    $place = key($fallback);
                    $referenceId = $fallback[$place];
                    $bannerTag->setPlace($place)->setReferenceId($referenceId);

                    // Fill Banner Tag
                    $bannerRepository->fillBannerTag($bannerTag, $currentUrl, $this->getType($bannerTag->getBannerType()));
                }

                // Save on Memcache and internally
                $this->instances[$key] = $bannerTag;
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
