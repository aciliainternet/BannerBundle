<?php
namespace Acilia\Bundle\BannerBundle\EventListener;

use Acilia\Bundle\BannerBundle\Service\BannerService;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use AndresMontanez\UserAgentStringBundle\Service\UserAgentService;

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
        $context = null;
        if ($this->userAgentService->getCurrent()->isPhone()) {
            $context = 'mobile';
        } elseif ($this->userAgentService->getCurrent()->isTablet()) {
            $context = 'tablet';
        }

        $this->bannerService->setContext($context);
    }
}
