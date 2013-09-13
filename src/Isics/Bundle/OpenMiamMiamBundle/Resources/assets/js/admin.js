$(function() {
    // Submits "on change" admin resource choice form
    $('#open_miam_miam_admin_resource_choice_admin').change(function() {
        $(this).parents('form:first').submit();
    });
});