define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var selectedAttachments = config.selectedAttachments;
        var fileAttachments = $H({});
        selectedAttachments.forEach(function(el) {
            fileAttachments.set(el, 0);
        });
        var gridJsObject = window[config.gridJsObjectName];

        $('file_attachments').value = Object.toJSON(fileAttachments);

        /**
         * Register product attachment
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerProductAttachment(grid, element, checked) {
            if (checked) {
                fileAttachments.set(element.value, 0);
            } else {
                fileAttachments.unset(element.value);
            }
            $('file_attachments').value = Object.toJSON(fileAttachments);
            grid.reloadParams = {
                'selected_file_attachments[]': fileAttachments.keys()
            };
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function productAttachmentRowClick(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');

                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Initialize attachment checkboxes
         *
         * @param {String} row
         * @param {Array} selectedAttachments
         */
        function productAttachmentRowInit(row, selectedAttachments) {
            var checkbox = $(row).getElementsByClassName('checkbox')[0];

            if (checkbox) {
                if (selectedAttachments.includes(checkbox.value)) {
                    checkbox.checked = true;
                }
            }
        }

        gridJsObject.rowClickCallback = productAttachmentRowClick;
        gridJsObject.checkboxCheckCallback = registerProductAttachment;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                productAttachmentRowInit(row, config.selectedAttachments);
            });
        }
    };
});
