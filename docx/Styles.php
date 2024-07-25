<?php

namespace dokuwiki\plugin\wordimport\docx;

/**
 * The styles of a docx file
 *
 * Styles have an ID and a name. The XMLs always use the ID, but we want to work with the name.
 */
class Styles extends AbstractXMLFile
{
    /** @var array The mapping of style IDs to style names */
    protected $id2name = [];
    /** @var array The mapping of style names to style IDs */
    protected $name2id = [];

    /** @inheritdoc */
    protected function parse()
    {
        $xml = $this->docx->loadXMLFile('word/styles.xml');
        $this->registerNamespaces($xml);


        $styles = $xml->xpath('//w:style');
        foreach ($styles as $style) {
            $x = $style->asXML();

            $id = strtolower($style->attributes('w', true)->styleId);
            $name = strtolower($style->xpath('w:name')[0]->attributes('w', true)->val);
            $this->id2name[$id] = $name;
        }
        $this->name2id = array_flip($this->id2name);
    }


    /**
     * Check if the given element has one of the given style names
     *
     * @param \SimpleXMLElement $xml
     * @param string[] $names
     * @return bool
     */
    public function hasStyle(\SimpleXMLElement $xml, $names)
    {
        // get IDs for the given names
        $ids = array_filter(array_map(function ($name) {
            $name = strtolower($name);
            return $this->name2id[$name] ?? $name;
        }, $names));

        $style = $xml->xpath('w:pPr/w:pStyle');
        foreach ($style as $s) {
            $id = strtolower($s->attributes('w', true)->val);
            if (in_array($id, $ids)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the name of a style by its ID
     *
     * @param string $id
     * @return string
     */
    public function getStyleName(string $id)
    {
        return $this->id2name[$id] ?? $id;
    }
}
