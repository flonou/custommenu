<?php

define('PLUGIN_CUSTOMMENU_VERSION', '1.0.0');
define('PLUGIN_CUSTOMMENU_MIN_GLPI', '11.0.0');
define('PLUGIN_CUSTOMMENU_MAX_GLPI', '12.0.99');


/**
 * Register plugin hooks.
 */
function plugin_init_custommenu()
{
    global $PLUGIN_HOOKS;

//    $PLUGIN_HOOKS[Hooks::REDEFINE_MENUS]['custom_menu'] = 'plugin_custom_menu_redefine_menus';
    $PLUGIN_HOOKS['redefine_menus']['custommenu'] = 'plugin_custommenu_redefine_menus';
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

