<?php

use Glpi\Plugin\Hooks;

/**
 * Install the Custom Menu plugin.
 */
function plugin_custommenu_install(): bool
{
    return true;
}

/**
 * Uninstall the Custom Menu plugin.
 */
function plugin_custommenu_uninstall(): bool
{
    return true;
}

/**
 * Insert a root menu entry after a specified menu key.
 *
 * @param array  $menu        Existing GLPI menu.
 * @param string $place_after Key of the menu entry to insert after.
 * @param string $new_key     Unique key for the new menu entry.
 * @param array  $entry       Definition of the new menu entry.
 *
 * @return array
 */
function plugin_custommenu_insert_after(
    array $menu,
    string $place_after,
    string $new_key,
    array $entry
): array {
    // Do not overwrite an existing menu entry.
    if (isset($menu[$new_key])) {
        return $menu;
    }

    $new_menu = [];
    $inserted = false;

    foreach ($menu as $key => $value) {
        $new_menu[$key] = $value;

        if ($key === $place_after) {
            $new_menu[$new_key] = $entry;
            $inserted = true;
        }
    }

    // Leave the menu unchanged if the anchor wasn't found.
    return $inserted ? $new_menu : $menu;
}

/**
 * Add Base root menu entries.
 *
 * @param array $menus Existing GLPI menu definitions.
 *
 * @return array Updated menu definitions.
 */
function plugin_custommenu_redefine_menus(array $menu): array
{
    $menu = plugin_custommenu_insert_after(
      $menu,
      'assets',
      'reservation',
      [
        'title'   =>  __('Reservation', 'reservation'),
        'icon'    => 'ti ti-calendar',
        'default' => '/plugins/reservation/front/menu.php',
        'content' => [],
      ]
    );
    $menu = plugin_custommenu_insert_after(
      $menu,
      'reservation',
      'wiki',
      [
        'title'   => __('Wiki'),
        'icon'    => 'ti ti-book',
        'default' => '/front/knowbaseitem.php',
        'content' => [],
      ]
    );
    return $menu;
}
