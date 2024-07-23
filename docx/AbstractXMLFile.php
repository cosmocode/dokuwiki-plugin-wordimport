<?php

namespace dokuwiki\plugin\wordimport\docx;

abstract class AbstractXMLFile
{
    protected $docx;

    /**
     * @param DocX $docx The DocX object to work on
     */
    public function __construct(DocX $docx) {
        $this->docx = $docx;
        $this->parse();
    }

    /**
     * Parse the XML file
     */
    abstract protected function parse();

    /**
     * Register all namespaces in the XML file for XPath queries
     *
     * @param \SimpleXMLElement $xml
     */
    protected function registerNamespaces($xml)
    {
        $namespaces = $xml->getDocNamespaces(true);
        foreach ($namespaces as $prefix => $namespace) {
            $xml->registerXPathNamespace($prefix, $namespace);
        }
    }

}
