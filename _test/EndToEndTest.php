<?php

namespace dokuwiki\plugin\wordimport\test;

use dokuwiki\plugin\wordimport\docx\DocX;
use DokuWikiTest;

/**
 * end to end tests for the wordimport plugin
 *
 * @group plugin_wordimport
 * @group plugins
 */
class EndToEndTest extends DokuWikiTest
{

    /**
     * @return \Generator|array
     */
    public function provideEndToEnd()
    {
        $docs = glob(__DIR__ . '/EndToEnd/*.docx');
        foreach ($docs as $doc) {
            $txt = substr($doc, 0, -4) . 'txt';
            if (!file_exists($txt)) continue;

            yield [$doc, file_get_contents($txt)];
        }
    }

    /**
     * @dataProvider provideEndToEnd
     */
    public function testEndToEnd($file, $expected)
    {
        /** @var array $conf */
        include __DIR__ . '/../conf/default.php'; // load default config

        $docx = new DocX($file, $conf);
        $this->assertEquals(trim($expected), trim($docx->getDocument()));
    }
}
