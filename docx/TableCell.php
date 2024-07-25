<?php

namespace dokuwiki\plugin\wordimport\docx;

/**
 * A table cell
 *
 * A table cell is not really a paragraph but we treat it as one for simplicity. However it contains paragraphs.
 */
class TableCell extends AbstractParagraph
{
    /** @var bool Is this a placeholder for a vertically merged cell? It has no content then */
    protected $vmerge = false;
    /** @var int The horizontal span of this cell */
    protected $span = 1;
    /** @var Paragraph[] All contained paragraphs */
    protected $paragraphs = [];

    /** @inheritdoc  */
    public function parse()
    {
        $this->p->asXML();

        // vertical merge
        $vMerge = $this->p->xpath('w:tcPr/w:vMerge');
        $this->vmerge = $vMerge && ((string)$vMerge[0]->attributes('w', true)->val !== 'restart');

        // horizontal span
        $span = $this->p->xpath('w:tcPr/w:gridSpan');
        $this->span = $span ? (int)$span[0]->attributes('w', true)->val : 1;

        $paragraphs = $this->p->xpath('w:p');
        foreach ($paragraphs as $paragraph) {
            // FIXME theoretically we would need to use the Document's factory again
            // because a table might contain images, OTOH a table may not contain headings or lists
            $p = new Paragraph($this->docx, $paragraph);
            $p->parse();
            $this->paragraphs[] = $p;
        }
    }

    /**
     * Outputs the cell with closing pipes
     *
     * The opening pipe is create by the Table class for each row.
     *
     * @inheritdoc
     */
    public function __toString(): string
    {
        if ($this->vmerge) {
            $string = ":::";
        } else {
            $string = implode('\\\\ ', array_map(static fn($p) => $p->__toString(), $this->paragraphs));
        }

        if ($this->paragraphs) {
            $string = $this->paragraphs[0]->alignmentPadding($string);
        }

        $string = " $string "; // add one space for nicer layout
        $string .= str_repeat('|', $this->span);
        return $string;
    }
}
