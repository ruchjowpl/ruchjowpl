<?php

namespace RuchJow\MailPoolBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Payment
 *
 * @ORM\Entity(repositoryClass="RuchJow\MailPoolBundle\Entity\PoolElement")
 * @ORM\Table(name="fr_fm_pool_element")
 */
class PoolElement {

    const InternalMailerError = -1;
    const InternalMailerErrorMessage = 'Internal Mailer error';

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=true)
     */
    protected $sentAt;

    /**
     * @var string
     *
     * @ORM\Column(name="last_mailer", type="string", nullable=false)
     */
    protected $lastMailer = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="last_error", type="integer", nullable=true)
     */
    protected $lastError;

    /**
     * @var string
     *
     * @ORM\Column(name="last_error_message", type="string", length=255, nullable=true)
     */
    protected $lastErrorMessage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_error_at", type="datetime", nullable=true)
     */
    protected $lastErrorAt;

    /**
     * @var int
     *
     * @ORM\Column(name="failed_send_attempts", type="integer", nullable=false)
     */
    protected $failedSendAttempts = 0;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="next_send_attempt_after", type="datetime", nullable=true)
     */
    protected $nextSendAttemptAfter;

    /**
     * @var string
     *
     * @ORM\Column(name="subscriber", type="string", nullable=false)
     */
    protected $subscriber;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=false)
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    protected $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_html", type="boolean", nullable=false)
     */
    protected $htmlContent = true;

    /**
     * @var string
     *
     * @ORM\Column(name="from_email", type="string", length=255, nullable=true)
     */
    protected $from;

    /**
     * @var string
     *
     * @ORM\Column(name="from_name", type="string", length=255, nullable=true)
     */
    protected $fromName;

    /**
     * @var string
     *
     * @ORM\Column(name="reply_to", type="string", length=255, nullable=true)
     */
    protected $replyTo;

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=255, nullable=true)
     */
    protected $tag;




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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set sentAt
     *
     * @param \DateTime $sentAt
     * @return $this
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @return string
     */
    public function getLastMailer()
    {
        return $this->lastMailer;
    }

    /**
     * @param string $lastMailer
     *
     * @return $this
     */
    public function setLastMailer($lastMailer)
    {
        $this->lastMailer = $lastMailer;

        return $this;
    }


    /**
     * Set lastError
     *
     * @param integer $lastError
     * @return $this
     */
    public function setLastError($lastError)
    {
        $this->lastError = $lastError;

        return $this;
    }

    /**
     * Get lastError
     *
     * @return integer
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param string $lastErrorMessage
     *
     * @return $this
     */
    public function setLastErrorMessage($lastErrorMessage)
    {
        $this->lastErrorMessage = $lastErrorMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }


    /**
     * Set lastErrorAt
     *
     * @param \DateTime $lastErrorAt
     * @return $this
     */
    public function setLastErrorAt($lastErrorAt)
    {
        $this->lastErrorAt = $lastErrorAt;

        return $this;
    }

    /**
     * Get lastErrorAt
     *
     * @return \DateTime
     */
    public function getLastErrorAt()
    {
        return $this->lastErrorAt;
    }

    /**
     * @param int $failedSendAttempts
     *
     * @return $this
     */
    public function setFailedSendAttempts($failedSendAttempts)
    {
        $this->failedSendAttempts = $failedSendAttempts;

        return $this;
    }

    /**
     * @return $this
     */
    public function incFailedSendAttempts()
    {
        $this->failedSendAttempts++;

        return $this;
    }

    /**
     * @return int
     */
    public function getFailedSendAttempts()
    {
        return $this->failedSendAttempts;
    }

    /**
     * @param \DateTime $nextSendAttemptAfter
     *
     * @return $this
     */
    public function setNextSendAttemptAfter($nextSendAttemptAfter)
    {
        $this->nextSendAttemptAfter = $nextSendAttemptAfter;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNextSendAttemptAfter()
    {
        return $this->nextSendAttemptAfter;
    }


    /**
     * Set subscriber
     *
     * @param string $subscriber
     * @return $this
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    /**
     * Get subscriber
     *
     * @return string
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set htmlContent
     *
     * @param boolean $htmlContent
     * @return $this
     */
    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * Get htmlContent
     *
     * @return boolean
     */
    public function isHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * Set from
     *
     * @param string $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set fromName
     *
     * @param string $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Get fromName
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set replyTo
     *
     * @param string $replyTo
     * @return $this
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * Get replyTo
     *
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * Set tag
     *
     * @param string $tag
     * @return $this
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
}
