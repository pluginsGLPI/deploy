<?php
/*
 -------------------------------------------------------------------------
 Deploy plugin for GLPI
 Copyright (C) 2022 by the Deploy Development Team.

 https://github.com/pluginsGLPI/deploy
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Deploy.

 Deploy is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Deploy is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Deploy. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
use Glpi\Application\View\TemplateRenderer;

class PluginDeployPackage extends CommonDBTM
{
    public static $rightname = 'entity';

    public static function getTypeName($nb = 0)
    {
        return _n('Package', 'Packages', $nb, 'deploy');
    }

    public static function getIcon()
    {
        return 'ti ti-file-zip';
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong)
            ->addStandardTab('PluginDeployPackage_Check', $ong, $options)
            ->addStandardTab('PluginDeployPackage_File', $ong, $options)
            ->addStandardTab('PluginDeployPackage_Action', $ong, $options)
            ->addStandardTab('PluginDeployPackage_Interaction', $ong, $options)
            ->addStandardTab(__CLASS__, $ong, $options);

        return $ong;
    }


    public function showDebug()
    {
        TemplateRenderer::getInstance()->display('@deploy/package/debug_json.html.twig', [
            'json' => self::getJson($this, true)
        ]);
    }


    public static function getJson(PluginDeployPackage $package, bool $pretty_json = false): string
    {
        $checks  = PluginDeployPackage_Check::getFormattedArrayForPackage($package);
        $files   = PluginDeployPackage_File::getFormattedArrayForPackage($package);
        $actions = PluginDeployPackage_Action::getFormattedArrayForPackage($package);

        $json_array = [
            'jobs' => [
                'checks'          => $checks,
                'associatedFiles' => array_keys($files),
                'actions'         => $actions
            ],
            'associatedFiles' => $files,
        ];

        return json_encode($json_array, $pretty_json ? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 0);
    }


    public function rawSearchOptions()
    {

        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false, // implicit field is id
            'datatype'           => 'number'
        ];

        $tab[] = [
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => 'comment',
            'name'               => __('Comments'),
            'datatype'           => 'text'
        ];

        $tab[] = [
            'id'                 => '19',
            'table'              => $this->getTable(),
            'field'              => 'date_mod',
            'name'               => __('Last update'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '121',
            'table'              => $this->getTable(),
            'field'              => 'date_creation',
            'name'               => __('Creation date'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '80',
            'table'              => 'glpi_entities',
            'field'              => 'completename',
            'name'               => Entity::getTypeName(1),
            'datatype'           => 'dropdown'
        ];

        return $tab;
    }

    public static function install(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $default_charset   = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int NOT NULL AUTO_INCREMENT,
                `entities_id` int unsigned NOT NULL DEFAULT '0',
                `is_recursive` tinyint NOT NULL DEFAULT '0',
                `name` varchar(255) DEFAULT NULL,
                `is_deleted` tinyint NOT NULL DEFAULT '0',
                `is_active` tinyint NOT NULL DEFAULT '0',
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                `comment` text,
                PRIMARY KEY (`id`),
                KEY `name` (`name`),
                KEY `date_creation` (`date_creation`),
                KEY `date_mod` (`date_mod`),
                KEY `is_active` (`is_active`),
                KEY `is_deleted` (`is_deleted`),
                KEY `entities_id` (`entities_id`),
                KEY `is_recursive` (`is_recursive`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->query($query) or die($DB->error());
        }

        // add display preferences
        $nb_display_pref = countElementsInTable(DisplayPreference::getTable(), [
            'itemtype' => self::getType()
        ]);
        if ($nb_display_pref == 0) {
            $dp = new DisplayPreference;
            $i  = 1;
            foreach ([1, 80, 121, 19] as $id_so) {
                $dp->add([
                    'itemtype' => self::getType(),
                    'num'      => $id_so,
                    'rank'     => $i,
                    'users_id' => 0,
                ]);
                $i++;
            }
        }
    }


    public static function uninstall(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);

        $DB->query("DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = '" . self::getType() . "'");
    }
}
