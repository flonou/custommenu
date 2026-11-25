<?php

define('PLUGIN_CUSTOMMENU_VERSION', '2.0.0');
define('PLUGIN_CUSTOMMENU_MIN_GLPI', '11.0.0');
define('PLUGIN_CUSTOMMENU_MAX_GLPI', '12.0.99');

use Glpi\Plugin\Hooks;
use GlpiPlugin\CustomMenu\CustomMenu;
use GlpiPlugin\CustomMenu\CustomMenuProfile;

require_once __DIR__ . '/src/Config.php';
require_once __DIR__ . '/src/CustomMenu.php';
require_once __DIR__ . '/src/CustomMenuProfile.php';

/**
 * Register plugin hooks.
 */
function plugin_init_custommenu()
{
    global $PLUGIN_HOOKS;

    // Configuration page shown by the "Configure" button on the plugin entry.
    $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['custommenu'] = 'front/custommenu.php';

    // Redefine the main GLPI menu with the custom entries.
    $PLUGIN_HOOKS[Hooks::REDEFINE_MENUS]['custommenu'] = 'plugin_custommenu_redefine_menus';

    // Register plugin classes
    Plugin::registerClass(CustomMenu::class);
    Plugin::registerClass(CustomMenuProfile::class, [
        'addtabon' => Profile::class
    ]);
}


/**
 * Plugin metadata.
 */
function plugin_version_custommenu()
{
    return [
        'name'           => 'Custom GLPI Menu Entries',
        'version'        => PLUGIN_CUSTOMMENU_VERSION,
        'author'         => 'Florian Nouviale',
        'license'        => 'GPL-3.0-or-later',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_CUSTOMMENU_MIN_GLPI,
                'max' => PLUGIN_CUSTOMMENU_MAX_GLPI,
            ],
        ],
    ];
}

