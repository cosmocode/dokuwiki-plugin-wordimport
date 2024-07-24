<?php

namespace dokuwiki\plugin\wordimport;

use dokuwiki\Menu\Item\AbstractItem;

/**
 * Class MenuItem
 *
 * Implements the PDF export button for DokuWiki's menu system
 *
 * @package dokuwiki\plugin\dw2pdf
 */
class MenuItem extends AbstractItem {

    /** @var string do action for this plugin */
    protected $type = 'wordimport';

    /** @var string icon file */
    protected $svg = __DIR__ . '/wordimport.svg';

    /**
     * Get label from plugin language file
     *
     * @return string
     */
    public function getLabel() {
        $hlp = plugin_load('action', 'wordimport_menu');
        return $hlp->getLang('page_button');
    }
}
