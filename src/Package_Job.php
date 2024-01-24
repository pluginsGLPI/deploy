<?php

namespace GlpiPlugin\Deploy;

use Agent;
use CommonDBTM;
use DBConnection;
use DBmysqlIterator;
use Glpi\Application\View\TemplateRenderer;
use Migration;
use Search;

class Package_Job extends CommonDBTM
{
    use Package_Subitem;

    public static $rightname = 'entity';

    private const SUBITEM_TYPE = 'job';

    public const PREPARED = 0;
    public const SERVER_HAS_SEND_DATA = 1;
    public const AGENT_HAS_SEND_DATA = 2;
    public const DONE = 3;
    public const ERROR = 4;
    public const POSTPONED = 5;


    public static function getTypeName($nb = 0)
    {
        return _n('Job', 'Jobs', $nb, 'deploy');
    }

    public static function getIcon()
    {
        return 'ti ti-briefcase';
    }

    public static function getHeadings(): array
    {
        return [
            'agents_id'     => __('Agent'),
            'status'        => __('Status'),
            'log'           => __('Log'),
            'date_creation' => __('Creation date'),
            'date_mod'      => __('Modification date'),
            'date_done'     => __('Completion date'),
        ];
    }

    public static function getColorStatus(int $status): string
    {
        switch ($status) {
            case self::PREPARED:
                return 'secondary';
            case self::SERVER_HAS_SEND_DATA:
                return 'info';
            case self::AGENT_HAS_SEND_DATA:
                return 'info';
            case self::DONE:
                return 'success';
            case self::ERROR:
                return 'danger';
            case self::POSTPONED:
                return 'warning';
            default:
                return 'secondary';
        }
    }

    public static function getAllStatus(): array
    {
        return [
            '' => '--',
            self::PREPARED   => __('Prepared'),
            self::SERVER_HAS_SEND_DATA => __('Server has send data'),
            self::AGENT_HAS_SEND_DATA => __('Agent has send data'),
            self::DONE => __('Done'),
            self::ERROR => __('Error'),
            self::POSTPONED => __('Postponed'),
        ];
    }

    public static function getStatusLabel(string $value): string
    {
        if ($value === "") {
            return NOT_AVAILABLE;
        }

        $all = static::getAllStatus();
        if (!isset($all[$value])) {
            trigger_error(
                sprintf(
                    'Status %1$s does not exists!',
                    $value
                ),
                E_USER_WARNING
            );
            return NOT_AVAILABLE;
        }
        return $all[$value];
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

    public static function getPackageAgents(Package $package): array
    {
        $packagejobs = new self();
        // set agent_list form the filters
        $agent_id_list = $packagejobs->find(
            [
                "plugin_deploy_packages_id" => $package->getID(),
                "NOT" => [
                    "agents_id" => 0
                ]
            ]
        );
        $agent_list = [
            0 => __('No agent')
        ];
        $agent = new Agent();
        foreach ($agent_id_list as $agent_id) {
            if ($agent->getFromDB($agent_id['agents_id'])) {
                $agent_list[$agent_id['agents_id']] = $agent->fields['name'];
            } else {
                $agent_list[$agent_id['agents_id']] = __('Agent not found');
            }
        }
        return $agent_list;
    }

    public static function convertFilterForSql(array $filter)
    {
        $sql_filter = [];
        if (isset($filter) && count($filter) > 1) {
            $sql_filter = [
                'OR' => []
            ];
            if (count($filter) > 2) {
                if (isset($filter['status'])) {
                    $sql_filter['OR'][] = [
                        'status' => $filter['status']
                    ];
                }
                if (isset($filter['agents_id'])) {
                    $sql_filter['AND'][] = [
                        'agents_id' => $filter['agents_id']
                    ];
                }
            } else {
                if (isset($filter['status'])) {
                    $sql_filter['OR'][] = [
                        'status' => $filter['status']
                    ];
                }
                if (isset($filter['agents_id'])) {
                    $sql_filter['OR'][] = [
                        'agents_id' => $filter['agents_id']
                    ];
                }
            }
        }

        return $sql_filter;
    }

    public static function showForPackage(Package $package)
    {
        $packagejobs = new self();
        $filters = $_GET['filters'] ?? [];

        $sql_filters = self::convertFilterForSql($filters);
        // search package jobs with filters
        $jobs_list = $packagejobs->find(
            [
                "plugin_deploy_packages_id" => $package->getID(),
            ] + $sql_filters
        );
        TemplateRenderer::getInstance()->display('@deploy/package/job.html.twig', [
            'icon'              => self::getIcon(),
            'package'           => $package,
            'jobs_list'         => $jobs_list,
            'status_list'       => self::getAllStatus(),
            'agent_filters'     => self::getPackageAgents($package),
            'none_found'        => __('No jobs found'),
            'headers'           => self::getHeadings(),
            'filters'           => $filters,
            'filtered_number'   => count($jobs_list)
        ]);
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
                `agents_id` int unsigned NOT NULL DEFAULT '0',
                `plugin_deploy_packages_id` int unsigned NOT NULL DEFAULT '0',
                `status` int unsigned NOT NULL DEFAULT '0',
                `log` text,
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                `date_done` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `status` (`status`),
                KEY `agents_id` (`agents_id`),
                KEY `plugin_deploy_packages_id` (`plugin_deploy_packages_id`),
                KEY `date_creation` (`date_creation`),
                KEY `date_mod` (`date_mod`),
                KEY `date_done` (`date_done`)
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
