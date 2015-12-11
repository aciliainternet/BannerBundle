<?php

/*
 * This file is part of the Acilia Component / Banner Bundle.
 *
 * (c) Acilia Internet S.L. <info@acilia.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acilia\Bundle\BannerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Banner
 *
 * Entity for storing the banner tag.
 *
 * @author Alejandro Glejberman <alejandro@acilia.es>
 *
 * @ORM\Entity()
 * @ORM\Table(name="banner", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 */
class Banner
{
    /**
     * @var integer
     *
     * @ORM\Column(name="banner_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="banner_place", type="string", nullable=false)
     */
    private $place;

    /**
     * @var integer
     *
     * @ORM\Column(name="banner_reference_id", type="integer", nullable=true)
     */
    private $referenceId;

    /**
     * @var string
     *
     * @ORM\Column(name="banner_name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="banner_tag", type="text", nullable=false)
     */
    private $tag;

    /**
     * @var boolean
     *
     * @ORM\Column(name="banner_status", type="boolean", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="banner_publish_since", type="date", nullable=true)
     */
    private $publishSince;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="banner_publish_until", type="date", nullable=true)
     */
    private $publishUntil;

    /**
     * @var string
     *
     * @ORM\Column(name="banner_url_exclude", type="text", nullable=true)
     */
    private $urlExclude;

    /**
     * @var string
     *
     * @ORM\Column(name="banner_url_include", type="text", nullable=true)
     */
    private $urlInclude;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="banner_modified_at", type="datetime", nullable=false)
     */
    private $modifiedAt;

    /**
     * @var BannerType
     *
     * @ORM\ManyToOne(targetEntity="BannerType")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="banner_type", referencedColumnName="banner_type_id")
     * })
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="banner_resource_id", type="integer")
     */
    private $resourceId;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set place
     *
     * @param  string $place
     * @return Banner
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set referenceId
     *
     * @param  integer $referenceId
     * @return Banner
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * Get referenceId
     *
     * @return integer
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Banner
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set tag
     *
     * @param  string $tag
     * @return Banner
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set status
     *
     * @param  boolean $status
     * @return Banner
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set publishSince
     *
     * @param  \DateTime $startAt
     * @return Banner
     */
    public function setPublishSince($publishSince)
    {
        $this->publishSince = $publishSince;

        return $this;
    }

    /**
     * Get publishSince
     *
     * @return \DateTime
     */
    public function getPublishSince()
    {
        return $this->publishSince;
    }

    /**
     * Set publishUntil
     *
     * @param  \DateTime $endAt
     * @return Banner
     */
    public function setPublishUntil($publishUntil)
    {
        $this->publishUntil = $publishUntil;

        return $this;
    }

    /**
     * Get publishUntil
     *
     * @return \DateTime
     */
    public function getPublishUntil()
    {
        return $this->publishUntil;
    }

    /**
     * Set urlExclude
     *
     * @param  string $urlExclude
     * @return Banner
     */
    public function setUrlExclude($urlExclude)
    {
        $this->urlExclude = $urlExclude;

        return $this;
    }

    /**
     * Get urlExclude
     *
     * @return string
     */
    public function getUrlExclude()
    {
        return $this->urlExclude;
    }

    /**
     * Set urlInclude
     *
     * @param  string $urlInclude
     * @return Banner
     */
    public function setUrlInclude($urlInclude)
    {
        $this->urlInclude = $urlInclude;

        return $this;
    }

    /**
     * Get urlInclude
     *
     * @return string
     */
    public function getUrlInclude()
    {
        return $this->urlInclude;
    }

    /**
     * Set modifiedAt
     *
     * @param  \DateTime $modifiedAt
     * @return Banner
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set type
     *
     * @param  BannerType $type
     * @return Banner
     */
    public function setType(BannerType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return BannerType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set resourceId
     *
     * @param  integer $resourceId
     * @return Banner
     */
    public function setResourceId($resourceId = null)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * Get region
     *
     * @return integer
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }
}
