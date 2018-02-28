<?php

namespace MauticPlugin\MauticContactLedgerBundle\EventListener;

use Symfony\Bridge\Monolog\Logger;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Event\LeadEvent;

/**
 * Class LeadSubscriber extends {@see \Mautic\CoreBundle\EventListener\CommonSubscriber}
 *
 * @package \MauticPlugin\\\MauticContactLedgerBundle\EventListener
 * 
 */
class LeadSubscriber extends CommonSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_UTMTAGS_ADD => ['generic', 0],
            LeadEvents::LEAD_IDENTIFIED  => ['generic', 0],
            LeadEvents::CURRENT_LEAD_CHANGED  => ['generic', 0],
            LeadEvents::LEAD_PRE_SAVE => ['prePersist', 1],
            LeadEvents::LEAD_PRE_MERGE  => ['generic', 0],
            LeadEvents::LEAD_PRE_DELETE  => ['generic', 0],
            LeadEvents::LEAD_POST_SAVE  => ['generic', 0],
            LeadEvents::LEAD_POST_MERGE  => ['generic', 0],
            LeadEvents::LEAD_POST_DELETE  => ['generic', 0],
        ];
    }
    
    /**
     * Listener to catch events just before persisting changes
     *
     * @param \Mautic\LeadBundle\Event\LeadEvent $event
     */
    public function prePersist(LeadEvent $event)
    {
        $this->logger->warning("DEBUG::CONTACTLISTENER " . $event->getName());

        $this->logger->warning("DEBUG::CONTACTLISTENER " . print_r($event->getChanges()  , true));
    }

    /**
     * Listener to catch events just before persisting changes
     *
     * @param \Mautic\LeadBundle\Event\LeadEvent $event
     */
    public function generic(LeadEvent $event)
    {
        $this->logger->warning("DEBUG::CONTACTLISTENER " . $event->getName());
       
        $this->logger->warning("DEBUG::CONTACTLISTENER " . print_r($event->getChanges()  , true));
    }
/*
    public function postPersist(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
    
    public function preRemove(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
    
    public function postRemove(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
    
    public function loadClassMetadata(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }

    public function onClassMetadataNotFound(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
        
    public function postLoad(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }

    public function preUpdate(Lead $contact, PreUpdateEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }

    public function postUpdate(Lead $contact, LifecycleEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
    
    public function preFlush(Lead $contact, PreFlushEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
     
    public function onFlush(Lead $contact, OnFlushEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
    
    public function postFlush(Lead $contact, PostFlushEventArgs $args)
    {
        $this->logger->warn("DEBUG::CONTACTLISTENER " . $event->getName());
    }
*/
}