<?php

namespace dokuwiki\plugin\wordimport\docx\Paragraph;

use dokuwiki\plugin\wordimport\docx\DocX;

/**
 * Paragraphs are the basic building blocks of a Word document
 */
abstract class AbstractParagraph
{
    /** @var DocX The main object holding everything together */
    protected $docx;
    /** @var \SimpleXMLElement The paragraph object */
    protected $p;

    /**
     * @param DocX $docx The main docx object for accessing shared data
     * @param \SimpleXMLElement $p The paragraph XML element
     */
    public function __construct(DocX $docx, \SimpleXMLElement $p)
    {
        $this->docx = $docx;
        $this->p = $p;
    }

    /**
     * Parse the paragraph XML element
     */
    abstract public function parse();

    /**
     * @return string The DokuWiki syntax representation of the paragraph
     */
    abstract public function __toString(): string;

    /**
     * Allows to merge this paragraph with the previous one if that was of the same type
     *
     * When false is returned no joining is done and the paragraphs are joined with two
     * newlines. Otherwise an array with two integers is expected. The first one is the number
     * of bytes to remove from the previous paragraph (eg. the closing syntax) and the second
     * one is the number of bytes to remove from this paragraph (eg. the opening syntax).
     *
     * @return bool|int[]
     */
    public function mergeToPrevious()
    {
        return false;
    }
}
