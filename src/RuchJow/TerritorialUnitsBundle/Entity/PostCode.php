<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TerritorialUnitsBundle\Entity\PostCodeRepository")
 * @ORM\Table(name="post_code")
 */
class PostCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6, nullable=false)
     */
    protected $code;

    /**
     * @var ArrayCollection|Commune[]
     *
     * @ORM\ManyToMany(targetEntity="Commune", inversedBy="postCodes")
     * @ORM\JoinTable(name="post_code_commune",
     *      joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="commune_id", referencedColumnName="id")}
     *      )
     */
    protected $communes;


    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        $this->communes = new ArrayCollection();
    }

    //*********************************
    //****** GETTERS and SETTERS ******
    //*********************************


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
     * Set code
     *
     * @param string $code
     *
     * @return PostCode
     */
    public function setCode($code)
    {
        if (!preg_match('/^(\d\d)-(\d\d\d)$/', $code, $matches)) {
            throw new \InvalidArgumentException('Post code should be in 54-321 format. ' . $code . ' given.');
        }

        $this->code = $code;
        $this->id = (int) ($matches[1] . $matches[2]);

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add communes
     *
     * @param Commune $communes
     *
     * @return PostCode
     */
    public function addCommune(Commune $communes)
    {
        $this->communes[] = $communes;

        return $this;
    }

    /**
     * Remove communes
     *
     * @param Commune $communes
     */
    public function removeCommune(Commune $communes)
    {
        $this->communes->removeElement($communes);
    }

    /**
     * Get communes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommunes()
    {
        return $this->communes;
    }

}
