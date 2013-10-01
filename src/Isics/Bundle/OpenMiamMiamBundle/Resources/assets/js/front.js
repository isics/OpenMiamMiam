var OpenMiamMiam = {};


OpenMiamMiam.CartAddForm = function() {
    var object = function() {
        this.handleAjax();
    };

    object.prototype = {
        handleAjax: function() {
            var pending = 0;

            var hiddenDiv = $('<div style="display: none"></div>');
            var loader    = $('<span></span> <img src="/bundles/isicsopenmiammiam/img/loader.gif" class="loader"/>');

            $('.form-cart-add').submit(function(event) {
                event.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                form.find(':input,:submit').prop('disabled', true);
                form.find('.quantity-buttons').addClass('disabled');

                if (0 === pending) {
                    $('#header-cart > strong').append(loader);
                    $('#header-cart > strong').addClass('loading');
                    $('#header-cart .btn').attr('disabled', 'disabled');
                }

                pending++;

                form.effect('transfer', {to: $('#header-cart')}, 1000, function() {
                    $.ajax({
                        type: 'POST',
                        url: form.attr('action'),
                        data: formData,
                        dataType: 'html',
                        success: function(data) {
                            if (0 === --pending) {
                                $('#header-cart').html(data);
                            }
                        },
                        error: function(jqXHR) {
                            pending--;
                            alert(jqXHR.responseText);
                        },
                        complete: function() {
                            form.find(':input,:submit').prop('disabled', false);
                            form.find('.quantity-buttons').removeClass('disabled');

                            if (0 === pending) {
                                hiddenDiv.append(loader);
                                $('#header-cart > strong').removeClass('loading');
                                $('#header-cart .btn').removeAttr('disabled');
                            }
                        }
                    });
                });
            });
        }
    };

    return object;
}();


OpenMiamMiam.Quantity = function() {
    var object = function() {
        this.quantities = $('.input-quantity');
        this.addQuantityButtons();
        this.positionBuyingUnit();
    };

    object.prototype = {
        addQuantityButtons: function() {
            this.quantities.each(function() {
                var quantity = $(this);

                var plusButton  = $('<div class="quantity-button">+</div>');
                var minusButton = $('<div class="quantity-button">-</div>');

                var buttonsContainer = $('<div class="quantity-buttons"></div>')
                    .append(plusButton)
                    .append(minusButton);

                quantity.after(buttonsContainer);

                var buttonHeight = quantity.outerHeight()/2 - 1;

                plusButton.css({'height': buttonHeight+'px', 'line-height': buttonHeight+'px'});
                minusButton.css({'height': buttonHeight+'px', 'line-height': buttonHeight+'px'});

                quantity.addClass('input-quantity-with-buttons');

                plusButton.click(function() {
                    quantity
                        .val(Math.min(parseInt(quantity.val()) + 1, 99))
                        .trigger('change');
                });

                minusButton.click(function() {
                    quantity
                        .val(Math.max(parseInt(quantity.val()) - 1, 1))
                        .trigger('change');
                });
            });
        },

        positionBuyingUnit: function() {
            this.quantities.each(function() {
                var quantity = $(this);
                var buyingUnit = quantity.siblings('.buying-unit');

                if (0 < buyingUnit.length) {
                    var quantityPosition = quantity.position();

                    buyingUnit.css({
                        left: quantityPosition.left+'px',
                        top: (quantityPosition.top+quantity.outerHeight()-buyingUnit.outerHeight()-2)+'px',
                        width: quantity.outerWidth()+'px'
                    });

                    console.log(quantityPosition);
                    console.log(buyingUnit.position());

                    quantity.css({'padding-bottom': (buyingUnit.outerHeight()+2)+'px'});
                }
            });
        }
    };

    return object;
}();


OpenMiamMiam.CartUpdateForm = function() {

    var object = function() {
        this.form = $('#form-cart-update');
        this.quantities = this.form.find('.input-quantity');

        this.addRemoveButtons();
        this.handleAjax();
    };

    object.prototype = {
        addRemoveButtons: function() {
            var removeButton = $('<button type="submit" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span></button>');

            // Add TD to each rows
            this.form.find('tr').append($('<td></td>'));

            this.quantities.each(function() {
                var quantity = $(this);

                var removeButtonClone = removeButton.clone();
                quantity.parents('tr').find('td:last').append(removeButtonClone);

                removeButtonClone.click(function() {
                    quantity.val(0);
                });
            });
        },

        handleAjax: function() {
            var self = this;
            var form = this.form;

            $(':submit[name="open_miam_miam_cart[update]"]').hide();

            this.quantities.change(function() {
                $.ajax({
                    type: 'PUT',
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(data) {
                        $('#header-cart').html(data.headerCart);
                        $('#cart').html(data.cart);
                        new OpenMiamMiam.Quantity;
                        new OpenMiamMiam.CartUpdateForm;
                    },
                    error: function(jqXHR) {
                        alert(jqXHR.responseText);
                    }
                });
            });
        }
    };

    return object;
}();