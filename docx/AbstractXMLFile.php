<?php

namespace dokuwiki\plugin\wordimport\docx;

abstract class AbstractXMLFile
{
    protected $docx;

    /**
     * @param DocX $docx The DocX object to work on
     */
    public function __construct(DocX $docx)
    {
        $this->docx = $docx;
        $this->parse();
    }

    /**
     * Parse the XML file
     */
    abstract protected function parse();

    /**
     * Register all namespaces that we access in XPath queries
     *
     * @param \SimpleXMLElement $xml
     */
    protected function registerNamespaces($xml)
    {
        $namespaces = [
            'rs' => 'http://schemas.openxmlformats.org/package/2006/relationships',
            'w' => 'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'wp' => 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing',
            'a' => 'http://schemas.openxmlformats.org/drawingml/2006/main',
        ];

        foreach ($namespaces as $prefix => $namespace) {
            $xml->registerXPathNamespace($prefix, $namespace);
        }
    }
}
