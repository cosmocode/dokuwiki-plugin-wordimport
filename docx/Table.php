<?php

namespace dokuwiki\plugin\wordimport\docx;

/**
 * A table
 *
 * A table is not really a paragraph but we treat it as one for simplicity. However it contains rows of cells which
 * again contain paragraphs.
 */
class Table extends AbstractParagraph
{
    /** @var Paragraph[][] */
    protected $table = [];

    /** @inheritdoc */
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

    /** @inheritdoc */
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
