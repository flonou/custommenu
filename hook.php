<?php

use DBConnection;
use GlpiPlugin\CustomMenu\CustomMenu;
use Migration;

/**
 * Install the Custom Menu plugin.
 *
 * @return bool
 */
function plugin_custommenu_install(): bool
{
    global $DB;

    $default_charset   = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();

    // Instantiate migration with the plugin version.
    $migration = new Migration(PLUGIN_CUSTOMMENU_VERSION);

    // Create the table only if it does not exist yet.
    $table = CustomMenu::getTable();
    if (!$DB->tableExists($table)) {
        $query = "CREATE TABLE `$table` (
                    `id`         INT(11) NOT NULL AUTO_INCREMENT,
                    `name`       VARCHAR(255) NOT NULL,
                    `url`        VARCHAR(255) NOT NULL,
                    `icon`       VARCHAR(100) DEFAULT NULL,
                    `position`   VARCHAR(50) NOT NULL DEFAULT 'end',
                    `target`     VARCHAR(20) NOT NULL DEFAULT '_self',
                    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
                    `sort_order` INT(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`)
                 ) ENGINE=InnoDB
                 DEFAULT CHARSET={$default_charset}
                 COLLATE={$default_collation}";
        $DB->doQuery($query);
    }

    // Execute the whole migration.
    $migration->executeMigration();

    return true;
}

/**
 * Uninstall the Custom Menu plugin.
 *
 * @return bool
 */
function plugin_custommenu_uninstall(): bool
{
    global $DB;

    $tables = [
        CustomMenu::getTable(),
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->doQuery("DROP TABLE `$table`");
        }
    }

    return true;
}

/**
 * Parse a position string into a structured description.
 *
 * Supported formats:
 *  - "after:<key>"   insert after the given menu key
 *  - "before:<key>"  insert before the given menu key
 *  - "<int>"         insert at the given 0-based index
 *  - "end"           append at the end (default)
 *
 * @param string $position
 *
 * @return array{type: string, reference: string|int|null}
 */
function plugin_custommenu_parse_position(string $position): array
{
    $position = trim($position);

    if (stripos($position, 'after:') === 0) {
        return [
            'type'      => 'after',
            'reference' => substr($position, 6),
        ];
    }

    if (stripos($position, 'before:') === 0) {
        return [
            'type'      => 'before',
            'reference' => substr($position, 7),
        ];
    }

    if (ctype_digit($position)) {
        return [
            'type'      => 'index',
            'reference' => (int) $position,
        ];
    }

    return [
        'type'      => 'end',
        'reference' => null,
    ];
}

/**
 * Insert a menu entry into the menu array at the given position.
 *
 * @param array  $menu     Current menu (keyed array).
 * @param string $new_key  Unique key for the new entry.
 * @param array  $entry    Entry definition.
 * @param string $position Position specification.
 *
 * @return array
 */
function plugin_custommenu_insert_at_position(
    array $menu,
    string $new_key,
    array $entry,
    string $position
): array {
    // Never overwrite an existing entry.
    if (isset($menu[$new_key])) {
        return $menu;
    }

    $parsed = plugin_custommenu_parse_position($position);

    switch ($parsed['type']) {
        case 'after':
            return plugin_custommenu_insert_relative($menu, $new_key, $entry, $parsed['reference'], 'after');

        case 'before':
            return plugin_custommenu_insert_relative($menu, $new_key, $entry, $parsed['reference'], 'before');

        case 'index':
            return plugin_custommenu_insert_at_index($menu, $new_key, $entry, $parsed['reference']);

        case 'end':
        default:
            $menu[$new_key] = $entry;
            return $menu;
    }
}

/**
 * Insert an entry relative to a reference key (before or after).
 *
 * @param array  $menu
 * @param string $new_key
 * @param array  $entry
 * @param string $reference
 * @param string $mode 'before' or 'after'
 *
 * @return array
 */
function plugin_custommenu_insert_relative(
    array $menu,
    string $new_key,
    array $entry,
    string $reference,
    string $mode
): array {
    $new_menu = [];
    $inserted = false;

    foreach ($menu as $key => $value) {
        if ($key === $reference && $mode === 'before' && !$inserted) {
            $new_menu[$new_key] = $entry;
            $inserted = true;
        }

        $new_menu[$key] = $value;

        if ($key === $reference && $mode === 'after' && !$inserted) {
            $new_menu[$new_key] = $entry;
            $inserted = true;
        }
    }

    // If the reference key was not found, append at the end.
    if (!$inserted) {
        $new_menu[$new_key] = $entry;
    }

    return $new_menu;
}

/**
 * Insert an entry at a specific 0-based index.
 *
 * @param array  $menu
 * @param string $new_key
 * @param array  $entry
 * @param int    $index
 *
 * @return array
 */
function plugin_custommenu_insert_at_index(
    array $menu,
    string $new_key,
    array $entry,
    int $index
): array {
    $keys   = array_keys($menu);
    $values = array_values($menu);

    $index = max(0, min($index, count($values)));

    array_splice($keys, $index, 0, $new_key);
    array_splice($values, $index, 0, $entry);

    return array_combine($keys, $values);
}

/**
 * Build a unique menu key from an entry id.
 *
 * @param int $id
 *
 * @return string
 */
function plugin_custommenu_build_key(int $id): string
{
    return 'custommenu_' . $id;
}

/**
 * Redefine the main GLPI menu with the custom entries.
 *
 * @param array $menu Existing GLPI menu definitions.
 *
 * @return array Updated menu definitions.
 */
function plugin_custommenu_redefine_menus(array $menu): array
{
    global $DB;

    $table = CustomMenu::getTable();

    // If the table does not exist yet (e.g. during install), return the menu unchanged.
    if (!$DB->tableExists($table)) {
        return $menu;
    }

    $iterator = $DB->request([
        'FROM'  => $table,
        'WHERE' => ['is_active' => 1],
        'ORDER' => ['sort_order ASC', 'id ASC'],
    ]);

    foreach ($iterator as $row) {
        $key = plugin_custommenu_build_key((int) $row['id']);

        $entry = [
            'title'   => $row['name'],
            'icon'    => $row['icon'] ?: CustomMenu::DEFAULT_ICON,
            'default' => $row['url'],
            'content' => [],
        ];

        // Add the target attribute when opening in a new tab.
        if ($row['target'] === CustomMenu::TARGET_BLANK) {
            $entry['target'] = '_blank';
        }

        $menu = plugin_custommenu_insert_at_position(
            $menu,
            $key,
            $entry,
            (string) $row['position']
        );
    }

    return $menu;
}
