if (undefined === OpenMiamMiam) {
    var OpenMiamMiam = {};
}


OpenMiamMiam.HomepageMap = function() {

    var object = function(country) {
        this.country = country;
        this.addMap();
    }

    object.prototype = {
        addMap: function() {
            this.map = new google.maps.Map(
                document.getElementById('homepage-map-map'),
                {
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
            );

            this.bounds = new google.maps.LatLngBounds();
        },

        addBranch: function(branch) {
            var self = this;
            var address = $.trim(branch.address1+' '+branch.address2)+' '+branch.zipcode+' '+branch.city+' '+this.country;
            var geocoder = new google.maps.Geocoder();

            geocoder.geocode({address: address}, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    var location = results[0].geometry.location;

                    var marker = new google.maps.Marker({
                        position: location,
                        map: self.map,
                        title: branch.name
                    });

                    google.maps.event.addListener(marker, 'click', function() {
                        window.location = branch.url;
                    });

                    self.bounds.extend(location);
                    self.map.fitBounds(self.bounds);
                }
            });
        }
    };

    return object;
}();


OpenMiamMiam.LocationMap = function() {

    var object = function(markerTitle, routeLink, address1, address2, zipcode, city, country) {
        this.markerTitle = markerTitle;
        this.routeLink = routeLink;
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

                    var map = new google.maps.Map(
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

                    $('#location-map').after($('<p><a href="#" id="route-link">'+self.routeLink+'</a></p>'));

                    $('#route-link').click(function() {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(function(position) {
                                var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                                window.location = 'https://maps.google.fr/maps?saddr='+pos+'&daddr='+self.address;
                            });
                        }
                    });
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
                            var error = $.parseJSON(jqXHR.responseText);

                            var message = error.message;

                            message += "\n\n";

                            $.each(error.errors, function(index, element){
                                message += element + "\n";
                            });

                            alert(message);
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


OpenMiamMiam.CartUpdateForm = function() {

    var object = function() {
        this.form = $('#form-cart-update');
        this.quantities = this.form.find('.input-quantity');

        this.addRemoveButtons();
        this.handleAjax();
    };

    object.prototype = {
        addRemoveButtons: function() {
            var removeButton = $('<button type="button" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span></button>');

            // Add TD to each rows
            this.form.find('tr').append($('<td></td>'));

            this.quantities.each(function() {
                var quantity = $(this);

                var removeButtonClone = removeButton.clone();
                quantity.parents('tr').find('td:last').append(removeButtonClone);

                removeButtonClone.click(function(e) {
                        quantity.val(0);
                        this.trigger('change');
                    }
                );
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
