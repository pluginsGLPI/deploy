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

use Glpi\Plugin\Hooks;

define('PLUGIN_DEPLOY_VERSION', '0.0.5');
define('PLUGIN_DEPLOY_REPOSITORY_PATH', GLPI_PLUGIN_DOC_DIR . "/deploy/repository");
define('PLUGIN_DEPLOY_MANIFESTS_PATH', PLUGIN_DEPLOY_REPOSITORY_PATH . "/manifests");
define('PLUGIN_DEPLOY_PARTS_PATH', PLUGIN_DEPLOY_REPOSITORY_PATH . "/parts");
define("PLUGIN_DEPLOY_MIN_GLPI", "10.1.0");
define("PLUGIN_DEPLOY_MAX_GLPI", "10.1.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_deploy()
{
    /** @var array $PLUGIN_HOOKS */
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['deploy'] = true;

    include_once(Plugin::getPhpDir("deploy") . "/vendor/autoload.php");

    $plugin = new Plugin();
    if (
        !$plugin->isInstalled('deploy')
        || !$plugin->isActivated('deploy')
    ) {
        return false;
    }

    $PLUGIN_HOOKS['menu_toadd']['deploy'] = [
        'tools' => 'GlpiPlugin\Deploy\Menu',
    ];
    $PLUGIN_HOOKS['config_page']['deploy'] = 'front/package.php';

    $PLUGIN_HOOKS[Hooks::ADD_CSS]['deploy'][] = 'lib/nouislider/dist/nouislider.css';
    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['deploy'][] = 'lib/nouislider/dist/nouislider.min.js';

    Plugin::registerClass('GlpiPlugin\Deploy\Profile', ['addtabon' => ['Profile']]);
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_deploy()
{
    return [
        'name'           => 'Deploy',
        'version'        => PLUGIN_DEPLOY_VERSION,
        'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
        'license'        => '',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_DEPLOY_MIN_GLPI,
                'max' => PLUGIN_DEPLOY_MAX_GLPI,
            ]
        ]
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_deploy_check_prerequisites()
{
    $prerequisitesSuccess = true;
    if (!is_dir(__DIR__ . '/lib/') || !is_readable(__DIR__ . '/lib/.package-lock.json')) {
        echo "Run `npm install` in the plugin directory<br>";
        $prerequisitesSuccess = false;
    }

    return $prerequisitesSuccess;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_deploy_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'deploy');
    }
    return false;
}
