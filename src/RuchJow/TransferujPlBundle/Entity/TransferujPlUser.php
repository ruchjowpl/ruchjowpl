<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 10/14/14
 * Time: 10:33 AM
 */

namespace RuchJow\TransferujPlBundle\Entity;


class TransferujPlUser
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $securityCode;

    public function __construct($id, $securityCode)
    {
        $this->id           = $id;
        $this->securityCode = $securityCode;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecurityCode()
    {
        return $this->securityCode;
    }

    /**
     * @param string $securityCode
     *
     * @return $this
     */
    public function setSecurityCode($securityCode)
    {
        $this->securityCode = $securityCode;

        return $this;
    }


}