<?php

/**
 * -------------------------------------------------------------------------
 * deploy plugin for GLPI
 * Copyright (C) 2022 by the deploy Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
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
        'name'           => 'deploy',
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
