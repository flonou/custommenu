<?php

namespace GlpiPlugin\CustomMenu;

use CommonGLPI;
use Html;
use Profile;
use Session;

/**
 * Profile rights management for Custom Menu plugin.
 *
 * @since 2.0.0
 */
class CustomMenuProfile extends Profile
{
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return self::createTabEntry(CustomMenu::getTypeName(Session::getPluralNumber()));
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if (!$item instanceof Profile || !self::canView()) {
            return false;
        }

        $profile = new Profile();
        $profile->getFromDB($item->getID());

        if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        echo "<div class='spaced'>";
        echo "<h2>" . CustomMenu::getTypeName(Session::getPluralNumber()) . "</h2>";

        $profile->displayRightsChoiceMatrix([
            [
                'itemtype' => CustomMenu::class,
                'label' => CustomMenu::getTypeName(Session::getPluralNumber()),
                'field' => CustomMenu::$rightname,
            ],
        ], ['title' => CustomMenu::getTypeName(Session::getPluralNumber())]);

        if ($canedit) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $item->getID()]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>";
            Html::closeForm();
        }

        echo "</div>";

        return true;
    }
}
