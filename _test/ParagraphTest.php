<?php

namespace dokuwiki\plugin\wordimport\test;

use dokuwiki\plugin\wordimport\docx\Paragraph;
use DokuWikiTest;

/**
 * Paragraph tests for the wordimport plugin
 *
 * @group plugin_wordimport
 * @group plugins
 */
class ParagraphTest extends DokuWikiTest
{
    public function testWrapFormatting()
    {
        $p = new Paragraph(new \SimpleXMLElement('<foo xmlns:w="http://example.com"></foo>'));

        $this->assertEquals('text', $p->wrapFormatting('text', []));
        $this->assertEquals('**text**', $p->wrapFormatting('text', ['bold' => true]));
        $this->assertEquals('**text foo** ', $p->wrapFormatting('text foo ', ['bold' => true]));
        $this->assertEquals(' **text foo** ', $p->wrapFormatting(' text foo ', ['bold' => true]));
        $this->assertEquals('  ', $p->wrapFormatting('  ', ['bold' => true]));

        $this->assertEquals("\n\n **text\n \n foo**\n   \n", $p->wrapFormatting("\n\n text\n \n foo\n   \n", ['bold' => true]));
    }
}
