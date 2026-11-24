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

/* Loaded right after the markup it initialises; every init skips elements already marked ready. */
(() => {
    function initFilterSelects() {
        $('[data-deploy-filter-select]:not([data-deploy-filter-select-ready])')
            .attr('data-deploy-filter-select-ready', '')
            .select2();
    }

    function initFilesTree() {
        $('[data-deploy-file-tree]:not([data-deploy-file-tree-ready])')
            .attr('data-deploy-file-tree-ready', '')
            .fancytree({
                // load plugins
                extensions: ['glyph'],

                // enable font-awesome icons
                glyph: {
                    preset: "awesome5",
                    map: {}
                },

                // copy path on node click
                activate: function(event, data) {
                    $('#server_file_input').val((data.node.data.path ?? ""));
                },
            });

        if ($('[data-deploy-hide-footer]').length > 0) {
            $('.modal-footer').addClass('d-none');
        }
    }

    function initSortableSubitems() {
        $('[data-deploy-sortable]:not([data-deploy-sortable-ready])').each(function() {
            const $list = $(this).attr('data-deploy-sortable-ready', '');
            const subitem_itemtype = $list.attr('data-deploy-sortable');
            const ajax_url = CFG_GLPI.root_doc + '/' + GLPI_PLUGINS_PATH.deploy + '/ajax/package.php';

            sortable([this], {
                handle: '.reorder-subitem',
                placeholder: '<tr><td colspan="' + $list.attr('data-deploy-colspan') + '" class="sortable-placeholder">&nbsp;</td></tr>'
            })[0].addEventListener('sortupdate', function(e) {
                var sort_detail      = e.detail;
                var subitem_id       = sort_detail.item.dataset.subitemId;
                var new_index        = sort_detail.destination.index;
                var old_index        = sort_detail.origin.index;
                var ref_id           = sort_detail.destination.itemsBeforeUpdate[new_index].dataset.subitemId;
                var sort_action      = 'after';

                if (old_index > new_index) {
                    sort_action = 'before';
                }

                $.post(ajax_url, {
                    'action': 'move_subitem',
                    'subitem_itemtype': subitem_itemtype,
                    'subitem_id': subitem_id,
                    'ref_id': ref_id,
                    'sort_action': sort_action,
                });

                displayAjaxMessageAfterRedirect();
            });
        });
    }

    function initJsonHighlight() {
        $('[data-deploy-prism]:not([data-deploy-prism-ready])').each(function() {
            $(this).attr('data-deploy-prism-ready', '');
            import($(this).attr('data-deploy-prism'));
        });
    }

    initFilterSelects();
    initFilesTree();
    initSortableSubitems();
    initJsonHighlight();
})();
