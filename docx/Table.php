<?php

namespace dokuwiki\plugin\wordimport\docx;

class Table extends AbstractParagraph
{
    /** @var Paragraph[][] */
    protected $table = [];

    public function parse()
    {
        $rows = $this->p->xpath('w:tr');
        foreach ($rows as $row) {
            $tableRow = [];

            $cells = $row->xpath('w:tc');
            foreach ($cells as $cell) {
                $p = new Paragraph($this->docx, $cell->xpath('w:p')[0]);
                $p->parse();
                $tableRow[] = $p;
            }

            $this->table[] = $tableRow;
        }
    }

    public function __toString(): string
    {
        $text = '';
        foreach ($this->table as $row) {
            $text .= '|';
            foreach ($row as $cell) {
                $string = $cell->alignmentPadding($cell->__toString());
                $text .= " $string |";
            }
            $text .= "\n";
        }
        return $text;
    }
}
