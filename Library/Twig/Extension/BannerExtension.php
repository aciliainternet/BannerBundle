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
            'render_banner' => new \Twig_Function_Method($this, 'render', ['is_safe' => array('html')])
        ];
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
