<?php

namespace dokuwiki\plugin\wordimport\docx;

class TextRun
{
    protected $formatting = [
        'bold' => false,
        'italic' => false,
        'underline' => false,
        'strike' => false,
    ];

    protected $text = '';


    public function __construct(\SimpleXMLElement $tr)
    {
        $br = $tr->xpath('w:br');
        if (!empty($br)) {
            $this->text = "\n";
            return;
        }


        $this->parseFormatting($tr);
        $this->text = $tr->xpath('w:t')[0];
    }

    public function __toString()
    {
        $text = $this->text;

        if ($this->formatting['bold']) $text = '**' . $text . '**';
        if ($this->formatting['italic']) $text = '//' . $text . '//';
        if ($this->formatting['underline']) $text = '__' . $text . '__';
        if ($this->formatting['strike']) $text = '~~' . $text . '~~';

        return $text;
    }


    /**
     * @see http://www.datypic.com/sc/ooxml/e-w_rPr-4.html
     * @param \SimpleXMLElement $textRun
     */
    public function parseFormatting(\SimpleXMLElement $textRun)
    {
        $result = $textRun->xpath('w:rPr');
        if (empty($result)) return;

        foreach ($result[0]->children() as $child) {
            switch ($child->getName()) {
                case 'w:b':
                case 'w:bCs':
                    $this->formatting['bold'] = true;
                    break;
                case 'w:i':
                case 'w:iCs':
                case 'w:em':
                    $this->formatting['italic'] = true;
                    break;
                case 'w:u':
                    $this->formatting['underline'] = true;
                    break;
                case 'w:strike':
                case 'w:dstrike':
                    $this->formatting['strike'] = true;
                    break;
            }
        }
    }
}
