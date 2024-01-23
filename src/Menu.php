<?php

/**
 * -------------------------------------------------------------------------
 * deploy plugin for GLPI
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
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2022 by the deploy plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/deploy
 * -------------------------------------------------------------------------
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
