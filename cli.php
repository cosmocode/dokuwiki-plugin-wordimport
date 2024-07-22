<?php

use splitbrain\phpcli\Options;

/**
 * DokuWiki Plugin wordimport (CLI Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class cli_plugin_wordimport extends \dokuwiki\Extension\CLIPlugin
{
    /** @inheritDoc */
    protected function setup(Options $options)
    {
        $options->setHelp('FIXME: What does this CLI do?');

        // main arguments
        //$options->registerArgument('FIXME:argumentName', 'FIXME:argument description', 'FIXME:required? true|false');

        // options
        // $options->registerOption('FIXME:longOptionName', 'FIXME: helptext for option', 'FIXME: optional shortkey', 'FIXME:needs argument? true|false', 'FIXME:if applies only to subcommand: subcommandName');

        // sub-commands and their arguments
        // $options->registerCommand('FIXME:subcommandName', 'FIXME:subcommand description');
        // $options->registerArgument('FIXME:subcommandArgumentName', 'FIXME:subcommand-argument description', 'FIXME:required? true|false', 'FIXME:subcommandName');
    }

    /** @inheritDoc */
    protected function main(Options $options)
    {
        $doc = new \dokuwiki\plugin\wordimport\docx\Doc('sample.docx');

        $doc->parse();



    }
}
