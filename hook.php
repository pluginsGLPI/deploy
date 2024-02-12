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

use GlpiPlugin\Deploy\Computer\Group;
use GlpiPlugin\Deploy\Computer\GroupDynamic;
use GlpiPlugin\Deploy\Computer\GroupStatic;
use GlpiPlugin\Deploy\Package;
use GlpiPlugin\Deploy\PackageJob;
use GlpiPlugin\Deploy\PackageAction;
use GlpiPlugin\Deploy\PackageCheck;
use GlpiPlugin\Deploy\PackageFile;
use GlpiPlugin\Deploy\PackageTarget;
use GlpiPlugin\Deploy\UserInteraction;
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

    PackageAction::install($migration);
    PackageCheck::install($migration);
    PackageFile::install($migration);
    Package::install($migration);
    PackageJob::install($migration);
    PackageTarget::install($migration);
    UserInteraction::install($migration);
    Profile::install($migration);
    Repository::install($migration);
    Group::install($migration);
    GroupDynamic::install($migration);
    GroupStatic::install($migration);

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

    PackageTarget::uninstall($migration);
    Package::uninstall($migration);
    PackageJob::uninstall($migration);
    PackageTarget::uninstall($migration);
    Profile::uninstall($migration);
    UserInteraction::uninstall($migration);
    Repository::uninstall($migration);
    Group::uninstall($migration);
    GroupDynamic::uninstall($migration);
    GroupStatic::uninstall($migration);

    return true;
}


function plugin_deploy_getDropdown()
{
    $dropdowns = [UserInteraction::class => UserInteraction::getTypeName(2)];
    return $dropdowns;
}
