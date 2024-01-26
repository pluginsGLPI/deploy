<?php

namespace GlpiPlugin\Deploy;

use CommonDBTM;
use DBConnection;
use DBmysqlIterator;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Migration;

class Package_Timeslot extends CommonDBTM
{
    use Package_Subitem;

    public static function getTypeName($nb = 0)
    {
        return __('Timeslot', 'deploy');
    }

    public static function getIcon()
    {
        return 'ti ti-calendar';
    }

    public static function getForPackage(Package $package): DBmysqlIterator
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

    public static function showForPackage(Package $package)
    {
        TemplateRenderer::getInstance()->display('@deploy/package/timeslot.html.twig', [
            'package_id'   => $package->fields['id'],
        ]);
    }

    public static function getDayNumber(string $day): int
    {
        $days = [
            'monday'    => 1,
            'tuesday'   => 2,
            'wednesday' => 3,
            'thursday'  => 4,
            'friday'    => 5,
            'saturday'  => 6,
            'sunday'    => 7,
        ];

        return $days[strtolower($day)];
    }

    public static function install(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $default_charset   = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();

            $query = "CREATE TABLE {$table} (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `plugin_deploy_packages_id` int unsigned NOT NULL DEFAULT '0',
                `weekday` tinyint NOT NULL DEFAULT '1',
                `time_start`  time NULL DEFAULT NULL,
                `time_end`  time NULL DEFAULT NULL,
                `entities_id` int unsigned NOT NULL DEFAULT '0',
                `is_active` tinyint(1) NOT NULL DEFAULT '1',
                `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                `date_mod` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `entities_id` (`entities_id`),
                KEY `is_active` (`is_active`),
                KEY `is_recursive` (`is_recursive`),
                KEY `date_mod` (`date_mod`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->request($query);
        }
    }

    public static function uninstall(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        if ($DB->tableExists($table)) {
            $migration->displayMessage("Uninstalling $table");
            $DB->request("DROP TABLE {$table}");
        }
    }
}
