/**
 * Order plugin for GLPI
 *
 * Event handlers for elements rendered with data attributes instead of
 * inline handlers (Content Security Policy compliance).
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
