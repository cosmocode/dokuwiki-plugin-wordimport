<?php

namespace dokuwiki\plugin\wordimport\docx;

/**
 * A code block
 *
 * Word doesn't have a specific code block element. We treat monospace paragraphs as code blocks
 *
 * This is basically a paragraph without any formatting and with real line breaks
 */
class CodeBlock extends AbstractParagraph
{
    /** @var string The raw text of the code block */
    protected $text = '';

    /** @inheritdoc */
    public function parse()
    {
        $runs = $this->p->xpath('w:r');
        foreach ($runs as $run) {
            $tr = new TextRun($this->docx, $run, "\n"); // use real line breaks
            $this->text .= $tr->__toString();
        }
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        return '<code>' . "\n" . $this->text . "\n" . '</code>';
    }
}
