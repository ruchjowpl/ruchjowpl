<?php

namespace RuchJow\StatisticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="StatisticRepository")
 * @ORM\Table(name="statistic", indexes={@ORM\Index(name="name_int_data_idx", columns={"name", "intData"})})
 */
class Statistic
{
    /**
     * @ORM\Id
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Id
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $suffix = '';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000, nullable=false, unique=false)
     */
    protected $serializedData;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true, unique=false)
     */
    protected $intData;


    //*********************************
    //****** GETTERS and SETTERS ******
    //*********************************

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     *
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }


    /**
     * @return string
     */
    public function getSerializedData()
    {
        return $this->serializedData;
    }

    /**
     * @param string $serializedData
     *
     * @return $this
     */
    public function setSerializedData($serializedData)
    {
        $this->serializedData = $serializedData;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return null !== $this->intData
            ? $this->intData
            : unserialize($this->serializedData);
    }

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        if (is_float($data) && abs($data - round($data)) < 0.0000001) {
            $data = (int) round($data);
        }

        $this->intData = is_integer($data)
            ? $data
            : null;

        $this->serializedData = serialize($data);

        return $this;
    }

}
