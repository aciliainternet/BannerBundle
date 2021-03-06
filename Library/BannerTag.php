<?php

/*
 * This file is part of the Acilia Component / Banner Bundle.
 *
 * (c) Acilia Internet S.L. <info@acilia.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acilia\Bundle\BannerBundle\Library;

class BannerTag
{
    protected $cacheKey;
    protected $id;
    protected $name;
    protected $resource;
    protected $context;
    protected $bannerType;
    protected $referenceId;
    protected $place;
    protected $tag = '';
    protected $debug = [];

    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

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

    public function setBannerType($bannerType)
    {
        $this->bannerType = $bannerType;
        return $this;
    }

    public function getBannerType()
    {
        return $this->bannerType;
    }

    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
        return $this;
    }

    public function getReferenceId()
    {
        return $this->referenceId;
    }

    public function setPlace($place)
    {
        $this->place = $place;
        return $this;
    }

    public function getPlace()
    {
        return $this->place;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    public function addDebug($debug)
    {
        $this->debug[] = $debug;
        return $this;
    }

    public function clear()
    {
        $this->tag = '';
        return $this;
    }

    public function isEmpty()
    {
        return ($this->tag == '');
    }

    public function __toString()
    {
        $debug = '';
        foreach ($this->debug as $idx => $line) {
            $debug .= "* {$idx} - {$line}\n";
        }

        $tag = '<!-- BANNER BEGIN ** ID: ' . $this->id . ' - ' . str_replace('-', '_', $this->name) . ' ** ' . PHP_EOL . $debug  . ' -->' . PHP_EOL
            . '<!-- CacheKey: ' . $this->cacheKey . ' -->' . PHP_EOL
            . $this->tag . PHP_EOL
            . '<!-- BANNER END -->';
        return $tag;
    }
}
