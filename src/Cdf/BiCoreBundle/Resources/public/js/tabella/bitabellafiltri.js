$(document).on("click", '.filterable .btn-filter', function (e) {
    var $panel = $(this).parents('.filterable');
    $filters = $panel.find('.filters input.colonnatabellafiltro');
    if ($filters.prop('readonly') == true) {
        $filters.prop('readonly', false);

        $.each($filters, function (key, value) {
            $(this).attr('placeholder', $(this).attr('placeholder').trim());
            $(this).closest("th").removeClass("sorting sorting_asc sorting_desc");
        });
        $filters.first().focus();
    } else {
        $filters.val('').prop('readonly', true);
    }
});

$(document).on("keypress", '.filterable .filters input', function (e) {
    /* Ignore tab key */
    var code = e.keyCode || e.which;
    if (code == '9') {
        return;
    }

    /* Invio */
    if (code == '13') {
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        var filtririchiesti = new Array();
        $(".colonnatabellafiltro").each(function (index) {
            if ($(this).val() != "") {
                var tipocampo = $(this).data('tipocampo');
                var valorefiltro = $(this).val();
                if ($(this).data('decodifiche') !== null) {
                    decodifiche = $(this).data('decodifiche');
                    valorifiltro = Array();
                    $.each(decodifiche, function (key, value) {
                        if (value.toLowerCase().indexOf(valorefiltro.toLowerCase()) !== -1) {
                            valorifiltro.push(key);
                        }
                    });
                    var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': 'IN', 'valore': valorifiltro};
                } else {
                    switch (tipocampo) {
                        case "string":
                            var testo = encodeURIComponent(valorefiltro);
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': 'CONTAINS', 'valore': testo};
                            break;
                        case "integer":
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': parseInt(valorefiltro)};
                            break;
                        case "decimal":
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': parseFloat(valorefiltro)};
                            break;
                        case "boolean":
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': (valorefiltro == 'SI' ? true : false)};
                            break;
                        case "date":
                            var date = tab.getDateTimeTabella(valorefiltro);
                            console.log(date);
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': {date: date}};
                            break;
                        case "datetime":
                            var date = tab.getDateTimeTabella(valorefiltro);
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': {date: date}};
                            break;
                        default:
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': valorefiltro};
                            break;
                    }
                }

                filtririchiesti.push(elem);
            }
        });
        tab.setDataParameterTabella("filtri", JSON.stringify(filtririchiesti));
        tab.caricatabella();
        //dumpParametriTabella(nomecontroller);
    }
});

