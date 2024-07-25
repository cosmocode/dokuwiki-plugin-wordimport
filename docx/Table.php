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
                $cell = new TableCell($this->docx, $cell);
                $cell->parse();
                $tableRow[] = $cell;
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
                $text .= $cell->__toString();

            }
            $text .= "\n";
        }
        return $text;
    }


}
