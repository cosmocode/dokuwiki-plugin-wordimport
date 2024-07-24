<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * DokuWiki Plugin wordimport (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Andreas Gohr <dokuwiki@cosmocode.de>
 */
class action_plugin_wordimport_menu extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'handleMenuAssembly');
    }

    /**
     * Event handler for MENU_ITEMS_ASSEMBLY
     *
     * @see https://www.dokuwiki.org/devel:events:MENU_ITEMS_ASSEMBLY
     * @param Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleMenuAssembly(Event $event, $param)
    {
        if($event->data['view'] != 'page') return;
        array_splice($event->data['items'], -1, 0, [new \dokuwiki\plugin\wordimport\MenuItem()]);
    }
}
