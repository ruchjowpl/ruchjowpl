<?php

namespace RuchJow\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use RuchJow\AddressBundle\Entity\Address;
use RuchJow\SocialLinksBundle\Entity\SocialLink;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\Country;

/**
 * @ORM\Entity(repositoryClass="RuchJow\UserBundle\Entity\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    const DISPLAY_NAME_NICK = 'nick';
    const DISPLAY_NAME_FULL_NAME = 'full_name';
    const DISPLAY_NAME_FULL_NAME_NICK = 'full_name_nick';
    const DISPLAY_NAME_REMOVED = 'removed';

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
     * @ORM\Column(name="nick", type="string", length=255, nullable=true, unique=true)
     */
    protected $nick;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name_format", type="string", length=255, nullable=false)
     */
    protected $displayNameFormat = self::DISPLAY_NAME_NICK;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=15, nullable=true)
     */
    protected $phone;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TerritorialUnitsBundle\Entity\Country")
     * @JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     */
    protected $country;

    /**
     * @var Commune
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TerritorialUnitsBundle\Entity\Commune")
     * @JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    protected $commune;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\Organisation", inversedBy="users")
     * @JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $organisation;

    /**
     * @var string
     *
     * @ORM\Column(name="referral_token", type="string", length=255)
     */
    protected $referralToken;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @JoinColumn(name="referrer_id", referencedColumnName="id", nullable=true)
     */
    protected $referrer;


    /**
     * @var string
     *
     * @ORM\Column(name="password_reset_token", type="string", length=255, nullable=true)
     */
    protected $passwordResetToken;

    /**
     * @var \DateTime()
     *
     * @ORM\Column(name="password_reset_requested_at", type="datetime", nullable=true)
     *
     */
    protected $passwordResetRequestedAt;


    /**
     * @var string
     *
     * @ORM\Column(name="remove_account_token", type="string", length=255, nullable=true)
     */
    protected $removeAccountToken;

    /**
     * @var \DateTime()
     *
     * @ORM\Column(name="remove_account_requested_at", type="datetime", nullable=true)
     *
     */
    protected $removeAccountRequestedAt;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="RuchJow\AddressBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    /**
     * @var ArrayCollection|SocialLink[]
     *
     * @ORM\OneToMany(targetEntity="RuchJow\SocialLinksBundle\Entity\SocialLink", mappedBy="user")
     */
    protected $socialLinks;

    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text", length=2000, nullable=false)
     */
    protected $about = '';

    /**
     * @var boolean
     *
     * @ORM\Column(name="local_gov", type="boolean", nullable=false)
     */
    protected $localGov = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="supports", type="boolean", nullable=false)
     */
    protected $supports = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="supported_at", type="datetime", nullable=true)
     *
     */
    protected $supportedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     *
     */
    protected $createdAt;

    /**
     * @var int
     *
     * @ORM\Column(name="reminder_counter", type="integer", nullable=false)
     */
    protected $reminderCounter = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="next_reminder_at", type="datetime", nullable=true)
     */
    protected $nextReminderAt;


    // Data visibility
    /**
     * @ORM\Column(name="first_name_visible", type="boolean", nullable=false)
     */
    protected $firstNameVisible = false;

    /**
     * @ORM\Column(name="last_name_visible", type="boolean", nullable=false)
     */
    protected $lastNameVisible = false;

    /**
     * @ORM\Column(name="organisation_visible", type="boolean", nullable=false)
     */
    protected $organisationVisible = true;

    /**
     * @ORM\Column(name="social_links_visible", type="boolean", nullable=false)
     */
    protected $socialLinksVisible = true;

    /**
     * @ORM\Column(name="about_visible", type="boolean", nullable=false)
     */
    protected $aboutVisible = true;


    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        parent::__construct();

        $this->socialLinks = new ArrayCollection();
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
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
     * @return User
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
     * Set nick
     *
     * @param string $nick
     *
     * @return User
     */
    public function setNick($nick)
    {
        $this->nick = $nick;
        $this->updateUsername();

        return $this;
    }

    /**
     * Get nick
     *
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @return string
     */
    public function getDisplayNameFormat()
    {
        return $this->displayNameFormat ? $this->displayNameFormat : self::DISPLAY_NAME_NICK;
    }

    /**
     * @param string $displayNameFormat
     *
     * @return $this
     */
    public function setDisplayNameFormat($displayNameFormat)
    {
        $this->displayNameFormat = $displayNameFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        switch ($this->getDisplayNameFormat()) {
            case self::DISPLAY_NAME_NICK:
                // It is default display name so we break the switch.
                break;

            case self::DISPLAY_NAME_FULL_NAME:
                if (
                    !$this->getFirstNameVisible()
                    || !$this->getLastNameVisible()
                    || !trim($this->getFirstName())
                    && !trim($this->getLastName())
                ) {
                    break;
                }

                return trim(trim($this->getFirstName()) . ' ' . trim($this->getLastName()));

            case self::DISPLAY_NAME_FULL_NAME_NICK:
                return $this->getFirstName() . ' ' . $this->getLastName()
                . ' (' . $this->getUsername() . ')';

            case self::DISPLAY_NAME_REMOVED:
                return '-';
        }

        // Default display nam
        return $this->getUsername();
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set country
     *
     * @param Country $country
     *
     * @return User
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set commune
     *
     * @param Commune $commune
     *
     * @return User
     */
    public function setCommune(Commune $commune = null)
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * Get commune
     *
     * @return Commune
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * Sets the email.
     *
     * {@inheritdoc}
     * It also triggers update of username.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->updateUsername();

        return $this;
    }

    /**
     * Updates username based on nick and email.
     */
    protected function updateUsername()
    {
        if ($this->nick) {
            if ($this->username !== $this->nick) {
                $this->username = $this->nick;
            }

            return;
        }

        if (!$this->username) {
            $this->username = $this->email;
        }
    }

    /**
     * Set organisation
     *
     * @param Organisation $organisation
     *
     * @return User
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set referralToken
     *
     * @param string $referralToken
     *
     * @return User
     */
    public function setReferralToken($referralToken)
    {
        $this->referralToken = $referralToken;

        return $this;
    }

    /**
     * Get referralToken
     *
     * @return string
     */
    public function getReferralToken()
    {
        return $this->referralToken;
    }


    /**
     * Sets referrer
     *
     * @param User $referrer
     *
     * @return User
     */
    public function setReferrer(User $referrer = null)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Gets referrer
     *
     * @return User
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * Set passwordResetToken
     *
     * @param string $passwordResetToken
     *
     * @return User
     */
    public function setPasswordResetToken($passwordResetToken)
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }

    /**
     * Get passwordResetToken
     *
     * @return string
     */
    public function getPasswordResetToken()
    {
        return $this->passwordResetToken;
    }

    /**
     * Set passwordResetRequestedAt
     *
     * @param \DateTime $passwordResetRequestedAt
     *
     * @return User
     */
    public function setPasswordResetRequestedAt($passwordResetRequestedAt)
    {
        $this->passwordResetRequestedAt = $passwordResetRequestedAt;

        return $this;
    }

    /**
     * Get passwordResetRequestedAt
     *
     * @return \DateTime
     */
    public function getPasswordResetRequestedAt()
    {
        return $this->passwordResetRequestedAt;
    }

    /**
     * @param $ttl
     *
     * @return bool
     */
    public function isPasswordResetRequestNonExpired($ttl)
    {
        return $this->getPasswordResetRequestedAt() instanceof \DateTime &&
        $this->getPasswordResetRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * @return string
     */
    public function getRemoveAccountToken()
    {
        return $this->removeAccountToken;
    }

    /**
     * @param string $removeAccountToken
     *
     * @return $this
     */
    public function setRemoveAccountToken($removeAccountToken)
    {
        $this->removeAccountToken = $removeAccountToken;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRemoveAccountRequestedAt()
    {
        return $this->removeAccountRequestedAt;
    }

    /**
     * @param \DateTime $removeAccountRequestedAt
     *
     * @return $this
     */
    public function setRemoveAccountRequestedAt($removeAccountRequestedAt)
    {
        $this->removeAccountRequestedAt = $removeAccountRequestedAt;

        return $this;
    }

    /**
     * @param $ttl
     *
     * @return bool
     */
    public function isRemoveAccountRequestNonExpired($ttl)
    {
        return $this->getRemoveAccountRequestedAt() instanceof \DateTime &&
            $this->getRemoveAccountRequestedAt()->getTimestamp() + $ttl > time();
    }


    /**
     * Set address
     *
     * @param Address $address
     *
     * @return User
     */
    public function setAddress(Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Add socialLinks
     *
     * @param SocialLink $socialLinks
     *
     * @return User
     */
    public function addSocialLink(SocialLink $socialLinks)
    {
        $this->socialLinks[] = $socialLinks;

        return $this;
    }

    /**
     * Remove socialLinks
     *
     * @param SocialLink $socialLinks
     */
    public function removeSocialLink(SocialLink $socialLinks)
    {
        $this->socialLinks->removeElement($socialLinks);
    }

    /**
     * Get socialLinks
     *
     * @return Collection|SocialLink[]
     */
    public function getSocialLinks()
    {
        return $this->socialLinks;
    }


    /**
     * Set about
     *
     * @param string $about
     *
     * @return User
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * @return boolean
     */
    public function isLocalGov()
    {
        return $this->localGov;
    }

    /**
     * @param boolean $localGov
     *
     * @return $this
     */
    public function setLocalGov($localGov)
    {
        $this->localGov = $localGov;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSupports()
    {
        return $this->supports;
    }

    /**
     * @param boolean $supports
     *
     * @return $this
     */
    public function setSupports($supports)
    {
        $this->supports = $supports;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSupportedAt()
    {
        return $this->supportedAt;
    }

    /**
     * @param \DateTime $supportedAt
     *
     * @return $this
     */
    public function setSupportedAt($supportedAt)
    {
        $this->supportedAt = $supportedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    /**
     * Set reminderCounter
     *
     * @param integer $reminderCounter
     *
     * @return User
     */
    public function setReminderCounter($reminderCounter)
    {
        $this->reminderCounter = $reminderCounter;

        return $this;
    }

    /**
     * Get reminderCounter
     *
     * @return integer
     */
    public function getReminderCounter()
    {
        return $this->reminderCounter;
    }


    /**
     * Set nextReminderAt
     *
     * @param \DateTime $nextReminderAt
     *
     * @return User
     */
    public function setNextReminderAt($nextReminderAt)
    {
        $this->nextReminderAt = $nextReminderAt;

        return $this;
    }

    /**
     * Get nextReminderAt
     *
     * @return \DateTime
     */
    public function getNextReminderAt()
    {
        return $this->nextReminderAt;
    }

    /**
     * @return mixed
     */
    public function getFirstNameVisible()
    {
        return $this->firstNameVisible;
    }

    /**
     * @param mixed $firstNameVisible
     *
     * @return $this
     */
    public function setFirstNameVisible($firstNameVisible)
    {
        $this->firstNameVisible = $firstNameVisible;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastNameVisible()
    {
        return $this->lastNameVisible;
    }

    /**
     * @param mixed $lastNameVisible
     *
     * @return $this
     */
    public function setLastNameVisible($lastNameVisible)
    {
        $this->lastNameVisible = $lastNameVisible;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganisationVisible()
    {
        return $this->organisationVisible;
    }

    /**
     * @param mixed $organisationVisible
     *
     * @return $this
     */
    public function setOrganisationVisible($organisationVisible)
    {
        $this->organisationVisible = $organisationVisible;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSocialLinksVisible()
    {
        return $this->socialLinksVisible;
    }

    /**
     * @param mixed $socialLinksVisible
     *
     * @return $this
     */
    public function setSocialLinksVisible($socialLinksVisible)
    {
        $this->socialLinksVisible = $socialLinksVisible;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAboutVisible()
    {
        return $this->aboutVisible;
    }

    /**
     * @param mixed $aboutVisible
     *
     * @return $this
     */
    public function setAboutVisible($aboutVisible)
    {
        $this->aboutVisible = $aboutVisible;

        return $this;
    }

    public function getVisibilityArray()
    {
        return array(
            'firstName'    => $this->getFirstNameVisible(),
            'lastName'     => $this->getLastNameVisible(),
            'organisation' => $this->getOrganisationVisible(),
            'socialLinks'  => $this->getSocialLinksVisible(),
            'about'        => $this->getAboutVisible(),
        );
    }


    public function getPublicData()
    {
        return array();
    }
}
