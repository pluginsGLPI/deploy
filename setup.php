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
 * @copyright Copyright (C) 2022-2023 by Deploy plugin team.
 * @copyright Copyright (C) 2022 by the deploy plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/Deploy
 * @link      https://github.com/pluginsGLPI/deploy
 * -------------------------------------------------------------------------
 */

define('PLUGIN_DEPLOY_VERSION', '0.0.5');
define('PLUGIN_DEPLOY_REPOSITORY_PATH', GLPI_PLUGIN_DOC_DIR . "/deploy/repository");
define('PLUGIN_DEPLOY_MANIFESTS_PATH',  PLUGIN_DEPLOY_REPOSITORY_PATH . "/manifests");
define('PLUGIN_DEPLOY_PARTS_PATH',      PLUGIN_DEPLOY_REPOSITORY_PATH . "/parts");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_deploy()
{
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
    $PLUGIN_HOOKS['config_page']['deploy'] = 'front/task.php';

    if (strpos($_SERVER['REQUEST_URI'] ?? '', Plugin::getPhpDir('deploy', false)) !== false) {
        $PLUGIN_HOOKS['add_css']['deploy'] = 'css/userinteraction.css';
    }

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
                'min' => '9.5',
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

    return true;
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
