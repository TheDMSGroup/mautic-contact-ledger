<?php

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;
use Mautic\LeadBundle\Entity\Lead;

/**
 * class Entry extends {@see \Mautic\CoreBundle\Entity\CommonEntity}
 *
 * @package \MauticPlugin\MauticContactLedgerBundle\Entity
 */
class Entry extends CommonEntity
{
    /**
     * @param Doctrine\ORM\Mapping\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('contact_ledger')
            ->setCustomRepositoryClass('MauticPlugin\MauticContactLedgerBundle\Entity\EntryRepository');

        $builder->addId();

        $builder->createField('dateAdded', 'datetime')
            ->columnName('date_added')
            ->build();

        $builder->createManyToOne('contact', 'Mautic\LeadBundle\Entity\Lead')
            ->addJoinColumn('contact_id', 'id', false)
            ->build();

        $builder->createManyToOne('campaign', 'Mautic\CampaignBundle\Entity\Campaign')
            ->addJoinColumn('campaign_id', 'id', false)
            ->build();

        $builder->createField('bundleName', 'string')
            ->columnname('bundle_name')
            ->length(100)
            ->nullable()
            ->build();

        $builder->createField('className', 'string')
            ->columnName('class_name')
            ->length(50)
            ->build();

        $builder->createField('objectId', 'integer')
            ->columnName('object_id')
            ->build();

        $builder->createField('activity', 'string')
            ->length(50)
            ->build();

        $builder->createField('memo', 'string')
            ->length(255)
            ->nullable()
            ->build();

        $builder->createField('cost', 'decimal')
            ->precision(19)
            ->scale(4)
            ->nullable()
            ->build();

        $builder->createField('revenue', 'decimal')
            ->precision(19)
            ->scale(4)
            ->nullable()
            ->build();
    }

    /**
     * @var int $id primary key read-only
     */
    private $id;

    /**
     * @var datetime $dateAdded time entry was made
     */
    private $dateAdded;

    /**
     * @var  \Mautic\LeadBundle\Entity\Lead $contact
     */
    private $contact;

    /**
     * @var \Mautic\CampaignBundle\Entity\Campaign $campaign
     */
    protected $campaign;

    /**
     * @var string $bundleName
     */
    private $bundleName;

    /**
     * @var string $className
     */
    private $className;

    /**
     * @var int $objectId
     */
    private $objectId;

    /**
     * @var string $activity
     */
    private $activity;

    /**
     * @var string $memo
     */
    private $memo;

    /**
     * @var string|float $cost
     */
    private $cost;

    /**
     * @var string|float $revenue
     */
    private $revenue;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string when the entry was added
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param string $dateAdded
     *
     * @return $this
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    /**
     * @return \Mautic\LeadBundle\Entity\Lead
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead $contact
     *
     * @return $this
     */
    public function setContact(Lead $contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return \Mautic\CampaignBundle\Entity\Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param \Mautic\CampaignBundle\Entity\Campaign
     *
     * @return $this
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @param string $bundleName
     *
     * @return $this
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;
        return $this;
    }

    /**
     * @return sting
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     *
     * @return $this
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return $this
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return string
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param string $activity
     *
     * @return $this
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param string $memo
     *
     * @retun $this
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
        return $this;
    }

    /**
     * @return string|float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param string|float $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return string|float
     */
    public function getRevenue()
    {
        return $this->revnue;
    }

    /**
     * @param string|float $revenue
     *
     * @return $this
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
        return $this;
    }
}
