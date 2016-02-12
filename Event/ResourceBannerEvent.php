<?php
namespace Acilia\Bundle\BannerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event sent when the resource for the banner is required
 *
 * @author Acilia Internet <info@acilia.es>
 */
class ResourceBannerEvent extends Event
{
    /**
     * Name of the event
     * @var string
     */
    const NAME = 'banner.resource';

    /**
     * Resource
     * @var
     */
    protected $resource;

    /**
     * Context
     * @var
     */
    protected $context;


    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function isAvailable()
    {
        if ($this->resource) {
            return true;
        }

        return false;
    }
}
