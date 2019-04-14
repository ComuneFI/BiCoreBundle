//$(document).ready(function () {
//    bootbox.setDefaults({
//        locale: "en"
//    });
//});

function binotification(messaggio, tipo = "info", icon = "it-info-circle") {
    $("#binotificationcontainer").html('<div class="notification with-icon ' + tipo + '" role="alert" aria-labelledby="' + messaggio + '" id="binotification"><h5><svg class="icon"><use xlink:href="' + baseUrl + 'bundles/bicore/svg/sprite.svg#' + icon + '"><\/use><\/svg>' + messaggio + '<\/h5><\/div>');
    notificationShow('binotification', 6000);
}
