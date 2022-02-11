<?php
/*
 -------------------------------------------------------------------------
 Deploy plugin for GLPI
 Copyright (C) 2022 by the Deploy Development Team.

 https://github.com/pluginsGLPI/deploy
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Deploy.

 Deploy is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Deploy is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Deploy. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginDeployMenu
{
    public static function getMenuName()
    {
        return __('Deploy');
    }


    public static function getIcon()
    {
        return 'ti ti-file-upload';
    }


    static function getMenuContent()
    {
        $menu = [];

        if (PluginDeployTask::canUpdate()) {
            $links_class = [
                "PluginDeployTask",
                "PluginDeployPackage",
            ];
            $links = [];
            foreach ($links_class as $link) {
                $link_text = "<span class='d-none d-xxl-block'>" . $link::getTypeName(Session::getPluralNumber()) . "</span>";
                $links["<i class='" . $link::getIcon() . "'></i>$link_text"] = $link::getSearchURL(false);
            }

            $menu = [
                'title'   => self::getMenuName(),
                'page'    => PluginDeployTask::getSearchURL(false),
                'icon'    => self::getIcon(),
                'options' => [],
                'links'   => $links,
            ];

            $menu['options']['package'] = [
                'title' => PluginDeployPackage::getTypeName(Session::getPluralNumber()),
                'page'  => PluginDeployPackage::getSearchURL(false),
                'icon'  => PluginDeployPackage::getIcon(),
                'links' => $links,
            ];

            if (PluginDeployPackage::canCreate()) {
                $add_link = PluginDeployPackage::getFormURL(false);
                $menu['links']['add'] = $add_link;
                $menu['options']['package']['links']['add'] = $add_link;
            }

            $menu['options']['task'] = [
                'title' => PluginDeployTask::getTypeName(Session::getPluralNumber()),
                'page'  => PluginDeployTask::getSearchURL(false),
                'icon'  => PluginDeployTask::getIcon(),
                'links' => $links
            ];

            if (PluginDeployTask::canCreate()) {
                $menu['options']['task']['options']['add'] = PluginDeployTask::getFormURL(false);
                $menu['options']['task']['links']['add']   = PluginDeployTask::getFormURL(false);
            }
        }

        if (count($menu)) {
            return $menu;
        }

        return false;
    }
}
