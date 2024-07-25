<?php

namespace dokuwiki\plugin\wordimport\docx;

class Numbering extends AbstractXMLFile
{
    protected $numbering = [];

    protected function parse()
    {
        $xml = $this->docx->loadXMLFile($this->docx->getRelationships()->getTarget('numbering'));
        $this->registerNamespaces($xml);

        $types = [];
        foreach ($xml->xpath('//w:abstractNum') as $num) {
            $id = (int)$num->attributes('w', true)->abstractNumId;
            $types[$id] = [];

            foreach($num->xpath('.//w:lvl') as $lvl) {
                $depth = (int)$lvl->attributes('w', true)->ilvl;
                $lvlType = (string)$lvl->xpath('.//w:numFmt')[0]->attributes('w', true)->val;
                $lvlType = ($lvlType === 'decimal') ? 'ordered' : 'unordered';

                $types[$id][$depth] = $lvlType;
            }
        }

        foreach ($xml->xpath('//w:num') as $num) {
            $id = (int)$num->attributes('w', true)->numId;
            $typeId = (int)$num->xpath('.//w:abstractNumId')[0]->attributes('w', true)->val;
            if (isset($types[$typeId])) {
                $this->numbering[$id] = $types[$typeId];
            }
        }
    }

    /**
     * Get the type of the numbering for the given ID and depth
     *
     * @param int $id
     * @param int $depth the depth of the list starting at 0
     * @return string 'ordered' or 'unordered'
     */
    public function getType($id, $depth)
    {
        return $this->numbering[$id][$depth] ?? 'unordered';
    }
}
