<?php

namespace dokuwiki\plugin\wordimport\docx;

abstract class AbstractParagraph {

    protected $docx;

    public function __construct(DocX $docx) {
        $this->docx = $docx;
    }

    /**
     * @param \SimpleXMLElement $p The paragraph XML element
     */
    abstract public function parse(\SimpleXMLElement $p);

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
