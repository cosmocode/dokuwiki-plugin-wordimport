<?php

use dokuwiki\Extension\CLIPlugin;
use dokuwiki\plugin\wordimport\docx\DocX;
use splitbrain\phpcli\Options;

/**
 * DokuWiki Plugin wordimport (CLI Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class cli_plugin_wordimport extends CLIPlugin
{
    /** @inheritDoc */
    protected function setup(Options $options)
    {
        $options->setHelp('Import Microsoft Word documents into DokuWiki');

        $options->registerCommand('import', 'Import a single Word document');

        // main arguments
        $options->registerArgument('docx', 'The .docx Word file', 'true', 'import');
        $options->registerArgument(
            'page',
            'The page ID to where the contents should be imported. Will be overwritten if exists.',
            'true',
            'import'
        );
    }

    /** @inheritDoc */
    protected function main(Options $options)
    {
        $this->loadConfig();
        $args = $options->getArgs();

        switch ($options->getCmd()) {
            case 'import':
                return $this->import($args[0], $args[1]);
            default:
                echo $options->help();
                return 1;
        }

    }

    protected function import($docx, $page)
    {
        $doc = new DocX($docx, $this->conf);
        $doc->import($page);
        return 0;
    }
}
