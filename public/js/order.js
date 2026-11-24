/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

$(document).on('click', '[data-order-mark-checkboxes]', function (e) {
    if (markCheckboxes($(this).attr('data-order-mark-checkboxes'))) {
        e.preventDefault();
    }
});

$(document).on('click', '[data-order-unmark-checkboxes]', function (e) {
    if (unMarkCheckboxes($(this).attr('data-order-unmark-checkboxes'))) {
        e.preventDefault();
    }
});

$(document).on('click', '[data-order-show-div]', function (e) {
    const root_doc = CFG_GLPI.root_doc;
    showHideDiv(
        $(this).attr('data-order-show-div'),
        $(this).attr('data-order-show-img'),
        root_doc + '/pics/plus.png',
        root_doc + '/pics/moins.png'
    );
    e.preventDefault();
});

$(document).on('click', '[data-order-select-container]', function () {
    selectNItems(
        $(this).attr('data-order-select-container'),
        $(this).attr('data-order-select-target')
    );
});

document.addEventListener('click', (e) => {
    const element = e.target.closest('[data-order-confirm]');
    if (element && !window.confirm(element.dataset.orderConfirm)) {
        e.preventDefault();
        e.stopImmediatePropagation();
    }
}, true);

$(document).on('click', '[data-order-reload-tab]', function (e) {
    e.preventDefault();
    reloadTab($(this).attr('data-order-reload-tab'));
});

$(document).on('click', '[data-order-check-all]', function () {
    checkAsCheckboxes(this, $(this).attr('data-order-check-all'), '.massive_action_checkbox');
});

function selectNItems(container_id, select_id) {
    var selectElement = document.getElementById(select_id);
    if (!selectElement) {
        console.error("Select element not found:", select_id);
        return;
    }
    var n = parseInt(selectElement.value);
    if (isNaN(n) || n <= 0) {
        console.error("Invalid number selected:", selectElement.value);
        return;
    }
    var checked = 0;
    var selector = "#" + container_id + " input[type='checkbox'][name^='item[PluginOrderLink]']";
    var checkboxes = document.querySelectorAll(selector);
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;
    });
    checkboxes.forEach(function(checkbox) {
        if (checked < n) {
            checkbox.checked = true;
            checked++;
        }
    });
}

$(document).on('click', '[data-order-add-reference]', function () {
    plugin_order_checkboxAction();
});

$(document).on('click', '[data-order-bulk-reception]', function (e) {
    e.preventDefault();
    window[$(this).attr('data-order-bulk-reception')]();
});

$(document).on('click', '[data-order-toggle-table]', function () {
    const toggle = $(this);
    const div_table = $('[name="' + toggle.attr('data-order-toggle-table') + '"]');
    const icon = toggle.find('i');
    const textSpan = icon.next('span');
    const container_name = toggle.attr('data-order-toggle-container');
    const countainer = $('#' + container_name);

    let isVisible = !div_table.is(':hidden');

    if (!isVisible) {
        div_table.show();
        icon.removeClass('ti-sort-descending').addClass('ti-sort-ascending');
        textSpan.text(toggle.attr('data-order-collapse-label'));
        countainer.val('true');
        isVisible = true;
    } else {
        div_table.hide();
        icon.removeClass('ti-sort-ascending').addClass('ti-sort-descending');
        textSpan.text(toggle.attr('data-order-expand-label'));
        countainer.val('false');
        isVisible = false;
    }

    // Update all reloadTab buttons to reflect the new visibility state
    $('[data-order-reload-tab]').each(function () {
        let params = $(this).attr('data-order-reload-tab');

        let paramsObj = {};
        params.split('&').forEach(function (param) {
            let [key, value] = param.split('=');
            paramsObj[key] = value;
        });

        paramsObj[container_name + 'visible'] = isVisible ? '1' : '0';

        let newParams = Object.entries(paramsObj).map(([key, value]) => `${key}=${value}`).join('&');
        $(this).attr('data-order-reload-tab', newParams);
    });
});

$(document).on('click', '[data-order-inline-edit]', function () {
    const trigger = $(this);
    trigger.hide();
    $('#' + trigger.attr('data-order-inline-form')).show();
    $.ajax({
        url: trigger.attr('data-order-inline-url'),
        method: 'POST',
        data: JSON.parse(trigger.attr('data-order-inline-params')),
        success: function (data) {
            $('#' + trigger.attr('data-order-inline-view')).html(data).show();
        }
    });
});

$(document).on('click', '[data-order-inline-cancel]', function () {
    const form_id = $(this).attr('data-order-inline-cancel');
    $('[data-order-inline-form="' + form_id + '"]').each(function () {
        $(this).show();
        $('#' + $(this).attr('data-order-inline-view') + ' input').remove();
    });
    $('#' + form_id).hide();
});
