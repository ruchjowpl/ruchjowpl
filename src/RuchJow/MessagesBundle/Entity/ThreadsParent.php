<?php

namespace RuchJow\MessagesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ORM\Entity(repositoryClass="RuchJow\MessagesBundle\Entity\ThreadsParentRepository")
 * @ORM\Table(name="msg_threads_parent")
 */
class ThreadsParent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="threads_cnt", type="integer", nullable=false)
     */
    protected $threadsCnt = 0;

    /**
     * @var Thread[]
     *
     * @ORM\OneToMany(targetEntity="RuchJow\MessagesBundle\Entity\Thread", mappedBy="threadsParent")
     */
    protected $threads;

    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        $this->threads = new ArrayCollection();
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
     * @return int
     */
    public function getThreadsCnt()
    {
        return $this->threadsCnt;
    }

    /**
     * @param int $threadsCnt
     *
     * @return $this
     */
    public function setThreadsCnt($threadsCnt)
    {
        $this->threadsCnt = $threadsCnt;

        return $this;
    }

    /**
     * @return Thread[]
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * @param Thread[] $threads
     *
     * @return $this
     */
    public function setThreads($threads)
    {
        $this->threads = $threads;

        return $this;
    }

}
