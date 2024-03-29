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

namespace GlpiPlugin\Deploy;

use Agent;
use CommonDBTM;
use DBConnection;
use DBmysqlIterator;
use Glpi\Application\View\TemplateRenderer;
use Migration;
use Toolbox;

class PackageJob extends CommonDBTM
{
    use PackageSubitem;

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

    public static function convertFilterForSql(array $filters)
    {

        $sql_filters = [];
        $like_filters = [
            'log',
        ];
        foreach ($like_filters as $filter_key) {
            if (strlen(($filters[$filter_key] ?? ""))) {
                $sql_filters[$filter_key] = ['LIKE', '%' . $filters[$filter_key] . '%'];
            }
        }

        if (isset($filters['agents_id']) && !empty($filters['agents_id'])) {
            $sql_filters['agents_id'] = $filters['agents_id'];
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $sql_filters['status'] = $filters['status'];
        }

        return $sql_filters;
    }

    public static function showForPackage(Package $package)
    {
        /** @var object $DB */
        global $DB;

        $packagejobs = new self();
        $start       = intval($_GET["start"] ?? 0);
        $sort        = $_GET["sort"] ?? "";
        $order       = strtoupper($_GET["order"] ?? "");
        $filters     = $_GET['filters'] ?? [];
        $is_filtered = count($filters) > 0;

        if (strlen($sort) == 0) {
            $sort = "id";
        }
        if (strlen($order) == 0) {
            $order = "DESC";
        }
        $sql_filters = self::convertFilterForSql($filters);
        // search all package jobs
        $jobs_list = $packagejobs->find(
            [
                "plugin_deploy_packages_id" => $package->getID(),
            ]
        );

        // search package jobs with filters
        $filtered_jobs_list = $DB->request([
            'FROM' => self::getTable(),
            'WHERE' => [
                'plugin_deploy_packages_id' => $package->getID(),
            ] + $sql_filters,
            'LIMIT' => $_SESSION['glpilist_limit'],
            'START' => $start,
            'ORDER' => "$sort $order",
        ]);

        // format data for datatable
        $entries = [];
        foreach ($filtered_jobs_list as $job) {
            if ($agent = Agent::getById($job['agents_id'])) {
                $job['agents_id'] = $agent->getLink(['display' => false]);
                $job['status'] = '<span class="badge bg-' . self::getColorStatus($job['status']) . ' text-light">' . self::getStatusLabel($job['status']) . '</span>';

                //higlight keywords
                if (strlen(($filters['log'] ?? ""))) {
                    $job['log'] = str_replace($filters['log'], "<mark>" . $filters['log'] . "</mark>", $job['log']);
                    $job['log_collapse'] = "";
                    if (str_contains($job['log'], $filters['log'])) {
                        //to open div if keyword is found
                        $job['log_collapse'] = "show";
                    }
                }
                $job['log'] = "<samp><small>" . nl2br($job['log']) . "</small></samp>";
                $entries[$job['id']] = $job;
            }
        }

        // search all status
        $status = array_unique(array_column($jobs_list, 'status'));
        $status = array_combine($status, $status);
        foreach ($status as $value) {
            $status[$value] = self::getStatusLabel($value);
        }

        // search all agents
        $agents = array_unique(array_column($jobs_list, 'agents_id'));
        $agents = array_combine($agents, $agents);
        foreach ($agents as $value) {
            if ($agent = Agent::getById($value)) {
                $agents[$value] = $agent->getName();
            } else {
                unset($agents[$value]);
            }
        }

        // count total and filtered number
        $total_number = count($entries);
        $filtered_number = count($filtered_jobs_list);
        // display datatable
        TemplateRenderer::getInstance()->display('@deploy/package/datatable.html.twig', [
            'start' => $start,
            'sort' => $sort,
            'order' => $order,
            'href' => $package::getFormURLWithID($package->getID()),
            'additional_params' => $is_filtered ? http_build_query([
                'filters' => $filters
            ]) : "",
            'is_tab' => true,
            'items_id' => $package->fields['id'],
            'filters' => $filters,
            'columns' => [
                'agents_id'      => __("Agent"),
                'status'        => __("Status"),
                'date_done'     => __("Date complete", "deploy"),
                'log'           => _n("Log", 'Logs"', 2),
            ],
            'columns_values' => [
                'agents_id'      => $agents,
                'status'        => $status,
            ],
            'formatters' => [
                'agents_id'          => 'array',
                'status'             => 'array',
                'log'                => 'collapse',
                'date_done'          => 'datetime',
            ],
            'class' => [
                'log'             => 'col-md-6',
            ],
            'row_formatters' => [
                'agents_id'          => 'raw_html',
                'status'             => 'raw_html',
            ],
            'entries' => $entries,
            'total_number' => $total_number,
            'filtered_number' => $filtered_number,
        ]);
    }

    public static function install(Migration $migration)
    {
        /** @var object $DB */
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
        /** @var object $DB */
        global $DB;

        $table = self::getTable();
        if ($DB->tableExists($table)) {
            $migration->displayMessage("Uninstalling $table");
            $DB->request("DROP TABLE {$table}");
        }
    }
}
