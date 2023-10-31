<?php

namespace GlpiPlugin\Deploy;

use CommonDBTM;
use CommonGLPI;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;

class UserInteractionTemplate_Behavior extends CommonDBTM
{

    public const BEHAVIOR_CONTINUEWITHNOINTERACTION = "continue:continue";
    public const BEHAVIOR_RETRYJOBLATER = "stop:postpone";
    public const BEHAVIOR_CANCELJOB = "stop:stop";
    public static function getTypeName($nb = 0)
    {
        return __('Behaviors', 'deploy');
    }

    //Define menu name
    public static function getMenuName($nb = 0)
    {
        return self::getTypeName($nb);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case UserInteractionTemplate::class:
                return self::createTabEntry(self::getMenuName());
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case UserInteractionTemplate::class:
                return self::showForUserInteractionPackage($item);
        }

        return true;
    }
    public static function showForUserInteractionPackage(UserInteractionTemplate $uit)
    {
        TemplateRenderer::getInstance()->display('@deploy/userinteractiontemplate/userinteractiontemplate_behavior.html.twig', [
            'item' => $uit,
            'behaviors' => self::getAllBehaviorsLabel(),
            'params' => [
                'candel' => false
            ]
        ]);
    }

    public static function getBehaviorLabelDropdown(
        $type,
        $value = 0,
        $options = []
    ): string {
        $name = $type;
        $values = static::getAllBehaviorsLabel();
        return Dropdown::showFromArray(
            $name,
            $values,
            [
                'value'   => $value,
                'display' => false
            ]
        );
    }

    public static function getBehaviorLabel(string $type, string $value): string
    {
        if ($value === "") {
            return NOT_AVAILABLE;
        }
        $all = static::getAllBehaviorsLabel();

        if (!isset($all[$value])) {
            trigger_error(
                sprintf(
                    'Behavior %1$s does not exists!',
                    $value
                ),
                E_USER_WARNING
            );
            return NOT_AVAILABLE;
        }
        return $all[$value];
    }

    public static function getAllBehaviorsLabel(): array
    {
        return [
            self::BEHAVIOR_CONTINUEWITHNOINTERACTION => __('Continue with no interaction', 'deploy'),
            self::BEHAVIOR_RETRYJOBLATER => __('Retry job later', 'deploy'),
            self::BEHAVIOR_CANCELJOB => __('Cancel job', 'deploy'),
        ];
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []): string
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'on_timeout':
            case 'on_nouser':
            case 'on_multiusers':
            case 'on_ok':
                return self::getBehaviorLabelDropdown($field, $values[$field], $options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'on_timeout':
            case 'on_nouser':
            case 'on_multiusers':
            case 'on_ok':
                return self::getBehaviorLabel($field, $values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public static function updateJsonWithBehavior($data)
    {
        $iut = new UserInteractionTemplate();
        $iut->getFromDB($data['id']);
        $json = json_decode($iut->fields['json'], true);
        $json['on_timeout'] = $data['on_timeout'];
        $json['on_nouser'] = $data['on_nouser'];
        $json['on_multiusers'] = $data['on_multiusers'];
        $json['on_ok'] = $data['on_ok'];

        return json_encode($json);
    }
}
