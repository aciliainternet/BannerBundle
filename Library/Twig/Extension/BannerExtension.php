<?php
namespace Acilia\Bundle\BannerBundle\Library\Twig\Extension;

use Acilia\Bundle\BannerBundle\Library\Twig\Tag\Banner\ConfiguratorTokenParser;
use Acilia\Bundle\BannerBundle\Service\BannerService;

class BannerExtension extends \Twig_Extension
{
    protected $banner;

    public function __construct(BannerService $banner)
    {
        $this->banner = $banner;
    }

    public function getTokenParsers()
    {
        return array(
            new ConfiguratorTokenParser(),
        );
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('has_banner', [$this, 'has'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_banner', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    public function has($bannerType, $place = null, $referenceId = null)
    {
        $bannerTag = $this->getBanner()->getCode($bannerType, $place, $referenceId);
        if ($bannerTag instanceof BannerTag) {
            return (! $bannerTag->isEmpty());
        }

        return false;
    }
    
    public function render($bannerType, $place = null, $referenceId = null)
    {
        return $this->getBanner()->getCode($bannerType, $place, $referenceId);
    }

    public function getBanner()
    {
        return $this->banner;
    }

    public function getName()
    {
        return 'acilia.twig.banner_extension';
    }

    public function configure($configuration)
    {
        $this->banner->configure($configuration);
    }
}
