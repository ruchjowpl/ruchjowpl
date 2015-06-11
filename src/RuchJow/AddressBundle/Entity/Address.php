<?php

namespace RuchJow\AddressBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="address")
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true, unique=false)
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=false)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true, unique=false)
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=false, unique=false)
     */
    protected $house;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true, unique=false)
     */
    protected $flat;

    /**
     * @var string
     *
     * @ORM\Column(name="post_code", type="string", length=6, nullable=false, unique=false)
     */
    protected $postCode;



    //*********************************
    //****** GETTERS and SETTERS ******
    //*********************************

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param string $house
     *
     * @return $this
     */
    public function setHouse($house)
    {
        $this->house = $house;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param string $flat
     *
     * @return $this
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @param string $postCode
     *
     * @return $this
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    public function toArray()
    {
        return array(
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'country'   => $this->country,
            'city'      => $this->city,
            'street'    => $this->street,
            'house'     => $this->house,
            'flat'      => $this->flat,
            'postCode'  => $this->postCode,
        );
    }

}