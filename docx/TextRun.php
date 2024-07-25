<?php

namespace dokuwiki\plugin\wordimport\docx;

class TextRun // this is not a paragraph!
{
    /**
     * @var int[] The formatting of this run and the length of the formatting chain. A value of 0 means
     *             the formatting is not present, a value of 1 means it is present only in this run. A value
     *             of 2 or higher means it is present in this run and the n-1 following runs.
     */
    protected $formatting = [
        'bold' => 0,
        'italic' => 0,
        'underline' => 0,
        'strike' => 0,
        'mono' => 0,
    ];


    protected $text = '';

    /**
     * @param \SimpleXMLElement $tr
     * @param string $newline The code for newlines
     */
    public function __construct(\SimpleXMLElement $tr, $newline = '\\\\ ')
    {
        $br = $tr->xpath('w:br');
        if (!empty($br)) {
            $this->text = $newline;
            return;
        }

        $this->parseFormatting($tr);
        $this->text = (string)($tr->xpath('w:t')[0] ?? '');
    }

    public function __toString()
    {
        return $this->text;
    }

    /**
     * A list of set formattings on this run
     *
     * @return int[]
     */
    public function getFormatting()
    {
        return $this->formatting;
    }

    public function isWhiteSpace()
    {
        return ctype_space($this->text);
    }

    /**
     * @see http://www.datypic.com/sc/ooxml/e-w_rPr-4.html
     * @param \SimpleXMLElement $textRun
     */
    public function parseFormatting(\SimpleXMLElement $textRun)
    {
        $result = $textRun->xpath('w:rPr');
        if (empty($result)) return;

        foreach ($result[0]->children('w', true) as $child) {
            switch ($child->getName()) {
                case 'b':
                case 'bCs':
                    $this->formatting['bold'] = 1;
                    break;
                case 'i':
                case 'iCs':
                case 'em':
                    $this->formatting['italic'] = 1;
                    break;
                case 'u':
                    $this->formatting['underline'] = 1;
                    break;
                case 'strike':
                case 'dstrike':
                    $this->formatting['strike'] = 1;
                    break;
                case 'rFonts':
                    if (in_array($child->attributes('w', true)->ascii, ['Courier New', 'Consolas'])) { // fixme make configurable
                        $this->formatting['mono'] = 1;
                    }
                    break;
            }
        }
    }

    /**
     * Use the formatting of the following run to update the scores of this one
     *
     * This is used to find the longest chains of formatting
     *
     * @param TextRun $nextRun
     * @return void
     */
public function updateFormattingScores(TextRun $nextRun)
{
    $next = $nextRun->getFormatting();
    foreach ($next as $key => $value) {
        if($this->formatting[$key] === 0) continue;
        $this->formatting[$key] += $value;
    }

    // sort by value, longest chains first
    arsort($this->formatting);
}

}
