$(function() {
    var pending = 0;

    var hiddenDiv = $('<div style="display: none"></div>');
    var loader    = $('<img src="/bundles/isicsopenmiammiam/img/loader.gif" class="loader"/>');

    $('.form-add-to-cart').submit(function(event) {
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
});