<?php

namespace dokuwiki\plugin\wordimport\docx;

class Numbering extends AbstractXMLFile
{
    protected $numbering = [];

    protected function parse()
    {
        $xml = $this->docx->loadFile('word/numbering.xml');
        $this->registerNamespaces($xml);

        $types = [];

        foreach ($xml->xpath('//w:abstractNum') as $num) {
            $id = (int)$num->attributes('w', true)->abstractNumId;
            $format = (string)$num->xpath('.//w:numFmt')[0]->attributes('w', true)->val;
            $format = ($format === 'decimal') ? 'ordered' : 'unordered';
            $types[$id] = $format;
        }

        foreach ($xml->xpath('//w:num') as $num) {
            $id = (int)$num->attributes('w', true)->numId;
            $typeId = (int)$num->xpath('.//w:abstractNumId')[0]->attributes('w', true)->val;
            if (isset($types[$typeId])) {
                $this->numbering[$id] = $types[$typeId];
            } else {
                $this->numbering[$id] = 'unordered';
            }
        }
    }

    /**
     * Get the type of the numbering for the given ID
     *
     * @param int $id
     * @return string 'ordered' or 'unordered'
     */
    public function getType($id)
    {
        return $this->numbering[$id] ?? 'unordered';
    }
}
