$(document).ready(function () {
    bootbox.setDefaults({
        locale: "it"
    });
});

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
            locale: 'it'
        });

        //Per impostare il layout delle select come bootstrapitalia
        $(".bootstrap-select-wrapper select").selectpicker('refresh');

    });
}

function binotification(messaggio, tipo = "info", icon = "it-info-circle") {
    $("#binotificationcontainer").html('<div class="notification with-icon ' + tipo + '" role="alert" aria-labelledby="' + messaggio + '" id="binotification"><h5><svg class="icon"><use xlink:href="' + baseUrl + 'bundles/bicore/svg/sprite.svg#' + icon + '"><\/use><\/svg>' + messaggio + '<\/h5><\/div>');
    notificationShow('binotification', 6000);
}
