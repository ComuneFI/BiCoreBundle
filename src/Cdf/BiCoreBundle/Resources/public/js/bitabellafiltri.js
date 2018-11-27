$(document).on("click", '.filterable .btn-filter', function (e) {
    var $panel = $(this).parents('.filterable');
    $filters = $panel.find('.filters input.colonnatabellafiltro');
    if ($filters.prop('disabled') == true) {
        $filters.prop('disabled', false);
        $filters.first().focus();
    } else {
        $filters.val('').prop('disabled', true);
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

                    //valorefiltro = findKeyArrayByValue(decodifiche, valorefiltro);
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
                            var date = getDateTimeTabella(valorefiltro);
                            var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': {date: date}};
                            break;
                        case "datetime":
                            var date = getDateTimeTabella(valorefiltro);
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
        var nomecontroller = this.dataset["nomecontroller"];

        setDataParameterTabella(nomecontroller, "filtri", JSON.stringify(filtririchiesti));
        //dumpParametriTabella(nomecontroller);
        ricaricatabella(nomecontroller);
    }
});

function getDateTimeTabella(stringadata)
{
    var date = new Date(stringadata.replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$3-$2-$1"));
    var tzoffset = date.getTimezoneOffset() * 60000; //offset in milliseconds
    var localISOTime = (new Date(date - tzoffset)).toISOString().slice(0, -1);
    return localISOTime;
}

$(document).on("click", '.birimuovifiltri', function (e) {
    //var $pulsante = this.id;
    var nomecontroller = getMainTabella();
    setDataParameterTabella(nomecontroller, "filtri", JSON.stringify([]));
    ricaricatabella(nomecontroller);
});
