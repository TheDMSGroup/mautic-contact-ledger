<?php

namespace MauticPlugin\MauticContactLedgerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use MauticPlugin\MauticContactLedgerBundle\DependencyInjection\Compiler\ContactListenerPass;

class MauticContactLedgerBundle extends PluginBundleBase
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container->addCompilerPass(new ContactListenerPass());
    }    
}