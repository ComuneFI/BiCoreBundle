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
                switch (tipocampo) {
                    case "string":
                        var testo = encodeURIComponent($(this).val());
                        var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': 'CONTAINS', 'valore': testo};
                        break;
                    case "integer":
                        var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': parseInt($(this).val())};
                        break;
                    case "decimal":
                        var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': parseFloat($(this).val())};
                        break;
                    case "boolean":
                        var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': ($(this).val() == 'SI' ? true : false)};
                        break;
                    case "date":
                        var date = getDateTimeTabella($(this).val());
                        var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': {date: date}};
                        break;
                    case "datetime":
                        var date = getDateTimeTabella($(this).val());
                        var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': {date: date}};
                        break;
                    default:
                        var elem = {'nomecampo': $(this).data('nomecampo'), 'operatore': '=', 'valore': $(this).val()};
                        break;
                }
                filtririchiesti.push(elem);
            }
        });
        setDataParameterTabella("filtri", JSON.stringify(filtririchiesti));
        //dumpParametriTabella();
        ricaricatabella();
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
    setDataParameterTabella("filtri", JSON.stringify([]));
    ricaricatabella();
});
