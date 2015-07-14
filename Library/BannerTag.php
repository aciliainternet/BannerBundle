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
    const TYPE_COMMON = 'common';

    const PLACE_ROS = 'ros';
    const PLACE_COMMON = 'common';
    const PLACE_SHOW = 'show';
    const PLACE_MOVIE = 'movie';

    protected $id;
    protected $name;
    protected $resourceCC;
    protected $resourceId;
    protected $bannerType;
    protected $referenceId;
    protected $place;
    protected $tag = '';
    protected $debug = [];

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

    public function setResourceCC($resourceCC)
    {
        $this->resourceCC = $resourceCC;

        return $this;
    }

    public function getResourceCC()
    {
        return $this->resourceCC;
    }

    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    public function getResourceId()
    {
        return $this->resourceId;
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
            . $this->tag . PHP_EOL
            . '<!-- BANNER END -->';
        return $tag;
    }
}
