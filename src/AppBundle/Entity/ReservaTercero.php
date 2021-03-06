<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReservaTercero
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReservaTerceroRepository")
 * @ORM\Table(name="reserva_tercero",
 *     indexes={
 *          @ORM\Index(name="idx_reserva_tercero_type", columns={"type"}),
 *          @ORM\Index(name="idx_reserva_tercero_provider_serial", columns={"provider_serial"}),
 *          @ORM\Index(name="idx_reserva_tercero_client_serial", columns={"client_serial"}),
 *          @ORM\Index(name="idx_reserva_tercero_start_at", columns={"start_at"})
 *     })
 */
class ReservaTercero
{
    const TYPE_MICROBUS = 'microbus';
    const TYPE_CLASICOS = 'clasicos';
    const TYPE_GUIA = 'guia';

    const STATE_CREATED = 'created';
    const STATE_EXECUTED = 'executed';
    const STATE_CANCELLED = 'cancelled';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     * @Assert\Regex("/^(microbus|clasicos|guia)$/")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(length=20)
     * @Assert\Regex("/^(created|executed|cancelled)$/")
     */
    private $state;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $confirmed;

    /**
     * @var ServiceType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ServiceType")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $serviceType;

    /**
     * @var Provider
     *
     * @ORM\ManyToOne(targetEntity="Provider")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(length=20, nullable=true)
     * @Assert\Length(max=20)
     */
    private $clientSerial;

    /**
     * @var ProviderTercero
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ThirdProvider")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $provider;

    /**
     * @var string
     *
     * @ORM\Column(length=20, nullable=true)
     * @Assert\Length(max=20)
     */
    private $providerSerial;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime")
     */
    private $startAt;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $startIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $endIn;

    /**
     * @var string
     *
     * @ORM\Column(name="service_description", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $serviceDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="client_names", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $clientNames;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pax;

    /**
     * @var string
     *
     * @ORM\Column(name="execution_issues", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $executionIssues;

    /**
     * @var string
     *
     * @ORM\Column(name="cancellation_issues", type="text", nullable=true)
     * @Assert\Length(max=32000)
     */
    private $cancellationIssues;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var ThirdPayAct
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ThirdPayAct", inversedBy="services")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $payAct;

    /**
     * @var float
     *
     * @ORM\Column(name="paid_charge", type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\NotBlank(groups={"Pay"})
     */
    private $paidCharge;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Assert\Length(max=255)
     */
    private $payNotes;

