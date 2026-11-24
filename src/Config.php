<?php

namespace GlpiPlugin\CustomMenu;

use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Session;

/**
 * Configuration tab for the Custom Menu plugin.
 *
 * Adds a "Custom Menu" tab on the Setup > General (Config) page where
 * administrators can manage the custom menu entries.
 *
 * @since 2.0.0
 */
class Config extends \Config
{
    /**
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('Custom Menu', 'custommenu');
    }

    /**
     * Tab name on the Config itemtype.
     *
     * @param CommonGLPI $item
     * @param int        $withtemplate
     *
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof \Config) {
            return self::createTabEntry(self::getTypeName());
        }

        return '';
    }

    /**
     * Display the tab content.
     *
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof \Config) {
            return self::showForConfig($item, $withtemplate);
        }

        return true;
    }

    /**
     * Render the configuration page.
     *
     * @param \Config $config
     * @param int     $withtemplate
     *
     * @return bool
     */
    public static function showForConfig(\Config $config, $withtemplate = 0)
    {
        global $DB;

        if (!Session::haveRight('config', UPDATE)) {
            Html::displayRightError([]);
            return false;
        }

        $entries = [];
        $table   = CustomMenu::getTable();

        if ($DB->tableExists($table)) {
            $iterator = $DB->request([
                'FROM'  => $table,
                'ORDER' => ['sort_order ASC', 'id ASC'],
            ]);

            foreach ($iterator as $row) {
                $entries[] = $row;
            }
        }

        TemplateRenderer::getInstance()->display('@custommenu/config.html.twig', [
            'can_edit' => Session::haveRight('config', UPDATE),
            'entries'  => $entries,
        ]);

        return true;
    }
}
