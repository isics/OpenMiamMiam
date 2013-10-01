var OpenMiamMiam = {};

OpenMiamMiam.LocationMap = function() {
	
	var object = function(markerTitle, address1, address2, zipcode, city, country) {
		this.markerTitle = markerTitle;
		this.address = $.trim(address1+' '+address2)+' '+zipcode+' '+city;
		this.addMap();
	}

	object.prototype = {
		addMap: function() {
			var self = this;
			var geocoder = new google.maps.Geocoder();
			
			geocoder.geocode({address: self.address}, function(results, status) {
				if (status === google.maps.GeocoderStatus.OK) {
					var location = results[0].geometry.location;
					
					map = new google.maps.Map(
					    document.getElementById('location-map'),
					    {
					        zoom: 10,
						    center: location,
						    mapTypeId: google.maps.MapTypeId.ROADMAP
					    }
					);
					var marker = new google.maps.Marker({
			            position: location,
						map: map,
					    title: self.markerTitle
					});
					
					if(navigator.geolocation) {
					    navigator.geolocation.getCurrentPosition(function(position) {
					    	
					        var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
					        map.setCenter(pos);
					        var directionsService = new google.maps.DirectionsService();
						    var directionsDisplay = new google.maps.DirectionsRenderer();
						    
						    var infowindow = new google.maps.InfoWindow({
						        map: map,
						        position: pos,
						        content: 'Location found using HTML5.'
						      });
						    
						    var request = {
							        origin: pos,
							    	destination: location,
							    	travelMode: google.maps.DirectionsTravelMode.DRIVING
							};
						    
						    directionsService.route(request, function(response, status) {
						    	if (status == google.maps.DirectionsStatus.OK) {
						    	    directionsDisplay.setDirections(response);
						    	}
						    });
							directionsDisplay.setMap(map);
					    });     
					}
				}
            });
        }
    };
	return object;
}();

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


OpenMiamMiam.QuantityButtons = function() {
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
                        .val(Math.max(parseInt(quantity.val()) - 1, 0))
                        .trigger('change');
                });
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
        this.submitOnQuantityChange();
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

        submitOnQuantityChange: function() {
            var form = this.form;
            this.quantities.change(function() {
                form.submit();
            });
        },

        handleAjax: function() {
            var self = this;

            $(':submit[name="open_miam_miam_cart[update]"]').hide();

            this.form.submit(function(event) {
                event.preventDefault();

                var form = $(this);

                $.ajax({
                    type: 'PUT',
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(data) {
                        $('#header-cart').html(data.headerCart);
                        $('#cart').html(data.cart);
                        new OpenMiamMiam.QuantityButtons;
                        new OpenMiamMiam.CartUpdateForm;
                    },
                    error: function(jqXHR) {
                        alert(jqXHR.responseText);
                    },
                    complete: function() {
                    }
                });
            });
        }
    };

    return object;
}();
