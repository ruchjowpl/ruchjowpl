<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TerritorialUnitsBundle\Entity\GeoShapeRepository")
 * @ORM\Table(name="geo_shape")
 */
class GeoShape
{
    const TYPE_COUNTRY  = 'country';
    const TYPE_REGION   = 'region';
    const TYPE_DISTRICT = 'district';
    const TYPE_COMMUNE  = 'commune';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=65536, nullable=false)
     */
    protected $shape;

    /**
     * @var float[]
     *
     * @ORM\Column(name="center", type="simple_array", nullable=true)
     */
    protected $center;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var Commune
     *
     * @ORM\ManyToOne(targetEntity="Commune", inversedBy="geoShapes")
     * @ORM\JoinColumn(name="commune_id")
     */
    protected $commune;

    /**
     * @var District
     *
     * @ORM\ManyToOne(targetEntity="District", inversedBy="geoShapes")
     * @ORM\JoinColumn(name="district_id")
     */
    protected $district;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="geoShapes")
     * @ORM\JoinColumn(name="region_id")
     */
    protected $region;

    /**
     * @var GeoShape
     *
     * @ORM\ManyToOne(targetEntity="GeoShape", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var ArrayCollection|GeoShape[]
     *
     * @ORM\OneToMany(targetEntity="GeoShape", mappedBy="parent")
     */
    protected $children;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={ "default"=1 })
     */
    protected $version = 1;


    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

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
     * Get google-encoded shape (Polyline) as a string
     *
     * @return string
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Set google-encoded shape (Polyline) as a string
     *
     * @param string $shape
     *
     * @return GeoShape
     */
    public function setShape($shape)
    {
        $this->shape = $shape;

        return $this;
    }

    /**
     * Get an array representing central point of the shape
     *
     * @return float[]
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * Set an array representing central point of the shape
     *
     * @param array $center
     *
     * @return GeoShape
     *
     * @throws \RuntimeException
     */
    public function setCenter($center = null)
    {
        if (!$center) {
            $this->center = null;
        } elseif (count($center) != 2) {
            throw new \RuntimeException('Invalid center coordinates.');
        } else {
            $this->center = $center;
        }

        return $this;
    }

    /**
     * Get shape type (country|region|district|commune)
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set shape type (country|region|district|commune). Other values trigger an exception
     *
     * @param type $type
     *
     * @return GeoShape
     *
     * @throws \RuntimeException
     */
    public function setType($type)
    {
        if (!in_array($type, array(
            self::TYPE_COUNTRY,
            self::TYPE_REGION,
            self::TYPE_DISTRICT,
            self::TYPE_COMMUNE,
        ))) {
            throw new \RuntimeException('Invalid GeoShape type: ' . $type);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get name to display
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name to display
     *
     * @param string $name
     *
     * @return GeoShape
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Commune reference (if any)
     *
     * @return Commune
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Set Commune reference
     *
     * @param Commune $commune
     *
     * @return GeoShape
     */
    public function setCommune(Commune $commune)
    {
        $this->commune = $commune;
        if (!$this->getDistrict() && $commune->getDistrict()) {
            $this->setDistrict($commune->getDistrict());
        }

        return $this;
    }

    /**
     * Get District reference (if any)
     *
     * @return District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set District reference
     *
     * @param District $district
     *
     * @return GeoShape
     */
    public function setDistrict(District $district)
    {
        $this->district = $district;

        if (!$this->getRegion() && $district->getRegion()) {
            $this->setRegion($district->getRegion());
        }

        return $this;
    }

    /**
     * Get Region reference (if any)
     *
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set Region reference
     *
     * @param Region $region
     *
     * @return GeoShape
     */
    public function setRegion(Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get parent shape (if any)
     *
     * @return type
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent shape
     *
     * @param GeoShape $parent
     *
     * @return GeoShape
     */
    public function setParent(GeoShape $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get children (if any)
     *
     * @return ArrayCollection|GeoShape[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Count children (lazily, without fetching them)
     *
     * @return int
     */
    public function countChildren()
    {
        return $this->children->count();
    }

    /**
     * Set children collection
     *
     * @param ArrayCollection $children
     *
     * @return GeoShape
     */
    public function setChildren(ArrayCollection $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get version (should be usable when we add another set of data)
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version (should be usable when we add another set of data)
     *
     * @param int $version
     *
     * @return GeoShape
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get related territorial unit (the smallest one)
     *
     * @return Comune|District|Region|null
     */
    public function getTerritorialUnit()
    {
        switch ($this->getType()) {
            case self::TYPE_COMMUNE:  return $this->getCommune();
            case self::TYPE_DISTRICT: return $this->getDistrict();
            case self::TYPE_REGION:   return $this->getRegion();
        }

        return null;
    }

    /**
     * @return bool|int|null
     */
    public function getTerritorialUnitId()
    {
        switch ($this->type) {
            case self::TYPE_COUNTRY:
                return null;
            case self::TYPE_REGION:
                return $this->getRegion()->getId();
            case self::TYPE_DISTRICT:
                return $this->getDistrict()->getId();
            case self::TYPE_COMMUNE:
                return $this->getCommune()->getId();
        }

        return false;
    }

    /**
     * Return this object transformed to an array, optionaly with fetched children
     *
     * @param bool $withChildren
     *
     * @return array
     */
    public function toArray($withChildren = false)
    {
        $ret = array(
            'id' => $this->getId(),
            'shape' => $this->getShape(),
            'center' => $this->getCenter(),
            'type' => $this->getType(),
            'territorial_unit_id' => $this->getTerritorialUnit()? $this->getTerritorialUnit()->getId() : null,
            'territorial_unit_name' => $this->getTerritorialUnit()? $this->getTerritorialUnit()->getName() : null,
            'name' => $this->getName(),
            'region' => $this->getRegion() ? $this->getRegion()->toArray() : null,
            'district' => $this->getDistrict() ? $this->getDistrict()->toArray() : null,
            'commune' => $this->getCommune() ? $this->getCommune()->toArray() : null,
            'children_count' => $this->countChildren(),
        );

        if ($withChildren) {
            $ret['children'] = array();
            foreach ($this->getChildren() as $child) {
                $ret['children'][] = $child->toArray(false); // only 1 level deep
            }
        }

        return $ret;
    }

}
