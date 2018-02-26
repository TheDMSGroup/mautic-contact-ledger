<?php

namespace MauticPlugin\MauticContactLedgerBundle\DependencyInjection\Compiler;

use ReflectionClass;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\EntityListenerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\Entity\ContactListener;

class ContactListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $metadata = new ClassMetadata(\Mautic\LeadBundle\Entity\Lead::class);
        Lead::loadMetadata($metadata);
    
        EntityListenerBuilder::bindEntityListener($metadata, ContactListener::class);        
    }
}