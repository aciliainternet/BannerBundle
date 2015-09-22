<?php
namespace Acilia\Bundle\BannerBundle\Library\Twig;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Loader extends FilesystemLoader
{
    protected $kernel;
    protected $cache;

    public function __construct(KernelInterface $kernel, FileLocatorInterface $locator, TemplateNameParserInterface $parser)
    {
        $this->kernel = $kernel;
        $this->cache = [];

        parent::__construct($locator, $parser);
    }

    protected function findTemplate($template, $regionCC =  null)
    {
        $logicalName = (string) $template;
        $previous = null;

        if (strpos($template, 'BannerBundle') === 0) {
            $template = $this->parser->parse($template);

            if ($template instanceof TemplateReferenceInterface) {
                $cc = ($regionCC != null) ? $regionCC : '_region';
                $key = $cc.'::'.$template->getLogicalName();
                $templatePath = $template->getPath();

                if (isset($this->cache[$key])) {
                    return $this->cache[$key];
                }

                try {
                    $templatePathBase = str_replace('BannerBundle/Resources/views/', 'BannerBundle/Resources/views/'.$cc.'/', $templatePath);

                    return $this->cache[$key] = $this->locator->locate($templatePathBase);
                } catch (\InvalidArgumentException $e) {
                    $templatePathBase = str_replace('BannerBundle/Resources/views/', 'BannerBundle/Resources/views/_region/', $templatePath);

                    return $this->cache[$key] = $this->locator->locate($templatePathBase);
                }
            }
        }

        throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName), -1, null, $previous);
    }
}
