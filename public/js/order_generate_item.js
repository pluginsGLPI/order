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

function plugin_order_config() {
    return JSON.parse($("[data-order-generate-config]").first().attr("data-order-generate-config"));
}

	function plugin_order_adaptOnSelectedEntity(id, value) {
        // Make AJAX call
        $.ajax({
            url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
            type: 'POST',
            data: {
                'entities': value,
                'action': 'show_location_by_entity',
                'id': id
            },
            success: function(response) {
                // This part is missing - updating the target element with response
                $('#show_location_by_entity_id_' + id).html(response);
            }
        });
        $.ajax({
            url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
            type: 'POST',
            data: {
                'entities': value,
                'action': 'show_group_by_entity',
                'multiple': plugin_order_config().multiple,
                'id': id
            },
            success: function(response) {
                $('#show_group_by_entity_id_' + id).html(response);
            }
        });

        $.ajax({
            url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
            type: 'POST',
            data: {
                'entities': value,
                'action': 'show_state_by_entity',
                'id': id,
            },
            success: function(response) {
                $('#show_state_by_entity_id_' + id).html(response);
            }
        });
    }

    function plugin_order_setAllEntities(value) {
        var elements = document.querySelectorAll('[id^="show_entity"]');
        plugin_order_adaptOnSelectedEntity('mass', value);
        for (var i = 0; i < elements.length; i++) {
            (function(index) {
                $.ajax({
                    url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
                    type: 'POST',
                    data: {
                        'entities': 0,
                        'action': 'update_entity',
                        'id': index,
                        'value': value
                    },
                    success: function(response) {
                        $('#show_entity_id_' + index).html(response);
                        plugin_order_adaptOnSelectedEntity(index, value);
                    }
                });
            })(i);
        }
    }

    function plugin_order_setAllLocations(value) {
        var elements = document.querySelectorAll('[id^="show_location_by"]');
        for (var i = 0; i < elements.length; i++) {
            (function(index) {
                $.ajax({
                    url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
                    type: 'POST',
                    data: {
                        'entities': 0,
                        'action': 'show_location_by_entity',
                        'id': index,
                        'value': value
                    },
                    success: function(response) {
                        $('#show_location_by_entity_id_' + index).html(response);
                    }
                });
            })(i);
        }
    }

    function plugin_order_setAllGroups(dom_select) {

        const values = Array.from(dom_select.selectedOptions).map(
            option => option.value
        );

        var elements = document.querySelectorAll('[id^="show_group_by"]');
        for (var i = 0; i < elements.length; i++) {
            (function(index) {
                $.ajax({
                    url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
                    type: 'POST',
                    data: {
                        'entities': 0,
                        'action': 'show_group_by_entity',
                        'id': index,
                        'value': values,
                        'multiple': plugin_order_config().multiple
                    },
                    success: function(response) {
                        $('#show_group_by_entity_id_' + index).html(response);
                    }
                });
            })(i);
        }
    }

    function plugin_order_setAllStates(value) {
        var elements = document.querySelectorAll('[id^="show_state_by"]');
        for (var i = 0; i < elements.length; i++) {
            (function(index) {
                $.ajax({
                    url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
                    type: 'POST',
                    data: {
                        'entities': 0,
                        'action': 'show_state_by_entity',
                        'id': index,
                        'value': value
                    },
                    success: function(response) {
                        $('#show_state_by_entity_id_' + index).html(response);
                    }
                });
            })(i);
        }
    }

    function plugin_order_setFieldsFromTextarea(value, type) {
        var lines = value.split(/\r?\n/);
        var nameFields = document.querySelectorAll('input[name$="[' + type + ']"]');

        for (var i = 0; i < nameFields.length; i++) {
            var field = nameFields[i];
            if (i < lines.length) {
                field.value = lines[i].trim();
            } else {
                field.value = '';
            }
            var event = new Event('change', { bubbles: true });
            field.dispatchEvent(event);
            $(field).trigger('change');
        }
    }

    $(function() {
        // Namespaced off().on(): this script is re-executed each time the form is rendered
        $('#mass_name_insertion').off('input.order_generate').on('input.order_generate', function() {
            plugin_order_setFieldsFromTextarea(this.value, 'name');
        });

        $('#mass_serial_insertion').off('input.order_generate').on('input.order_generate', function() {
            plugin_order_setFieldsFromTextarea(this.value, 'serial');
        });

        $('#mass_otherserial_insertion').off('input.order_generate').on('input.order_generate', function() {
            plugin_order_setFieldsFromTextarea(this.value, 'otherserial');
        });

        if (plugin_order_config().can_immo) {
            $('#mass_immo_number_insertion').off('input.order_generate').on('input.order_generate', function() {
                plugin_order_setFieldsFromTextarea(this.value, 'immo_number');
            });
        }

        // Mass dropdowns: replaces the former inline on_change attributes
        $(document).off('change.order_generate')
            .on('change.order_generate', 'select[name="mass[entities_id]"]', function() {
                plugin_order_setAllEntities(this.value);
            })
            .on('change.order_generate', 'select[name="mass[locations_id]"]', function() {
                plugin_order_setAllLocations(this.value);
            })
            .on('change.order_generate', 'select[name="mass[groups_id]"]', function() {
                plugin_order_setAllGroups(this);
            })
            .on('change.order_generate', 'select[name="mass[states_id]"]', function() {
                plugin_order_setAllStates(this.value);
            })
            .on('change.order_generate', 'select[name^="id["][name$="[entities_id]"]', function() {
                plugin_order_adaptOnSelectedEntity(parseInt(this.name.match(/^id\[(\d+)\]/)[1], 10), this.value);
            });
    });

    function sendFieldUpdateToServer(field, fieldType) {
        // Get field value
        var fieldValue = field.value;

        // If field is empty, just remove validation states and return
        if (fieldValue.trim() === '') {
            $(field).removeClass('is-invalid');
            $(field).removeAttr('required');
            $(field).next('.invalid-feedback').remove();
            return;
        }

        // Extract item ID from field name using regex
        var namePattern = /id\[(\d+)\]\[/;
        var matches = field.name.match(namePattern);
        var itemId = matches ? matches[1] : 0;

        // Find itemtype - this should be passed from your PHP controller or set globally
        var itemtype = $('input[name="id[' + itemId + '][itemtype]"]').val();

        // Make AJAX call
        $.ajax({
            url: CFG_GLPI.root_doc + '/plugins/order/ajax/linkactions.php',
            type: 'POST',
            data: {
                'action': 'check_unicity',
                'field': fieldType,
                'field_value': fieldValue,
                'itemtype': itemtype
            },
            success: function(response) {
                if (response.trim() == 'false') {
                    if ($(field).hasClass('is-invalid')) {
                        return;
                    }

                    $(field).addClass('is-invalid');
                    // Create field type label for the message
                    let fieldTypeLabel = '';
                    if (fieldType === 'name') fieldTypeLabel = plugin_order_config().name_label;
                    if (fieldType === 'serial') fieldTypeLabel = plugin_order_config().serial_label;

                    // Add error message after the input
                    $(field).after('<div class="invalid-feedback"> ' + plugin_order_config().unicity_error + ' </div>');

                    // Add required attribute for visual indication
                    $(field).attr('required', 'required');
                } else {
                    $(field).removeClass('is-invalid');
                    $(field).removeAttr('required');
                    $(field).next('.invalid-feedback').remove();
                }
            }
        });
    }

    $(function() {
        // Namespaced off().on(): this script is re-executed each time the form is rendered
        $('input[name^="id["][name$="[name]"]').off('input.order_generate change.order_generate').on('input.order_generate change.order_generate', function() {
            sendFieldUpdateToServer(this, 'name');
        });

        $('input[name^="id["][name$="[serial]"]').off('input.order_generate change.order_generate').on('input.order_generate change.order_generate', function() {
            sendFieldUpdateToServer(this, 'serial');
        });
    });
