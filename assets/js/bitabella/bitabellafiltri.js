import BiTabella from './bitabella.js';

jQuery(document).on("click", '.filterable .btn-filter', function (e) {
    var $panel = jQuery(this).parents('.filterable');
    $filters = $panel.find('.filters input.colonnatabellafiltro');
    if ($filters.prop('readonly') == true) {
        $filters.prop('readonly', false);

        $.each($filters, function (key, value) {
            jQuery(this).attr('placeholder', jQuery(this).attr('placeholder').trim());
            jQuery(this).closest("th").removeClass("sorting sorting_asc sorting_desc");
        });
        $filters.first().focus();
    } else {
        $filters.val('').prop('readonly', true);
    }
});

jQuery(document).on("keypress", '.filterable .filters input', function (e) {
    /* Ignore tab key */
    var code = e.keyCode || e.which;
    if (code == '9') {
        return;
    }

    /* Invio */
    if (code == '13') {
        var filtririchiesti = new Array();
        jQuery(".colonnatabellafiltro").each(function (index) {
            if (jQuery(this).val() != "") {
                var tipocampo = jQuery(this).data('tipocampo');
                var valorefiltro = jQuery(this).val();
                if (jQuery(this).data('decodifiche') !== null) {
                    decodifiche = jQuery(this).data('decodifiche');
                    valorifiltro = Array();
                    $.each(decodifiche, function (key, value) {
                        if (value.toLowerCase().indexOf(valorefiltro.toLowerCase()) !== -1) {
                            valorifiltro.push(key);
                        }
                    });
                    var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': 'IN', 'valore': valorifiltro};
                } else {
                    switch (tipocampo) {
                        case "string":
                            var testo = encodeURIComponent(valorefiltro);
                            var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': 'CONTAINS', 'valore': testo};
                            break;
                        case "integer":
                            var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': '=', 'valore': parseInt(valorefiltro)};
                            break;
                        case "decimal":
                            var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': '=', 'valore': parseFloat(valorefiltro)};
                            break;
                        case "boolean":
                            var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': '=', 'valore': (valorefiltro == 'SI' ? true : false)};
                            break;
                        case "date":
                            var date = getDateTimeTabella(valorefiltro);
                            var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': '=', 'valore': {date: date}};
                            break;
                        case "datetime":
                            var date = getDateTimeTabella(valorefiltro);
                            var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': '=', 'valore': {date: date}};
                            break;
                        default:
                            var elem = {'nomecampo': jQuery(this).data('nomecampo'), 'operatore': '=', 'valore': valorefiltro};
                            break;
                    }
                }

                filtririchiesti.push(elem);
            }
        });
        var nomecontroller = this.dataset["nomecontroller"];

        setDataParameterTabella(nomecontroller, "filtri", JSON.stringify(filtririchiesti));
        //dumpParametriTabella(nomecontroller);
        BiTabella.ricaricatabella(nomecontroller);
    }
});

function getDateTimeTabella(stringadata)
{
    var date = new Date(stringadata.replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$3-$2-$1"));
    var tzoffset = date.getTimezoneOffset() * 60000; //offset in milliseconds
    var localISOTime = (new Date(date - tzoffset)).toISOString().slice(0, -1);
    return localISOTime;
}

jQuery(document).on("click", '.birimuovifiltri', function (e) {
    var nomecontroller = this.dataset["nomecontroller"];
    setDataParameterTabella(nomecontroller, "filtri", JSON.stringify([]));
    BiTabella.ricaricatabella(nomecontroller);
});
