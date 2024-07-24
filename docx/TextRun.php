<?php

namespace dokuwiki\plugin\wordimport\docx;

class TextRun  // this is not a paragraph!
{
    protected $formatting = [
        'bold' => false,
        'italic' => false,
        'underline' => false,
        'strike' => false,
        'mono' => false,
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
        $this->text = (string) ($tr->xpath('w:t')[0] ?? '');
    }

    public function __toString()
    {
        return $this->text;
    }

    /**
     * A list of set formattings on this run
     *
     * @return string[]
     */
    public function getFormatting()
    {
        return array_keys(array_filter($this->formatting));
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
                    $this->formatting['bold'] = true;
                    break;
                case 'i':
                case 'iCs':
                case 'em':
                    $this->formatting['italic'] = true;
                    break;
                case 'u':
                    $this->formatting['underline'] = true;
                    break;
                case 'strike':
                case 'dstrike':
                    $this->formatting['strike'] = true;
                    break;
                case 'rFonts':
                    if (in_array($child->attributes('w', true)->ascii, ['Courier New', 'Consolas'])) { // fixme make configurable
                        $this->formatting['mono'] = true;
                    }
                    break;
            }
        }
    }
}
