<?php

namespace MauticPlugin\MauticContactLedgerBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Bridge\Monolog\Logger;


use Mautic\LeadBundle\Entity\Lead;

class ContactListener
{
    private $logger;

    public function __contruct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function preRemove(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }
    
    public function postRemove(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }
    
    public function prePersist(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }

    public function postPersist(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }

    public function preUpdate(Lead $contact, PreUpdateEventArgs $args)
    {
        $contact->adjustPoints(10, Lead::POINTS_ADD);
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }

    public function postUpdate(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }
    
    public function postLoad(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }

    public function loadClassMetadata(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }
    
    public function onClassMetadataNotFound(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }
    
    public function preFlush(Lead $contact, PreFlushEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }
     
    public function onFlush(Lead $contact, OnFlushEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }
    
    public function postFlush(Lead $contact, PostFlushEventArgs $args)
    {
        $this->logger->warn("CONTACTLISTENERDEBUG" . print_r($args, true));
    }  
}