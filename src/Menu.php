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

namespace GlpiPlugin\Deploy;

use \Session;

class Menu
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

        if (Task::canUpdate()) {
            $links_class = [
                Task::class,
                Package::class,
            ];

            if (Computer_Group::canCreate()) {
                $links_class[] = Computer_Group::class;
            }

            $links = [];
            foreach ($links_class as $link) {
                $link_text = "<span class='d-none d-xxl-block'>" . $link::getTypeName(Session::getPluralNumber()) . "</span>";
                $links["<i class='" . $link::getIcon() . "'></i>$link_text"] = $link::getSearchURL(false);
            }

            $menu = [
                'title'   => self::getMenuName(),
                'page'    => Task::getSearchURL(false),
                'icon'    => self::getIcon(),
                'options' => [],
                'links'   => $links,
            ];

            $menu['options']['package'] = [
                'title' => Package::getTypeName(Session::getPluralNumber()),
                'page'  => Package::getSearchURL(false),
                'icon'  => Package::getIcon(),
                'links' => $links,
            ];

            if (Package::canCreate()) {
                $add_link = Package::getFormURL(false);
                $menu['links']['add'] = $add_link;
                $menu['options']['package']['links']['add'] = $add_link;
            }

            $menu['options']['task'] = [
                'title' => Task::getTypeName(Session::getPluralNumber()),
                'page'  => Task::getSearchURL(false),
                'icon'  => Task::getIcon(),
                'links' => $links
            ];

            if (Task::canCreate()) {
                $menu['options']['task']['options']['add'] = Task::getFormURL(false);
                $menu['options']['task']['links']['add']   = Task::getFormURL(false);
            }

            if (Computer_Group::canCreate()) {
                $menu['options']['computer_group'] = [
                    'title' => Computer_Group::getTypeName(Session::getPluralNumber()),
                    'page'  => Computer_Group::getSearchURL(false),
                    'icon'  => Computer_Group::getIcon(),
                    'links' => $links,
                ];

                $add_link = Computer_Group::getFormURL(false);
                $menu['options']['computer_group']['options']['add'] = Computer_Group::getFormURL(false);
                $menu['options']['computer_group']['links']['add']   = Computer_Group::getFormURL(false);
            }

        }

        if (count($menu)) {
            return $menu;
        }

        return false;
    }
}
