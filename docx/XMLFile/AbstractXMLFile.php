<?php

namespace dokuwiki\plugin\wordimport\docx\XMLFile;

use dokuwiki\plugin\wordimport\docx\DocX;

/**
 * Word Documents are ZIP files containing a bunch of XML files
 *
 * This class is the base for all classes that parse these XML files
 */
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
}
