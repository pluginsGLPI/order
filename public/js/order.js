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
