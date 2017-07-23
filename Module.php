<?php

namespace LodConnect;

use Omeka\Module\AbstractModule;
use Zend\EventManager\SharedEventManagerInterface;
use EasyRdf_Graph;
use EasyRdf_Resource;
use EasyRdf_Namespace;

class Module extends AbstractModule
{

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }
    
    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Api\Representation\ValueRepresentation',
            'rep.value.html',
            array($this, 'repValueHtml')
            );
    }
    
    public function repValueHtml($event)
    {
        $target = $event->getTarget();
        if ($target->type() == 'uri') {
            
            $uri = $target->uri();
            $dbpediaUri = str_replace('http://dbpedia.org/data', 'http://dbpedia.org/resource', $uri);
            $easyRdfTargetUri = new EasyRdf_Resource($dbpediaUri);
            $client = $this->getServiceLocator()->get('Omeka\HttpClient');
            $client->setUri($uri);
            $response = $client->send();
            $rdf = $response->getBody();
            //echo $rdf;
            //die();
            //$rdf = file_get_contents($uri);
            EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
            $graph = new EasyRdf_Graph();
            try {
                $triplesCount = $graph->parse($rdf);
            } catch (\InvalidArgumentException $e) {
                return;
            }
            if ($triplesCount !== 0) {
                // remember that this doesn't work, but the non-commented code
                // does work for setting $resource
                // refer to the chaos in MetadataBrowse
                // $resource = $graph->resource($uri);
                $resource = $graph->resource($easyRdfTargetUri);
                $translator = $this->getServiceLocator()->get('MvcTranslator');
                $html = "<p>" . $event->getParam('html') . ' ' . $translator->translate('(Full Data)') . " </p>";
                $property = 'dbo:abstract';
                $abstract = $resource->getLiteral($property, 'en');
                $html .= $abstract->getValue();
                $event->setParam('html', $html);
            }
        }
    }
}

