<?php

namespace dokuwiki\plugin\wordimport\docx;

abstract class AbstractParagraph {

    protected $docx;
    protected $p;

    /**
     * @param DocX $docx The main docx object for accessing shared data
     * @param \SimpleXMLElement $p The paragraph XML element
     */
    public function __construct(DocX $docx, \SimpleXMLElement $p) {
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
     * This means instead of adding two lines only one is added between the two paragraphs. Used
     * for example for list items.
     *
     * @return bool
     */
    public function mergeToPrevious(): bool {
        return false;
    }
}
