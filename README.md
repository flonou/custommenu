# Custom Menu

A GLPI plugin that allows administrators to add custom entries to the main GLPI menu. Each entry can have a name, URL, icon, position, and link target.

## Requirements

- GLPI 11.0.0 – 12.0.99
- PHP 8.1+

## Installation

1. Copy the `custommenu` folder into your GLPI `plugins/` directory.
2. Go to **Setup > Plugins** in GLPI.
3. Find **Custom Menu** in the list and click **Install**.
4. Click **Activate**.

## Usage

After activation, go to **Setup > General** and open the **Custom Menu** tab.

From there you can:

- **Add** a new menu entry
- **List** all entries (search engine view)
- **Edit** or **delete** existing entries

### Entry fields

| Field        | Description                                                        |
|--------------|--------------------------------------------------------------------|
| **Name**     | Label displayed in the menu (required).                            |
| **URL**      | Target URL – absolute (`https://…`) or relative (`/front/…`).      |
| **Icon**     | Tabler icon class (e.g. `ti ti-home`). Defaults to `ti ti-link`.   |
| **Position** | Where to place the entry (see below). Defaults to `end`.           |
| **Target**   | `Current tab` (default) or `New tab`.                              |
| **Active**   | Whether the entry is visible in the menu.                          |
| **Sort order**| Numeric weight used to order entries at the same position.         |

### Position format

The position field accepts the following values:

| Value            | Meaning                                          |
|------------------|--------------------------------------------------|
| `end`            | Append at the end of the menu (default).         |
| `after:<key>`    | Insert immediately after the menu item with the given key. |
| `before:<key>`   | Insert immediately before the menu item with the given key. |
| `5` (integer)    | Insert at the given 0-based index.               |

> **Tip:** To find a menu key, open the browser DevTools and inspect the menu item's `data-menu` attribute or the link's `href` fragment.

## Uninstallation

Go to **Setup > Plugins**, find **Custom Menu**, and click **Uninstall**. This removes the plugin's database table.

## License

GPL-3.0-or-later. See the [LICENSE](LICENSE) file.
