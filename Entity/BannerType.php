<?php
namespace Acilia\Bundle\BannerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BannerType
 *
 * @ORM\Table(name="banner_type")
 * @ORM\Entity
 */
class BannerType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="banner_type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="banner_type_name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="banner_type_slug", type="string", length=255, nullable=false)
     */
    private $slug;

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
     * Set name
     *
     * @param  string     $name
     * @return BannerType
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
     * Set slug
     *
     * @param  string     $slug
     * @return BannerType
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
