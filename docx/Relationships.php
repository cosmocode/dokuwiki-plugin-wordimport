<?php

namespace dokuwiki\plugin\wordimport\docx;

class Relationships extends AbstractXMLFile
{
    /**
     * @var array The relationships type -> id -> target
     */
    protected $relationships = [];

    /** @inheritdoc */
    protected function parse()
    {
        $xml = $this->docx->loadXMLFile('word/_rels/document.xml.rels');
        $this->registerNamespaces($xml);

        foreach ($xml->xpath('//default:Relationship') as $rel) {
            $id = (string)$rel->attributes()->Id;
            $type = basename((string)$rel->attributes()->Type);
            $target = 'word/' . $rel->attributes()->Target;

            if (!isset($this->relationships[$type])) {
                $this->relationships[$type] = [];
            }
            $this->relationships[$type][$id] = $target;
        }
    }

    /**
     * Get the target for the given type and ID
     *
     * @param string $type
     * @param string $id
     * @return string The target path relative to the docx file's root (eg. with a word/ prefix)
     * @throws \Exception
     */
    public function getTarget($type, $id = null): ?string
    {
        if ($id === null) {
            $id = array_keys($this->relationships[$type] ?? [])[0] ?? null;
            if ($id === null) throw new \Exception('No relationship found for type ' . $type);
        }

        if (!isset($this->relationships[$type][$id])) {
            throw new \Exception('No relationship found for type ' . $type . ' and id ' . $id);
        }

        return $this->relationships[$type][$id];
    }
}
