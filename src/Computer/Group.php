<?php

/**
 * -------------------------------------------------------------------------
 * Deploy plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Deploy.
 *
 * Deploy is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Deploy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Deploy. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2022-2024 by Deploy plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/deploy
 * -------------------------------------------------------------------------
 */

 namespace GlpiPlugin\Deploy\Computer;

use CommonDBTM;
use Computer;
use DisplayPreference;
use Glpi\Application\View\TemplateRenderer;
use Migration;
use Search;
use Session;

class Group extends CommonDBTM
{
    public $dohistory  = true;
    public static $rightname = 'computer_group';


    public static function getTypeName($nb = 0)
    {
        return _n('Computer Group', 'Computers Group', $nb, 'deploy');
    }

    public static function canCreate()
    {
        return Session::haveRight(static::$rightname, UPDATE);
    }

    public static function canPurge()
    {
        return Session::haveRight(static::$rightname, UPDATE);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
         ->addStandardTab('GlpiPlugin\Deploy\Computer\GroupDynamic', $ong, $options)
         ->addStandardTab('GlpiPlugin\Deploy\Computer\GroupStatic', $ong, $options)
         ->addStandardTab('Log', $ong, $options);
        return $ong;
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
            'id'                 => '3',
            'table'              => $this->getTable(),
            'field'              => 'comment',
            'name'               => __('Comment'),
            'datatype'           => 'text'
        ];

        $tab[] = [
            'id'               => '5',
            'table'            => GroupDynamic::getTable(),
            'field'            => 'search',
            'name'             => __('Number of dynamics items', 'deploy'),
            'nosearch'         => true,
            'massiveaction'    => false,
            'forcegroupby'     => true,
            'additionalfields' => ['id'],
            'joinparams'       => ['jointype' => 'child'],
            'datatype'         => 'specific',
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => GroupStatic::getTable(),
            'field'              => 'id',
            'name'               => __('Number of statics items', 'deploy'),
            'forcegroupby'       => true,
            'usehaving'          => true,
            'nosearch'           => true,
            'datatype'           => 'count',
            'massiveaction'      => false,
            'joinparams'       => ['jointype' => 'child'],
        ];

        $tab[] = [
            'id'               => '7',
            'table'            => GroupDynamic::getTable(),
            'field'            => '_virtual_dynamic_list',
            'name'             => __('List of dynamics items', 'deploy'),
            'massiveaction'    => false,
            'forcegroupby'     => true,
            'nosearch'         => true,
            'additionalfields' => ['id', 'search'],
            'searchtype'       => ['equals', 'notequals'],
            'joinparams'       => ['jointype' => 'child'],
            'datatype'         => 'specific',
        ];

        $tab[] = [
            'id'                 => '8',
            'table'              => Computer::getTable(),
            'field'              => 'name',
            'datatype'           => 'itemlink',
            'name'               => __('List of statics items', 'deploy'),
            'forcegroupby'       => true,
            'massiveaction'      => false,
            'joinparams'         => [
                'beforejoin'         => [
                    'table'              => GroupStatic::getTable(),
                    'joinparams'         => [
                        'jointype'           => 'child',
                    ]
                ]
            ]
        ];

        return $tab;
    }


    public function showForm($id, array $options = [])
    {
        if (!empty($id)) {
            $this->getFromDB($id);
        } else {
            $this->getEmpty();
        }
         $this->initForm($id, $options);

         TemplateRenderer::getInstance()->display('generic_show_form.html.twig', [
             'item'         => $this,
             'params'       => $options,
         ]);
         return true;
    }

    public function countDynamicItem()
    {
        /** @var object $DB */
    global $DB;
        $count = 0;

        $params = [
            'SELECT' => '*',
            'FROM'   => GroupDynamic::getTable(),
            'WHERE'  => ['plugin_deploy_computers_groups_id' => $this->fields['id']],
        ];

        $iterator = $DB->request($params);
        foreach ($iterator as $computergroup_dynamic) {
            $params = unserialize($computergroup_dynamic['search']);
            $computers_params["reset"] = true;
            $search_params = Search::manageParams('Computer', $computers_params);
            $data = Search::prepareDatasForSearch('Computer', $search_params);
            Search::constructSQL($data);
            Search::constructData($data, true);
            $count += $data['data']['totalcount'];
        }

        return $count;
    }


    public function countStaticItem()
    {
        /** @var object $DB */
    global $DB;
        $count = 0;

        $params = [
            'SELECT' => '*',
            'FROM'   => GroupStatic::getTable(),
            'WHERE'  => ['plugin_deploy_computers_groups_id' => $this->fields['id']],
        ];

        $iterator = $DB->request($params);
        $count = count($iterator);

        return $count;
    }


    public static function install(Migration $migration)
    {
        /** @var object $DB */
    global $DB;
        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                      `id` int unsigned NOT NULL AUTO_INCREMENT,
                      `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `date_creation` timestamp NULL DEFAULT NULL,
                      `date_mod` timestamp NULL DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `name` (`name`),
                      KEY `comment` (`comment`),
                      KEY `date_creation` (`date_creation`),
                      KEY `date_mod` (`date_mod`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $DB->doQuery($query) or die($DB->error());

           // install default display preferences

                 // add display preferences
            $nb_display_pref = countElementsInTable(DisplayPreference::getTable(), [
                'itemtype' => self::getType()
            ]);
            if ($nb_display_pref == 0) {
                  $dp = new DisplayPreference();
                  $i  = 1;
                foreach ([3, 5, 6] as $id_so) {
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
    }


    public static function uninstall(Migration $migration)
    {
        /** @var object $DB */
    global $DB;

        $table = self::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);

        $DB->doQuery("DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 'GlpiPlugin\\\Deploy\\\Computer\\\Group'");
    }


    public static function getIcon()
    {
        return "fa-fw ti ti-device-laptop";
    }


    public function post_purgeItem()
    {
    }
}
