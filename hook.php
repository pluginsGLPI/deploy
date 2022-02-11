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

function plugin_deploy_install()
{
    $version   = plugin_version_deploy();
    $migration = new Migration($version['version']);

    // Parse inc directory
    foreach (glob(dirname(__FILE__) . '/inc/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/inc.(.+)\.class.php$/", $filepath, $matches)) {
            $classname = 'PluginDeploy' . ucfirst($matches[1]);
            include_once($filepath);
            // If the install method exists, load it
            if (method_exists($classname, 'install')) {
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

    // Parse inc directory
    foreach (glob(dirname(__FILE__) . '/inc/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
            $classname = 'PluginDeploy' . ucfirst($matches[1]);
            include_once($filepath);
            // If the install method exists, load it
            if (method_exists($classname, 'uninstall')) {
                $classname::uninstall($migration);
            }
        }
    }
    return true;
}

