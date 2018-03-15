<?php

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;
use Mautic\LeadBundle\Entity\Lead;

/**
 * Class Entry.
 */
class Entry extends CommonEntity
{
    /**
     * @var int primary key read-only
     */
    protected $id;

    /**
     * @var \DateTime time entry was made
     */
    protected $dateAdded;

    /**
     * @var int
     */
    protected $contactId;

    /**
     * @var int
     */
    protected $campaignId;

    /**
     * @var string
     */
    protected $bundleName;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var string
     */
    protected $activity;

    /**
     * @var string
     */
    protected $memo;

    /**
     * @var string|float
     */
    protected $cost;

    /**
     * @var string|float
     */
    protected $revenue;

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('contact_ledger')
            ->setCustomRepositoryClass('MauticPlugin\MauticContactLedgerBundle\Entity\EntryRepository');

        $builder->addId();

        $builder->createField('dateAdded', 'datetime')
            ->columnName('date_added')
            ->build();

        $builder->createField('contactId', 'integer')
            ->columnName('contact_id')
            ->build();

        $builder->createField('campaignId', 'integer')
            ->columnName('campaign_id')
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
     * @return int
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead|int $contact
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setContactId($contact)
    {
        if ($contact instanceof Lead) {
            $this->contactId = $contact->getId();
        } elseif (is_int($contact)) {
            $this->contactId = $contact;
        } else {
            throw new \InvalidArgumentException(
                '$contact must be an integer or instance of "\\Mautic\\LeadBundle\\Entity\\Lead"'
            );
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @param \Mautic\CampaignBundle\Entity\Campaign|int $campaign
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setCampaignId($campaign)
    {
        if ($campaign instanceof Campaign) {
            $this->campaignId = $campaign->getId();
        } elseif (is_int($campaign)) {
            $this->campaignId = $campaign;
        } else {
            throw new \InvalidArgumentException(
                '$campaign must be an integer or instance of "\\Mautic\\CampaignBundle\\Entity\\Campaign"'
            );
        }

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
     * @return string
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
     * @return $this
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
        return $this->revenue;
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
