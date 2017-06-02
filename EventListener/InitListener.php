<?php
namespace Acilia\Bundle\BannerBundle\EventListener;

use Acilia\Bundle\BannerBundle\Service\BannerService;
use Acilia\Bundle\BannerBundle\Service\UserAgentService;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class InitListener
{
    protected $bannerService;
    protected $userAgentService;

    public function __construct(BannerService $bannerService, UserAgentService $userAgentService)
    {
        $this->bannerService = $bannerService;
        $this->userAgentService = $userAgentService;
    }

    public function onRequest(GetResponseEvent $event)
    {
        $this->bannerService->setContext($this->userAgentService->getDeviceType());
    }
}
