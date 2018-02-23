<?php

namespace MauticPlugin\MauticContactLedgerBundle\Helper;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

class ContactListener
{
    public function __contruct()
    {
        
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        
    }
    
    public function postRemove(LifecycleEventArgs $args)
    {
        
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        
    }
    
    public function postLoad(LifecycleEventArgs $args)
    {
        
    }

    public function loadClassMetadata(LifecycleEventArgs $args)
    {
        
    }
    
    public function onClassMetadataNotFound(LifecycleEventArgs $args)
    {
        
    }
    
    public function preFlush(PreFlushEventArgs $args)
    {
        
    }
     
    public function onFlush(OnFlushEventArgs $args)
    {
        
    }
    
    public function postFlush(PostFlushEventArgs $args)
    {
        
    }  
}