    /**
     * @var ThirdCobro
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ThirdCobro", inversedBy="services")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $cobro;

    /**
     * @var float
     *
     * @ORM\Column(name="cobro_charge", type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\NotBlank(groups={"Cobro"})
     */
    private $cobroCharge;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Assert\Length(max=255)
     */
    private $cobroNotes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="control_1", type="string", length=10, nullable=true)
     */
    private $control1;

    /**
     * @var boolean
     *
     * @ORM\Column(name="control_2", type="string", length=10, nullable=true)
     */
    private $control2;

    /**
     * ReservaTercero constructor.
     */
    public function __construct()
    {
        $this->state = self::STATE_CREATED;
        $this->confirmed = false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_null($this->getProvider()) && $this->getProvider()->getIsSerialGenerator() && !is_null($this->id) && !is_null($this->startAt)) {
            return sprintf('%s%s-%04s',
                $this->getProvider()->getSerialPrefix(),
                substr($this->startAt->format('ymd'), 1, 5),
                $this->id
                );
        } else {
            return (string) $this->getProviderSerial();
        }
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
     * Set type
     *
     * @param string $type
     *
     * @return ReservaTercero
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set providerSerial
     *
     * @param string $providerSerial
     *
     * @return ReservaTercero
     */
    public function setProviderSerial($providerSerial)
    {
        $this->providerSerial = $providerSerial;

        return $this;
    }

    /**
     * Get providerSerial
     *
     * @return string
     */
    public function getProviderSerial()
    {
        return $this->providerSerial;
    }

    /**
     * Set clientSerial
     *
     * @param string $clientSerial
     *
     * @return ReservaTercero
     */
    public function setClientSerial($clientSerial)
    {
        $this->clientSerial = $clientSerial;

        return $this;
    }

    /**
     * Get clientSerial
     *
     * @return string
     */
    public function getClientSerial()
    {
        return $this->clientSerial;
    }

    /**
     * Set startAt
     *
     * @param \DateTime $startAt
     *
     * @return ReservaTercero
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;

        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set endAt
     *
     * @param \DateTime $endAt
     *
     * @return ReservaTercero
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ReservaTercero
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
     * Set client
     *
     * @param \AppBundle\Entity\Provider $client
     *
     * @return ReservaTercero
     */
    public function setClient(\AppBundle\Entity\Provider $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \AppBundle\Entity\Provider
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set startIn
     *
     * @param \AppBundle\Entity\Place $startIn
     *
     * @return ReservaTercero
     */
    public function setStartIn(\AppBundle\Entity\Place $startIn)
    {
        $this->startIn = $startIn;

        return $this;
    }

    /**
     * Get startIn
     *
     * @return \AppBundle\Entity\Place
     */
    public function getStartIn()
    {
        return $this->startIn;
    }

    /**
     * Set endIn
     *
     * @param \AppBundle\Entity\Place $endIn
     *
     * @return ReservaTercero
     */
    public function setEndIn(\AppBundle\Entity\Place $endIn = null)
    {
        $this->endIn = $endIn;

        return $this;
    }

    /**
     * Get endIn
     *
     * @return \AppBundle\Entity\Place
     */
    public function getEndIn()
    {
        return $this->endIn;
    }

    /**
     * Set serviceType
     *
     * @param \AppBundle\Entity\ServiceType $serviceType
     *
     * @return ReservaTercero
     */
    public function setServiceType(\AppBundle\Entity\ServiceType $serviceType)
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * Get serviceType
     *
     * @return \AppBundle\Entity\ServiceType
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * Set serviceDescription
     *
     * @param string $serviceDescription
     *
     * @return ReservaTercero
     */
    public function setServiceDescription($serviceDescription)
    {
        $this->serviceDescription = $serviceDescription;

        return $this;
    }

    /**
     * Get serviceDescription
     *
     * @return string
     */
    public function getServiceDescription()
    {
        return $this->serviceDescription;
    }

    /**
     * Set provider
     *
     * @param \AppBundle\Entity\ThirdProvider $provider
     *
     * @return ReservaTercero
     */
    public function setProvider(\AppBundle\Entity\ThirdProvider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \AppBundle\Entity\ThirdProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set clientNames
     *
     * @param string $clientNames
     *
     * @return ReservaTercero
     */
    public function setClientNames($clientNames)
    {
        $this->clientNames = $clientNames;

        return $this;
    }

    /**
     * Get clientNames
     *
     * @return string
     */
    public function getClientNames()
    {
        return $this->clientNames;
    }

    /**
     * Set pax
     *
     * @param integer $pax
     *
     * @return ReservaTercero
     */
    public function setPax($pax)
    {
        $this->pax = $pax;

        return $this;
    }

    /**
     * Get pax
     *
     * @return integer
     */
    public function getPax()
    {
        return $this->pax;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return ReservaTercero
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set executionIssues
     *
     * @param string $executionIssues
     *
     * @return ReservaTercero
     */
    public function setExecutionIssues($executionIssues)
    {
        $this->executionIssues = $executionIssues;

        return $this;
    }

    /**
     * Get executionIssues
     *
     * @return string
     */
    public function getExecutionIssues()
    {
        return $this->executionIssues;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ReservaTercero
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set cancellationIssues
     *
     * @param string $cancellationIssues
     *
     * @return ReservaTercero
     */
    public function setCancellationIssues($cancellationIssues)
    {
        $this->cancellationIssues = $cancellationIssues;

        return $this;
    }

    /**
     * Get cancellationIssues
     *
     * @return string
     */
    public function getCancellationIssues()
    {
        return $this->cancellationIssues;
    }

    /**
     * Set payAct
     *
     * @param \AppBundle\Entity\ThirdPayAct $payAct
     *
     * @return ReservaTercero
     */
    public function setPayAct(\AppBundle\Entity\ThirdPayAct $payAct = null)
    {
        $this->payAct = $payAct;

        return $this;
    }

    /**
     * Get payAct
     *
     * @return \AppBundle\Entity\ThirdPayAct
     */
    public function getPayAct()
    {
        return $this->payAct;
    }

    /**
     * Set payNotes
     *
     * @param string $payNotes
     *
     * @return ReservaTercero
     */
    public function setPayNotes($payNotes)
    {
        $this->payNotes = $payNotes;

        return $this;
    }

    /**
     * Get payNotes
     *
     * @return string
     */
    public function getPayNotes()
    {
        return $this->payNotes;
    }

    /**
     * Set paidCharge
     *
     * @param string $paidCharge
     *
     * @return ReservaTercero
     */
    public function setPaidCharge($paidCharge)
    {
        $this->paidCharge = $paidCharge;

        return $this;
    }

    /**
     * Get paidCharge
     *
     * @return string
     */
    public function getPaidCharge()
    {
        return $this->paidCharge;
    }

    /**
     * Set cobroNotes
     *
     * @param string $cobroNotes
     *
     * @return ReservaTercero
     */
    public function setCobroNotes($cobroNotes)
    {
        $this->cobroNotes = $cobroNotes;

        return $this;
    }

    /**
     * Get cobroNotes
     *
     * @return string
     */
    public function getCobroNotes()
    {
        return $this->cobroNotes;
    }

    /**
     * Set cobro
     *
     * @param \AppBundle\Entity\ThirdCobro $cobro
     *
     * @return ReservaTercero
     */
    public function setCobro(\AppBundle\Entity\ThirdCobro $cobro = null)
    {
        $this->cobro = $cobro;

        return $this;
    }

    /**
     * Get cobro
     *
     * @return \AppBundle\Entity\ThirdCobro
     */
    public function getCobro()
    {
        return $this->cobro;
    }

    /**
     * Set cobroCharge
     *
     * @param string $cobroCharge
     *
     * @return ReservaTercero
     */
    public function setCobroCharge($cobroCharge)
    {
        $this->cobroCharge = $cobroCharge;

        return $this;
    }

    /**
     * Get cobroCharge
     *
     * @return string
     */
    public function getCobroCharge()
    {
        return $this->cobroCharge;
    }

    /**
     * Set control1
     *
     * @param string $control1
     *
     * @return ReservaTercero
     */
    public function setControl1($control1)
    {
        $this->control1 = $control1;

        return $this;
    }

    /**
     * Get control1
     *
     * @return string
     */
    public function getControl1()
    {
        return $this->control1;
    }

    /**
     * Set control2
     *
     * @param string $control2
     *
     * @return ReservaTercero
     */
    public function setControl2($control2)
    {
        $this->control2 = $control2;

        return $this;
    }

    /**
     * Get control2
     *
     * @return string
     */
    public function getControl2()
    {
        return $this->control2;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return ReservaTercero
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }
}
