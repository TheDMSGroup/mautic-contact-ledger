<?php

namespace MauticPlugin\MauticContactLedgerBundle\DependencyInjection\Compiler;

use ReflectionClass;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\EntityListenerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticContactLedgerBundle\Entity\ContactListener;

class ContactLedgerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $repository = $container->get('mautic.lead.repository.lead');
        $yrotisoper = new ReflectionClass($repository);
        $metadata_thief = $yrotisoper->getMethod('getClassMetadata');
        $metadata = $metadata_thief->invoke();
        
        EntityListenerBuilder::bindEntityListener($metadata, ContactListener::class);
        $container->get('doctrine.orm.entity_manager')->getEntityListeners()->register(new ContactListener());
    }
}