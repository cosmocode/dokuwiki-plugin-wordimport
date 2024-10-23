<?php

namespace dokuwiki\plugin\wordimport\docx\Paragraph;

use dokuwiki\plugin\wordimport\docx\Run\Text;

/**
 * A code block
 *
 * Word doesn't have a specific code block element. We treat monospace paragraphs as code blocks
 *
 * This is basically a paragraph but can only contain text and newlines
 */
class CodeBlock extends Paragraph
{
    /** @var string The raw text of the code block */
    protected $text = '';

    /** @inheritdoc */
    public function mergeToPrevious()
    {
        // '</code>\n\n', '<code>\n'
        return [9, 7];
    }

    /** @inheritdoc */
    public function parse()
    {
        $wrs = $this->p->xpath('w:r');
        foreach ($wrs as $wr) {
            $run = $this->docx->createRun($wr, $this);
            if (!$run instanceof Text) continue;
            $run->parse();
            $this->text .= $run->__toString();
        }
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        return '<code>' . "\n" . $this->text . "\n" . '</code>';
    }
}
