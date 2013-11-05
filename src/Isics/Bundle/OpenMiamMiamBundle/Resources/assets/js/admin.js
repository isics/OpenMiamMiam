if (undefined === OpenMiamMiam) {
    var OpenMiamMiam = {};
}


OpenMiamMiam.ActionInSelectForm = function() {

    var object = function() {
        this.handleSubmit();
    };

    object.prototype = {
        handleSubmit: function() {
            $('form[data-type=action-in-select]').submit(function(e) {
                var form = $(this);
                form.attr('action', form.find('select').val());
            });
        }
    };

    return object;
}();

OpenMiamMiam.UrlSwitcherSelect = function() {

    var object = function() {
        this.handleSwitcher();
    };

    object.prototype = {
        handleSwitcher: function() {
            $('select[data-type=url-switcher]').change(function() {
                window.location = $(this).val();
            });
        }
    };

    return object;
}();


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
            $('#open_miam_miam_product_hasNoPrice').change(function() {
                $('#open_miam_miam_product_price').prop(
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
            var stockInput  = $('#open_miam_miam_product_stock');
            var stockErrors = stockInput.siblings('ul.list-errors');

            // radio (target) and parent label
            var availabilityInStockId    = 'open_miam_miam_product_availability_1';
            var availabilityInStockRadio = $('#'+availabilityInStockId);
            var availabilityInStockLabel = availabilityInStockRadio.parent();

            // Moves input next to radio
            var stockPlaceholderId = 'open_miam_miam_product_stock_placeholder';
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
            var availableAtSelects = $('select[name*="open_miam_miam_product[availableAt]"]');
            var availableAtErrors  = availableAtSelects.first().parent().siblings('ul.list-errors');

            // radio (target) and parent label
            var availabilityAvailableAtId    = 'open_miam_miam_product_availability_2';
            var availabilityAvailableAtRadio = $('#'+availabilityAvailableAtId);
            var availabilityAvailableAtLabel = availabilityAvailableAtRadio.parent();

            // Moves selects next to radio
            var datePlaceholderId = 'open_miam_miam_product_stock_placeholder';
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
            $(':radio[name="open_miam_miam_product[availability]"]').change(function() {
                if (availabilityInStockRadio.is(':checked')) {
                    stockInput
                        .removeClass('disabled')
                        .trigger('focus');
                } else {
                    stockInput.addClass('disabled');
                }

                if ($('#open_miam_miam_product_availability_2').is(':checked')) {
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


OpenMiamMiam.DeleteDialog = function() {
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


OpenMiamMiam.SalesOrderForm = function() {
    var object = function() {
        this.addProductsModal = $('#add-products-dialog');
        this.editionFormFieldsContainer = $('#edition-form-fields-container');
        this.addArtificialProductFormId = 'add-artificial-product-form';
        this.filterProductsFormId = 'filter-products-form';
        this.keyUpDelay = 400;

        this.initialize();
    };

    object.prototype = {
        initialize: function() {
            var that = this;

            this.addProductsModal.delegate('a.add-product-link', 'click', function(e){
                $.ajax({
                    url: this.href,
                    success: function(html) {
                        $('#'+$(html).attr('id')).replaceWith($(html));
                        that.refreshEditionFormFieldsContainer();
                    }
                });

                e.preventDefault();
            });

            this.addProductsModal.delegate('form', 'submit', function(e){
                var promise = $.ajax({
                    type: 'post',
                    url: this.action,
                    data: $(this).serialize()
                });

                if ($(this).attr('id') == that.addArtificialProductFormId) {
                    promise.done(function(html) {
                        that.addProductsModal.find('form:first').html(html);
                        that.refreshEditionFormFieldsContainer();
                    });
                    promise.fail(function(xhr){
                        that.addProductsModal.find('form:first').html(xhr.responseText);
                    });
                }

                if ($(this).attr('id') == that.filterProductsFormId) {
                    promise.done(function(html) {
                        that.addProductsModal.find('tbody').html(html);
                    });
                }

                e.preventDefault();
            });

            this.addProductsModal.delegate('form#'+this.filterProductsFormId+' select', 'change', function(){
                $('#'+that.filterProductsFormId).submit();
            });

            this.addProductsModal.delegate('form#'+this.filterProductsFormId+' input[type="text"]', 'keyup', function(){
                if (that.keyUpTimer !== undefined) {
                    clearTimeout(that.keyUpTimer);
                }
                that.keyUpTimer = setTimeout(function() {
                        $('#'+that.filterProductsFormId).submit();
                    },
                    that.keyUpDelay
                );
            });

            this.initializeControls();
        },

        initializeControls: function() {
            new OpenMiamMiam.Quantity;
            new OpenMiamMiam.DeleteDialog;
        },

        refreshEditionFormFieldsContainer: function(){
            var that = this;
            $.ajax({
                url: this.editionFormFieldsContainer.data('refresh-url'),
                success: function(html){
                    that.editionFormFieldsContainer.html(html);
                    that.initializeControls();
                }
            });
        }
    };

    return object;
}();
