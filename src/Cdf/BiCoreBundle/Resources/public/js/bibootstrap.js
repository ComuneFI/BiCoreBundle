function openloaderspinner()
{
    $("#spinnerloader").addClass("is-active");
}
function closeloaderspinner()
{
    $("#spinnerloader").removeClass("is-active");
}
function formlabeladjust()
{
    $('.form-group label').each(function (index, object) {
        var fieldtowakeup = $(object).attr("for");
        if ($("#" + fieldtowakeup).val() || $("#" + fieldtowakeup).is('select')) {
            $(object).addClass("active");
        }
    });
    $(function () {
        $('.bidatepicker').datetimepicker({
            locale: 'it',
            format: 'L'
        });
        $('.bidatetimepicker').datetimepicker({
            locale: 'it',
        });
    });
}
