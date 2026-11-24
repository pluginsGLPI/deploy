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

/* Delegated on document: loaded once per page, works for AJAX-rendered content. */

/* Optional data-deploy-checkboxes overrides checkAsCheckboxes' default selector. */
$(document).on('click', '[data-deploy-check-all]', function() {
    checkAsCheckboxes(this, $(this).attr('data-deploy-check-all'), $(this).attr('data-deploy-checkboxes'));
});

$(document).on('click', '[data-deploy-reload-tab]', function(e) {
    e.preventDefault();
    reloadTab($(this).attr('data-deploy-reload-tab'));
});

$(document).on('click', '.next-tab', function() {
    const next_tab_el = $("#tabspanel li a.active").parent('li').next('li').find("a")[0];
    const next_tab = new bootstrap.Tab(next_tab_el);
    next_tab.show();
});

function deployOpenSubitemModal(modal, title, data) {
    modal.find('.modal-title').html(title);
    modal.find('.modal-ajax-content').load(
        CFG_GLPI.root_doc + '/' + GLPI_PLUGINS_PATH.deploy + '/ajax/package.php',
        data
    );
    bootstrap.Modal.getOrCreateInstance(modal[0]).show();
}

$(document).on('click', '[data-deploy-subitem-add]', function() {
    const subitem_type = $(this).attr('data-deploy-subitem-add');
    const modal = $('#modal_' + subitem_type);
    deployOpenSubitemModal(modal, modal.attr('data-deploy-add-title'), {
        'plugin_deploy_packages_id': modal.attr('data-deploy-package-id'),
        'action': 'add_' + subitem_type,
    });
});

$(document).on('click', '[data-deploy-subitem-edit]', function() {
    const subitem_type = $(this).attr('data-deploy-subitem-edit');
    const modal = $('#modal_' + subitem_type);
    deployOpenSubitemModal(modal, modal.attr('data-deploy-edit-title'), {
        'id': $(this).data('id'),
        'action': 'edit_' + subitem_type,
    });
});

$(document).on('change', '#actiontype', function() {
    $('.specificfields, .modal-footer').addClass('d-none');
    const actiontype = $(this).val();

    if (actiontype != "") {
        $('.modal-footer').removeClass('d-none');
    }

    $('.action-icon-type')
        .attr('class', '')
        .addClass('ms-2 action-icon-type');

    const targets = JSON.parse($(this).attr('data-deploy-action-targets'));
    if (targets[actiontype] !== undefined) {
        $(targets[actiontype].field).removeClass('d-none');
        $('.action-icon-type').addClass(targets[actiontype].icon);
    }
});

$(document).on('change', '[name="parent_type"]', function() {
    $('.subtype, .subfields, #return-bloc, .modal-footer').addClass('d-none');

    var parent_type = $(this).val();
    if (parent_type.length > 0) {
        $('#return-block').removeClass('d-none');
        $('#subtype-' + $(this).val()).removeClass('d-none');
    }
});

$(document).on('change', '.checktype', function() {
    var parentype = $('[name="parent_type"]:checked').val();
    var checktype = $(this).val();

    $('.valuefield').addClass('d-none')
        .find('label').html(__("Value", 'deploy') + '<i class="ms-2 ti ti-forms"></i>');
    $('.subfields, .modal-footer').removeClass('d-none');

    $('#checktype_value').val(checktype);
    $('#extra-help').html('');
    switch (parentype) {
        case 'registry':
            $('#extra-help').html(__("Path to the registry key (ex: \"HKEY_LOCAL_MACHINE\\SOFTWARE\\Glpi-Agent\\\")", 'deploy'));
            break;

        case 'file':
            $('#extra-help').html(__("Path to the file (ex: \"/etc/passwd\")", 'deploy'));
            break;

        case 'directory':
            $('#extra-help').html(__("Path to the directory (ex: \"/tmp\")", 'deploy'));
            break;
    }

    var types_with_value = JSON.parse($(this).attr('data-deploy-types-with-value'));
    if (Object.keys(types_with_value).includes(checktype)) {
        $('.valuefield').removeClass('d-none')
            .find('label').html(types_with_value[checktype]);
    }
});

$(document).on('select2:select', '[id^="dropdown_upload_mode"]', function() {
    $('#file_options, #file_more_options, #from_computer, #from_server, .modal-footer').addClass('d-none');

    var mode = $(this).val();
    if (mode.length > 0) {
        $('#file_options, #file_more_options, .modal-footer').removeClass('d-none');
        if (mode == 'from_computer') {
            $('#from_computer').removeClass('d-none');
        }
        if (mode == 'from_server') {
            $('#from_server').removeClass('d-none');
        }
    }
});
