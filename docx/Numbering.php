<?php

namespace dokuwiki\plugin\wordimport\docx;

/**
 * Numbering information
 */
class Numbering extends AbstractXMLFile
{
    /** @var array the numbering information numID -> level -> type */
    protected $numbering = [];

    /**
     * The files defines "abstract numbers" first. They define a set of rules for each indentation level of a list.
     * Then the actual numbers are defined. They reference one of the abstract numbers. List items reference one of the
     * actual numbers.
     *
     * @inheritdoc
     */
    protected function parse()
    {
        $xml = $this->docx->loadXMLFile($this->docx->getRelationships()->getTarget('numbering'));
        $this->registerNamespaces($xml);

        $types = [];
        foreach ($xml->xpath('//w:abstractNum') as $num) {
            $id = (int)$num->attributes('w', true)->abstractNumId;
            $types[$id] = [];

            foreach ($num->xpath('.//w:lvl') as $lvl) {
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
     * @param string $id
     * @param int $depth the depth of the list starting at 0
     * @return string 'ordered' or 'unordered'
     */
    public function getType($id, int $depth): string
    {
        return $this->numbering[$id][$depth] ?? 'unordered';
    }
}
