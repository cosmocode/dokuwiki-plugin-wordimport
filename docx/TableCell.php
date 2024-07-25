<?php

namespace dokuwiki\plugin\wordimport\docx;

class TableCell extends AbstractParagraph
{

    protected $vmerge = false;
    protected $span = 1;
    /** @var Paragraph[] */
    protected $paragraphs = [];

    public function parse()
    {

        $x = $this->p->asXML();

        // vertical merge
        $vMerge = $this->p->xpath('w:tcPr/w:vMerge');
        $this->vmerge = $vMerge && ((string)$vMerge[0]->attributes('w', true)->val !== 'restart');

        // horizontal span
        $span = $this->p->xpath('w:tcPr/w:gridSpan');
        $this->span = $span ? (int)$span[0]->attributes('w', true)->val : 1;

        $paragraphs = $this->p->xpath('w:p');
        foreach ($paragraphs as $paragraph) {
            $p = new Paragraph($this->docx, $paragraph);
            $p->parse();
            $this->paragraphs[] = $p;
        }
    }

    /**
     * Outputs the cell with closing pipes
     *
     * @inheritdoc
     */
    public function __toString(): string
    {
        if ($this->vmerge) {
            $string = ":::";
        } else {
            $string = join('\\\\ ', array_map(function ($p) {
                return $p->__toString();
            }, $this->paragraphs));
        }

        if ($this->paragraphs) {
            $string = $this->paragraphs[0]->alignmentPadding($string);
        }

        $string = " $string "; // add one space for nicer layout
        $string .= str_repeat('|', $this->span);
        return $string;
    }

}
