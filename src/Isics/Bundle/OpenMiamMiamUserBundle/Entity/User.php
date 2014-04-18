<?php

namespace Isics\Bundle\OpenMiamMiamUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamUserBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 *
 * @ExclusionPolicy("all")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=128, nullable=false)
     * @Expose
     */
    private $firstname;

    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=128, nullable=false)
     * @Expose
     */
    private $lastname;

    /**
     * @var string $address1
     *
     * @ORM\Column(name="address1", type="string", length=64, nullable=false)
     * @Expose
     */
    private $address1;

    /**
     * @var string $address2
     *
     * @ORM\Column(name="address2", type="string", length=64, nullable=true)
     * @Expose
     */
    private $address2;

    /**
     * @var string $zipcode
     *
     * @ORM\Column(name="zipcode", type="string", length=8, nullable=false)
     * @Expose
     */
    private $zipcode;

    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=64, nullable=false)
     * @Expose
     */
    private $city;

    /**
     * @var string $phoneNumber
     *
     * @ORM\Column(name="phone_number", type="string", length=16, nullable=true)
     * @Expose
     */
    private $phoneNumber;

    /**
     * @var Doctrine\Common\Collections\Collection $salesOrders
     *
     * @ORM\OneToMany(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\SalesOrder", mappedBy="user")
     */
    private $salesOrders;

    /**
     * @var Doctrine\Common\Collections\Collection $subscriptions
     *
     * @ORM\OneToMany(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\Subscription", mappedBy="user")
     */
    private $subscriptions;

    /**
     * @var boolean $isOrdersOpenNotificationSubscriber
     *
     * @ORM\Column(name="is_orders_open_notification_subscriber", type="boolean", nullable=false, options={"default":1})
     * @Expose
     */
    private $isOrdersOpenNotificationSubscriber;

    /**
     * @var boolean $isNewsletterSubscriber
     *
     * @ORM\Column(name="is_newsletter_subscriber", type="boolean", nullable=false, options={"default":1})
     * @Expose
     */
    private $isNewsletterSubscriber;

    /**
     * @var \Doctrine\Common\Collections\Collection $payments
     *
     * @ORM\OneToMany(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\Payment", mappedBy="user")
     */
    private $payments;

    /**
     * @var \Doctrine\Common\Collections\Collection $activityLogs
     *
     * @ORM\OneToMany(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\Activity", mappedBy="user")
     */
    private $activityLogs;

    /**
     * @var \Doctrine\Common\Collections\Collection $comments
     *
     * @ORM\OneToMany(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\Comment", mappedBy="user")
     */
    private $comments;

    /**
     * @var \Doctrine\Common\Collections\Collection $writtenComments
     * 
     * @ORM\OneToMany(targetEntity="Isics\Bundle\OpenMiamMiamBundle\Entity\Comment", mappedBy="writer")
     */
    private $writtenComments;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->isOrdersOpenNotificationSubscriber = true;
        $this->isNewsletterSubscriber = true;
    }

    /**
     * Add comments
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $comments
     * @return User
     */
    public function addComment(\Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    
        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $comments
     */
    public function removeComment(\Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add writtenComments
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $writtenComments
     * @return User
     */
    public function addWrittenComment(\Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $writtenComments)
    {
        $this->writtenComments[] = $writtenComments;
    
        return $this;
    }

    /**
     * Remove writtenComments
     *
     * @param \Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $writtenComments
     */
    public function removeWrittenComment(\Isics\Bundle\OpenMiamMiamBundle\Entity\Comment $writtenComments)
    {
        $this->writtenComments->removeElement($writtenComments);
    }

    /**
     * Get writtenComments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWrittenComments()
    {
        return $this->writtenComments;
    }
}
