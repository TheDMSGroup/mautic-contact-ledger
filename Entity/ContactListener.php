<?php

namespace MauticPlugin\MauticContactLedgerBundle\Helper;

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
        
    }
    
    public function postRemove(Lead $contact, LifecycleEventArgs $args)
    {
        
    }
    
    public function prePersist(Lead $contact, LifecycleEventArgs $args)
    {
        
    }

    public function postPersist(Lead $contact, LifecycleEventArgs $args)
    {
        
    }

    public function preUpdate(Lead $contact, PreUpdateEventArgs $args)
    {
        
    }

    public function postUpdate(Lead $contact, LifecycleEventArgs $args)
    {
        
    }
    
    public function postLoad(Lead $contact, LifecycleEventArgs $args)
    {
        
    }

    public function loadClassMetadata(Lead $contact, LifecycleEventArgs $args)
    {
        
    }
    
    public function onClassMetadataNotFound(Lead $contact, LifecycleEventArgs $args)
    {
        
    }
    
    public function preFlush(Lead $contact, PreFlushEventArgs $args)
    {
        
    }
     
    public function onFlush(Lead $contact, OnFlushEventArgs $args)
    {
        
    }
    
    public function postFlush(Lead $contact, PostFlushEventArgs $args)
    {
        
    }  
}