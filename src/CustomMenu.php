<?php

namespace GlpiPlugin\CustomMenu;

use CommonDBTM;
use Glpi\Application\View\TemplateRenderer;
use Session;
use Toolbox;

/**
 * A custom entry in the main GLPI menu.
 *
 * @since 2.0.0
 */
class CustomMenu extends CommonDBTM
{
    public static string $rightname = 'custommenu::custommenuentry';

    /**
     * Valid link targets.
     */
    public const TARGET_SELF  = '_self';
    public const TARGET_BLANK = '_blank';

    /**
     * Default icon used when none is provided.
     */
    public const DEFAULT_ICON = 'ti ti-link';

    /**
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return _n('Custom menu entry', 'Custom menu entries', $nb, 'custommenu');
    }

    /**
     * Get the icon for the CustomMenu class.
     *
     * @return string
     */
    public static function getIcon(): string
    {
        return self::DEFAULT_ICON;
    }

    /**
     * List of valid link targets.
     *
     * @return array<string, string>
     */
    public static function getAvailableTargets(): array
    {
        return [
            self::TARGET_SELF  => __('Current tab', 'custommenu'),
            self::TARGET_BLANK => __('New tab', 'custommenu'),
        ];
    }


    /**
     * Sanitize and validate input before add/update.
     *
     * @param array $input
     *
     * @return array|false
     */
    public function prepareInputForAdd($input)
    {
        return $this->sanitizeInput($input);
    }

    /**
     * Sanitize and validate input before update.
     *
     * @param array $input
     *
     * @return array|false
     */
    public function prepareInputForUpdate($input)
    {
        return $this->sanitizeInput($input);
    }

    /**
     * Common input validation.
     *
     * @param array $input
     *
     * @return array|false
     */
    private function sanitizeInput(array $input)
    {
        // Name is required.
        if (empty($input['name'])) {
            Session::addMessageAfterRedirect(
                __('The name is required.', 'custommenu'),
                false,
                ERROR
            );
            return false;
        }

        // URL is required and must be a valid URL.
        if (empty($input['url'])) {
            Session::addMessageAfterRedirect(
                __('The URL is required.', 'custommenu'),
                false,
                ERROR
            );
            return false;
        }

        $input['url'] = trim($input['url']);
        if (!Toolbox::isValidWebUrl($input['url'])) {
            Session::addMessageAfterRedirect(
                __('The URL is not valid.', 'custommenu'),
                false,
                ERROR
            );
            return false;
        }

        // Icon: fall back to the default when empty.
        if (empty($input['icon'])) {
            $input['icon'] = self::DEFAULT_ICON;
        }

        // Target: restrict to the known values, default to current tab.
        if (!isset($input['target']) || !array_key_exists($input['target'], self::getAvailableTargets())) {
            $input['target'] = self::TARGET_SELF;
        }

        // Position: handle new position_type and position_value fields
        if (isset($input['position_type']) && isset($input['position_value'])) {
            $positionType = $input['position_type'];
            $positionValue = trim((string) ($input['position_value'] ?? ''));
            
            switch ($positionType) {
                case 'after':
                    $input['position'] = $positionValue ? 'after:' . $positionValue : 'end';
                    break;
                case 'before':
                    $input['position'] = $positionValue ? 'before:' . $positionValue : 'end';
                    break;
                case 'index':
                    $input['position'] = $positionValue ? (int) $positionValue : 'end';
                    break;
                case 'end':
                default:
                    $input['position'] = 'end';
                    break;
            }
            
            // Remove the temporary fields
            unset($input['position_type'], $input['position_value']);
        } elseif (!isset($input['position']) || trim((string) $input['position']) === '') {
            $input['position'] = 'end';
        } else {
            $input['position'] = trim((string) $input['position']);
        }

        // is_active: normalize to 0/1.
        $input['is_active'] = empty($input['is_active']) ? 0 : 1;

        // sort_order: normalize to a non-negative integer.
        $input['sort_order'] = max(0, (int) ($input['sort_order'] ?? 0));

        return $input;
    }

    /**
     * Display the form.
     *
     * @param int   $ID
     * @param array $options
     *
     * @return bool
     */
    public function showForm($ID, array $options = [])
    {
        $this->initForm($ID, $options);

        // Parse position for the form
        $position = $this->fields['position'] ?? 'end';
        $positionType = 'end';
        $positionValue = '';
        
        if ($position === 'end') {
            $positionType = 'end';
        } elseif (str_starts_with($position, 'after:')) {
            $positionType = 'after';
            $positionValue = substr($position, 6);
        } elseif (str_starts_with($position, 'before:')) {
            $positionType = 'before';
            $positionValue = substr($position, 7);
        } elseif (is_numeric($position)) {
            $positionType = 'index';
            $positionValue = $position;
        }
        
        // Add parsed position fields to the item
        $this->fields['position_type'] = $positionType;
        $this->fields['position_value'] = $positionValue;

        TemplateRenderer::getInstance()->display('@custommenu/custommenu.form.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);

        return true;
    }

    /**
     * Delete multiple custom menu entries at once.
     *
     * @param array $ids Array of entry IDs to delete.
     * @return bool
     */
    public static function deleteItems(array $ids): bool
    {
        $custommenu = new self();
        foreach ($ids as $id) {
            $custommenu->delete(['id' => $id]);
        }
        return true;
    }

    /**
     * Get an empty instance of the custom menu entry.
     *
     * @return array
     */
    public function getEmpty()
    {
        $empty = [
            'id'         => 0,
            'name'       => '',
            'url'        => '',
            'icon'       => self::DEFAULT_ICON,
            'target'     => self::TARGET_SELF,
            'position'   => 'end',
            'is_active'  => 0,
            'sort_order' => 0,
        ];

        return $empty;
    }

    /**
     * Search options for the list view.
     *
     * @return array
     */
    public function rawSearchOptions()
    {
        $options = [];

        $options[] = [
            'id'       => 1,
            'table'    => self::getTable(),
            'field'    => 'name',
            'name'     => __('Name'),
            'datatype'      => 'itemlink',
            'itemlink_type' => $this->getType(),
            'itemtype'       => self::class,
            'massiveaction' => false,
        ];

        $options[] = [
            'id'       => 2,
            'table'    => self::getTable(),
            'field'    => 'url',
            'name'     => __('URL'),
            'datatype' => 'string',
        ];

        $options[] = [
            'id'       => 3,
            'table'    => self::getTable(),
            'field'    => 'icon',
            'name'     => __('Icon'),
            'datatype' => 'string',
        ];

        $options[] = [
            'id'       => 4,
            'table'    => self::getTable(),
            'field'    => 'position',
            'name'     => __('Position'),
            'datatype' => 'string',
        ];

        $options[] = [
            'id'       => 5,
            'table'    => self::getTable(),
            'field'    => 'target',
            'name'     => __('Target'),
            'datatype' => 'string',
        ];

        $options[] = [
            'id'       => 6,
            'table'    => self::getTable(),
            'field'    => 'is_active',
            'name'     => __('Active'),
            'datatype' => 'boolean',
        ];

        $options[] = [
            'id'       => 7,
            'table'    => self::getTable(),
            'field'    => 'sort_order',
            'name'     => __('Sort order', 'custommenu'),
            'datatype' => 'number',
        ];

        return $options;
    }
}
