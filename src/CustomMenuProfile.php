<?php

namespace GlpiPlugin\CustomMenu;

use CommonGLPI;
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

        if ($profile->canEdit()) {
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

        if ($profile->canEdit()) {
            echo "<div class='center'>";
            echo "<input type='hidden' name='id' value='" . $item->getID() . "'>";
            echo "<input type='submit' name='update' value='" . _sx('button', 'Save') . "' class='submit'>";
            echo "</div>";
            echo "</form>";
        }

        echo "</div>";

        return true;
    }
}
