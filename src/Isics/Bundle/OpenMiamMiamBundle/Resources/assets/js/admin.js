$(function() {
    // Handles admin switcher
    $('#admin-switcher').change(function() {
        window.location = $(this).val();
    });
});

var OpenMiamMiam = {};

OpenMiamMiam.PanelForm = function() {

    var object = function() {
        this.handleErrors();
    };

    object.prototype = {
        handleErrors: function() {
            var panelsInError = $('.panel:has(.has-error)')
                .removeClass('panel-primary')
                .addClass('panel-danger');
        }
    };

    return object;
}();

OpenMiamMiam.ProducerProductForm = function() {

    var object = function() {
        this.handlePrice();
        this.handleAvailability();
    };

    object.prototype = {
        handlePrice: function() {
            $('#open_miam_miam_admin_product_hasNoPrice').change(function() {
                $('#open_miam_miam_admin_product_price').prop(
                    'disabled',
                    $(this).is(':checked')
                )
            }).trigger('change');
        },

        handleAvailability: function() {

            //
            // Stock
            //

            // stock input (to be moved)
            var stockInput  = $('#open_miam_miam_admin_product_stock');
            var stockErrors = stockInput.siblings('ul.list-errors');

            // radio (target) and parent label
            var availabilityInStockId    = 'open_miam_miam_admin_product_availability_1';
            var availabilityInStockRadio = $('#'+availabilityInStockId);
            var availabilityInStockLabel = availabilityInStockRadio.parent();

            // Moves input next to radio
            var stockPlaceholderId = 'open_miam_miam_admin_product_stock_placeholder';
            availabilityInStockLabel.html(function(index, oldhtml) {
                return oldhtml.replace(/%stock%/i, '<div id="'+stockPlaceholderId+'"></div>');
            });

            // Reloads
            availabilityInStockRadio = $('#'+availabilityInStockId);

            // Moves errors if exists
            if (0 < stockErrors.size()) {
                availabilityInStockRadio.before(stockErrors);
                availabilityInStockRadio.parents('.checkbox:first').addClass('has-error');
            }

            // Deletes old form-group
            var stockFormGroup = stockInput.parents('.form-group:first');
            $('#'+stockPlaceholderId).replaceWith(stockInput);
            stockFormGroup.remove();

            // Creates labels for each text node and removes parent label
            availabilityInStockLabel.contents().filter(function() {
                return this.nodeType === 3;
            }).wrap(availabilityInStockLabel.clone().empty().attr('for', availabilityInStockId));
            availabilityInStockLabel.replaceWith(availabilityInStockLabel.children());

            // Select radio on focus
            stockInput.focus(function() {
                $(this).siblings(':radio:first').trigger('click');
            });


            //
            // Available at
            //

            // availableAt selects (to be moved)
            var availableAtSelects = $('select[name*="open_miam_miam_admin_product[availableAt]"]');
            var availableAtErrors  = availableAtSelects.first().parent().siblings('ul.list-errors');

            // radio (target) and parent label
            var availabilityAvailableAtId    = 'open_miam_miam_admin_product_availability_2';
            var availabilityAvailableAtRadio = $('#'+availabilityAvailableAtId);
            var availabilityAvailableAtLabel = availabilityAvailableAtRadio.parent();

            // Moves selects next to radio
            var datePlaceholderId = 'open_miam_miam_admin_product_stock_placeholder';
            availabilityAvailableAtLabel.html(function(index, oldhtml) {
                return oldhtml.replace(/%date%/i, '<div id="'+datePlaceholderId+'"></div>');
            });

            // Reloads
            availabilityAvailableAtRadio = $('#'+availabilityAvailableAtId);

            // Moves errors if exists
            if (0 < availableAtErrors.size()) {
                availabilityAvailableAtRadio.before(availableAtErrors);
                availabilityAvailableAtRadio.parents('.checkbox:first').addClass('has-error');
            }

            // Deletes old form-group
            var availableAtFormGroup = availableAtSelects.parents('.form-group:first');
            $('#'+datePlaceholderId).replaceWith(availableAtSelects);
            availableAtFormGroup.remove();

            // Creates labels for each text node and removes parent label
            availabilityAvailableAtLabel.contents().filter(function() {
                return this.nodeType === 3;
            }).wrap(availabilityAvailableAtLabel.clone().empty().attr('for', availabilityAvailableAtId));
            availabilityAvailableAtLabel.replaceWith(availabilityAvailableAtLabel.children());

            // Selects radio on focus
            availableAtSelects.focus(function() {
                $(this).siblings(':radio:first').trigger('click');
            });


            //
            // Common
            //

            // Moves focus to input/select and enable/disable (CSS class only)
            $(':radio[name="open_miam_miam_admin_product[availability]"]').change(function() {
                if (availabilityInStockRadio.is(':checked')) {
                    stockInput
                        .removeClass('disabled')
                        .trigger('focus');
                } else {
                    stockInput.addClass('disabled');
                }

                if ($('#open_miam_miam_admin_product_availability_2').is(':checked')) {
                    availableAtSelects.removeClass('disabled')
                    availableAtSelects.first().trigger('focus');
                } else {
                    availableAtSelects.addClass('disabled');
                }
            }).trigger('change');
        }
    };

    return object;
}();

OpenMiamMiam.deleteDialog = function() {
    var object = function() {
        this.handleConfirmation();
    };
    object.prototype = {
        handleConfirmation: function() {
            $('[href=#delete-dialog]').on('click', function(e) {
                e.preventDefault();

                $('#delete-dialog .btn-danger').attr('href', $(this).data('url'));
            });
        }
    };

    return object;
}();
