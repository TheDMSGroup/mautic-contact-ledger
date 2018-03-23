<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic Community
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;
use Mautic\LeadBundle\Entity\Lead;

/**
 * Class LedgerEntry.
 */
class LedgerEntry extends CommonEntity
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
     * @var \Mautic\LeadBundle\Entity\Lead
     */
    protected $contact;

    /**
     * @var int
     */
    protected $campaignId;

    /**
     * @var \Mautic\CampaignBundle\Entity\Campaign
     */
    protected $campaign;

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
            ->setCustomRepositoryClass(
                'MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntryRepository'
            );

        $builder->addId();
        $builder->addDateAdded();

        $builder->createField('contactId', 'integer')
            ->columnName('contact_id')
            // Nullable because a new contact will not have an ID on pre-save.
            ->nullable()
            ->build();

        $builder->createField('campaignId', 'integer')
            ->columnName('campaign_id')
            ->nullable()
            ->build();

        $builder->createField('bundleName', 'string')
            ->columnName('bundle_name')
            ->length(100)
            ->nullable()
            ->build();

        $builder->createField('className', 'string')
            ->columnName('class_name')
            ->length(50)
            ->nullable()
            ->build();

        $builder->createField('objectId', 'integer')
            ->columnName('object_id')
            ->nullable()
            ->build();

        $builder->createField('activity', 'string')
            ->length(50)
            ->nullable()
            ->build();

        $builder->createField('memo', 'text')
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
     * @param \Mautic\LeadBundle\Entity\Lead|int $contactId
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setContactId($contactId)
    {
        if ($contactId instanceof Lead) {
            $this->setContact($contactId);
        } elseif (\is_int($contactId)) {
            $this->contactId = $contactId;
        } else {
            throw new \InvalidArgumentException(
                '$contact must be an integer or instance of "\\Mautic\\LeadBundle\\Entity\\Lead"'
            );
        }

        return $this;
    }

    /**
     * @return int|Lead
     */
    public function getContact()
    {
        if (null !== $this->contact) {
            return $this->contact;
        }

        return $this->contactId;
    }

    /**
     * @param Lead $contact
     *
     * @return $this
     */
    public function setContact(Lead $contact)
    {
        $this->contact   = $contact;
        $this->contactId = $contact->getId();

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
     * @param \Mautic\CampaignBundle\Entity\Campaign|int $campaignId
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setCampaignId($campaignId)
    {
        if ($campaignId instanceof Campaign) {
            $this->setCampaign($campaignId);
        } elseif (\is_int($campaignId)) {
            $this->campaignId = $campaignId;
        } else {
            throw new \InvalidArgumentException(
                '$campaign must be an integer or instance of "\\Mautic\\CampaignBundle\\Entity\\Campaign"'
            );
        }

        return $this;
    }

    /**
     * @return int|Campaign
     */
    public function getCampaign()
    {
        if (null !== $this->campaign) {
            return $this->campaign;
        }

        return $this->campaignId;
    }

    /**
     * @param Campaign $campaign
     *
     * @return $this
     */
    public function setCampaign(Campaign $campaign = null)
    {
        if ($campaign) {
            $this->campaign   = $campaign;
            $this->campaignId = $campaign->getId();
        }

        return $this;
    }

    /**
     * @return string|null
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
     *
     * @throws \InvalidArgumentException
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
     * @return string|null
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
     * @return string|float|null
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param string|float|null $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return string|float|null
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * @param string|float|null $revenue
     *
     * @return $this
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;

        return $this;
    }
}
