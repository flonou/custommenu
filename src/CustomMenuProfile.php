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
    /**
     * Right name for the plugin.
     */
    public static string $rightname = CustomMenu::$rightname;

    /**
     * Get the tab name for the profile item.
     *
     * @param CommonGLPI $item
     * @param int        $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return self::createTabEntry(
            __('Custom Menu', 'custommenu'),
            0,
            $item::class,
            CustomMenu::getIcon()
        );
    }

    /**
     * Display the tab content for the profile item.
     *
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof Profile) {
            $profile = new self();
            $profile->showForm($item->getID());
        }

        return true;
    }

    /**
     * Display the form for configuring profile rights.
     *
     * @param int   $ID
     * @param array $options
     * @return bool
     */
    public function showForm($ID, array $options = [])
    {
        if (!self::canView()) {
            return false;
        }

        echo "<div class='spaced'>";
        $profile = new Profile();
        $profile->getFromDB($ID);

        if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        $rights = [
            [
                'itemtype'  => CustomMenu::class,
                'label'     => CustomMenu::getTypeName(Session::getPluralNumber()),
                'field'     => CustomMenu::$rightname,
            ],
        ];

        $matrix_options['title'] = __('Custom Menu management', 'custommenu');
        $profile->displayRightsChoiceMatrix($rights, $matrix_options);

        if ($canedit) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $ID]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>\n";
            Html::closeForm();
        }

        echo "</div>";

        return true;
    }

    /**
     * Install or update profile rights for the plugin.
     *
     * @return void
     */
    public static function installProfileRights()
    {
        $profile = new Profile();
        $profiles = $profile->find();

        foreach ($profiles as $profile_id => $profile_data) {
            self::addProfileRights($profile_id);
        }
    }

    /**
     * Remove profile rights for the plugin.
     *
     * @return void
     */
    public static function uninstallProfileRights()
    {
        $profile_right = new \ProfileRight();
        $profile_right->deleteByCriteria([
            'name' => CustomMenu::$rightname
        ]);
    }

    /**
     * Add profile rights for a specific profile.
     *
     * @param int $profile_id
     * @return void
     */
    public static function addProfileRights(int $profile_id)
    {
        $profile_right = new \ProfileRight();

        // Check if rights already exist for this profile
        $existing_rights = $profile_right->find(
            "`name` LIKE 'plugin_custommenu_%' AND `profiles_id` = '$profile_id'"
        );

        if (empty($existing_rights)) {
            // Add rights for CustomMenu
            $rights = [
                CustomMenu::$rightname => CREATE | READ | UPDATE | DELETE | PURGE,
            ];

            foreach ($rights as $right => $value) {
                $profile_right->add([
                    'profiles_id' => $profile_id,
                    'name'        => $right,
                    'rights'      => $value,
                ]);
            }
        }
    }

    /**
     * Check if a profile has a specific right for CustomMenu.
     *
     * @param int    $profile_id
     * @param string $right_name
     * @return bool
     */
    public static function hasRight(int $profile_id, string $right_name = ''): bool
    {
        $profile_right = new \ProfileRight();
        $right_name = $right_name ?: CustomMenu::$rightname;
        $right = $profile_right->find(
            "`profiles_id` = '$profile_id' AND `name` = '$right_name'"
        );

        return !empty($right);
    }

    /**
     * Get all rights for a profile related to CustomMenu.
     *
     * @param int $profile_id
     * @return array
     */
    public static function getProfileRights(int $profile_id): array
    {
        $profile_right = new \ProfileRight();
        $rights = $profile_right->find(
            "`profiles_id` = '$profile_id' AND `name` = '" . CustomMenu::$rightname . "'"
        );

        $result = [];
        foreach ($rights as $right) {
            $result[$right['name']] = $right['rights'];
        }

        return $result;
    }

    /**
     * Update rights for a specific profile.
     *
     * @param int   $profile_id
     * @param array $rights Array of rights to update (e.g., [CustomMenu::$rightname => READ | CREATE])
     * @return bool
     */
    public static function updateProfileRights(int $profile_id, array $rights): bool
    {
        $profile_right = new \ProfileRight();

        foreach ($rights as $right_name => $right_value) {
            $existing = $profile_right->find(
                "`profiles_id` = '$profile_id' AND `name` = '$right_name'"
            );

            if (!empty($existing)) {
                $profile_right->update([
                    'id'   => $existing[0]['id'],
                    'rights' => $right_value,
                ]);
            } else {
                $profile_right->add([
                    'profiles_id' => $profile_id,
                    'name'        => $right_name,
                    'rights'      => $right_value,
                ]);
            }
        }

        return true;
    }

    /**
     * Get the right name for CustomMenu.
     *
     * @return string
     */
    public static function getRightName(): string
    {
        return self::$rightname;
    }
}
