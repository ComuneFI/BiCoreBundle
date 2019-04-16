class BiNotification {
    static show(messaggio, tipo = "info", icon = "it-info-circle") {
        $("#binotificationcontainer").remove();
        var elem = document.createElement('div');
        elem.setAttribute("id", 'binotificationcontainer');
        document.body.appendChild(elem);
        $(elem).html('<div class="notification with-icon ' + tipo + '" role="alert" aria-labelledby="' + messaggio + '" id="binotification"><h5><svg class="icon"><use xlink:href="' + window.baseUrl + '/bundles/bicore/svg/sprite.svg#' + icon + '"><\/use><\/svg>' + messaggio + '<\/h5><\/div>');
        notificationShow('binotification', 6000);
    }
}