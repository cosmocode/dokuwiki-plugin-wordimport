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

        // main arguments
        $options->registerArgument('docx', 'The .docx Word file', 'true');
        $options->registerArgument('page', 'The page ID to where the contents should be imported. Will be overwritten if exists.', 'true');
    }

    /** @inheritDoc */
    protected function main(Options $options)
    {
        auth_setup(); // we need this for ACL checks

        [$docx, $page] = $options->getArgs();
        $doc = new DocX($docx);
        $doc->import($page);
    }
}
