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

class PluginDeployFile extends CommonDBTM
{
    public static $rightname = 'entity';

    public static function getTypeName($nb = 0)
    {
        return _n('File', 'Files', $nb, 'deploy');
    }


    public static function getIcon()
    {
        return 'ti ti-file';
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'PluginDeployPackage') {
            $files  = self::getForPackage($item);
            $number = count($files);
            return self::createTabEntry(self::getTypeName($number), $number);
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'PluginDeployPackage') {
            self::showForPackage($item);
        }
    }


    public static function showForPackage(PluginDeployPackage $package)
    {
        $files = self::getForPackage($package);
        TemplateRenderer::getInstance()->display('@deploy/files.html.twig', [
            'count' => count($files),
            'files' => $files,
            'plugin_deploy_packages_id' => $package->fields['id'],
        ]);
    }


    public static function getForPackage(PluginDeployPackage $package): DBmysqlIterator
    {
        $DBread   = DBConnection::getReadConnection();
        $iterator = $DBread->request([
            'FROM'  => self::getTable(),
            'WHERE' => [
                'plugin_deploy_packages_id' => $package->fields['id']
            ]
        ]);

        return $iterator;
    }


    public function prepareInputForAdd($input)
    {
        switch ($input['upload_mode'])
        {
            case "from_computer":
                $input = array_merge($input, $this->AddFileFromComputer());

                break;
            case "from_server":
                break;
        }

        if (!isset($input['filename']) || strlen($input['filename']) == 0) {
            return false;
        }

        return $input;
    }

    public function AddFileFromComputer(): array
    {
        $repository = new PluginDeployRepository;
        $repository_file = $repository->AddFileFromComputer();
        return $repository_file->getDefinition();
    }


    public static function install(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $default_charset = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int NOT NULL AUTO_INCREMENT,
                `plugin_deploy_packages_id` int unsigned NOT NULL DEFAULT '0',
                `filename` text,
                `filesize` varchar(255) DEFAULT NULL,
                `mimetype` varchar(255) DEFAULT NULL,
                `sha512` varchar(128) DEFAULT NULL,
                `p2p` tinyint(1) NOT NULL DEFAULT '0',
                `p2p_retention_days` int(11) NOT NULL DEFAULT '0',
                `uncompress` tinyint(1) NOT NULL DEFAULT '0',
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `plugin_deploy_packages_id` (`plugin_deploy_packages_id`),
                KEY `date_creation` (`date_creation`),
                KEY `date_mod` (`date_mod`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->query($query) or die($DB->error());
        }
    }


    public static function uninstall(Migration $migration)
    {
        $table = self::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);
    }
}
