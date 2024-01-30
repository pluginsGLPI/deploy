<?php

use GlpiPlugin\Deploy\Computer\Group;
use GlpiPlugin\Deploy\Computer\Group_Dynamic;
use GlpiPlugin\Deploy\Computer\Group_Static;
use GlpiPlugin\Deploy\Package;
use GlpiPlugin\Deploy\Package_Action;
use GlpiPlugin\Deploy\Package_Check;
use GlpiPlugin\Deploy\Package_File;
use GlpiPlugin\Deploy\Package_Target;
use GlpiPlugin\Deploy\Profile;
use GlpiPlugin\Deploy\Repository;

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

function plugin_deploy_install()
{
    $version   = plugin_version_deploy();
    $migration = new Migration($version['version']);

    Package_Action::install($migration);
    Package_Check::install($migration);
    Package_File::install($migration);
    Package::install($migration);
    Package_Target::install($migration);
    Profile::install($migration);
    Repository::install($migration);
    Group::install($migration);
    Group_Dynamic::install($migration);
    Group_Static::install($migration);

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

    Package_Target::uninstall($migration);
    Package::uninstall($migration);
    Profile::uninstall($migration);
    Repository::uninstall($migration);
    Group::uninstall($migration);
    Group_Dynamic::uninstall($migration);
    Group_Static::uninstall($migration);

    return true;
}
