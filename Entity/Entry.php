<?php

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Type;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead;

class Entry
{
    /**
     * @var int primary key
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateAdded;
    /**
     * @var Lead
     */
    private $contact;

    /**
     * @var string
     */
    private $bundle;

    /**
     * @var string
     */
    private $object;

    private $objectId;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $memo;

    /**
     * @var string  decimal(19,4)
     */
    private $credit;

    /**
     * @var string represents decimal(19,4)
     */
    private $debit;

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('contact_ledger')
            ->setCustomRepositoryClass('MauticPlugin\MauticContactLedgerBundle\Entity\EntryRepository');

        $builder->addId();
        $builder->addDateAdded();
        $builder->addContact();

        $builder->createField('bundle', 'string')
            ->length(50)
            ->build();

        $builder->createField('object', 'string')
            ->length(50)
            ->build();

        $builder->createField('objectId', 'integer')
            ->columnName('object_id')
            ->build();

        $builder->createField('action', 'string')
            ->length(50)
            ->build();

        $builder->createField('memo', 'string')
            ->length(255)
            ->nullable()
            ->build();

        $builder->createField('credit', Type::DECIMAL)
            ->precision(19)
            ->scale(4)
            ->nullable()
            ->build();

        $builder->createField('debit', Type::DECIMAL)
            ->precision(19)
            ->scale(4)
            ->nullable()
            ->build();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function setContact(Lead $contact)
    {
        $this->contact = $contact;
        return $this;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
        return $this;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObjact($object)
    {
        $this->object = $object;
        return $this;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function setObjactId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function getMemo()
    {
        return $this->memo;
    }

    public function setMemo($memo)
    {
        $this->memo = $memo;
        return $this;
    }

    public function getCredit()
    {
        return $this->credit;
    }

    public function setCredit($credit)
    {
        $this->credit = $credit;
        return $this;
    }

    public function getDebit()
    {
        return $this->debit;
    }

    public function setDebit($debit)
    {
        $this->debit = $debit;
        return $this;
    }
}
