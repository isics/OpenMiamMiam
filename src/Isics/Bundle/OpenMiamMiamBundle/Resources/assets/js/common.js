if (undefined === OpenMiamMiam) {
    var OpenMiamMiam = {};
}


OpenMiamMiam.Quantity = function() {
    var object = function() {
        this.quantities = $('.input-quantity');
        this.addQuantityButtons();
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
    };

    return object;
}();