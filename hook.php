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

function plugin_deploy_install()
{
    $version   = plugin_version_deploy();
    $migration = new Migration($version['version']);

    // Parse src directory
    foreach (glob(dirname(__FILE__) . '/src/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/src\/(.+).php$/", $filepath, $matches)) {
            $classname = 'GlpiPlugin\\Deploy\\' . ucfirst($matches[1]);
            $refl = new ReflectionClass($classname);
            // If the install method exists, load it
            if (method_exists($classname, 'install') && !$refl->isTrait()) {
                $classname::install($migration);
            }
        }
    }
    $migration->executeMigration();

    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_deploy_uninstall()
{
    $migration = new Migration(PLUGIN_DEPLOY_VERSION);

    // Parse src directory
    foreach (glob(dirname(__FILE__) . '/src/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/src\/(.+).php/", $filepath, $matches)) {
            $classname = 'GlpiPlugin\\Deploy\\' . ucfirst($matches[1]);
            $refl = new ReflectionClass($classname);
            // If the install method exists, load it
            if (method_exists($classname, 'uninstall') && !$refl->isTrait()) {
                $classname::uninstall($migration);
            }
        }
    }
    return true;
}

