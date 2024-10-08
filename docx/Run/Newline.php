<?php

namespace dokuwiki\plugin\wordimport\docx\Run;

use dokuwiki\plugin\wordimport\docx\Paragraph\CodeBlock;
use dokuwiki\plugin\wordimport\docx\Paragraph\Heading;

class Newline extends AbstractRun
{

    public function parse()
    {
        // nothing to do
    }

    public function __toString(): string
    {
        // In code blocks return a simple newline
        if ($this->parent instanceof CodeBlock) {
            return "\n";
        }
        if ($this->parent instanceof Heading) {
            return " "; // no newlines in headings
        }
        // otherwise force a break
        return '\\\\ ';
    }
}
