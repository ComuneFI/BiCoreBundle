'use strict';

$(document).on("click", '.filterable .btn-filter', function (e) {
    var panel = $(this).parents('.filterable');
    var filters = panel.find('.filters input.colonnatabellafiltro');
    if (filters.prop('readonly') === true) {
        filters.prop('readonly', false);

        $.each(filters, function (key, value) {
            $(this).attr('placeholder', $(this).attr('placeholder').trim());
            $(this).closest("th").removeClass("sorting sorting_asc sorting_desc");
        });
        filters.first().focus();
    } else {
        filters.val('').prop('readonly', true);
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
                    var decodifiche = $(this).data('decodifiche');
                    var valorifiltro = Array();
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

//, .colonnatabellafiltro[readonly]
$(document).on("click", "th.sorting .colonnatabellafiltro[readonly], th.sorting_asc .colonnatabellafiltro[readonly], th.sorting_desc .colonnatabellafiltro[readonly]", function (e) {
    var nomecampo = this.dataset["nomecampo"];
    var nomecontroller = this.dataset["nomecontroller"];
    var nuovotipoordinamento = 'ASC';
    let tab = new Tabella(nomecontroller);
    var parametri = tab.getParametriTabellaDataset();
    var colonneordinamento = JSON.parse(BiStringFunctions.getTabellaParameter(parametri.colonneordinamento));
    if (typeof colonneordinamento[nomecampo] !== 'undefined') {
        if (colonneordinamento[nomecampo] === 'ASC') {
            nuovotipoordinamento = 'DESC';
        } else {
            nuovotipoordinamento = 'ASC';
        }
    }
    tab.setDataParameterTabella("colonneordinamento", '{"' + nomecampo + '": "' + nuovotipoordinamento + '" }');
    tab.caricatabella();
});

$(document).on("submit", ".bitabellaform", function (e) {
    e.preventDefault();
    var form = $(this).closest("form");
    var formid = $(form).attr('id');
    //$("#" + formid).children('input[type="submit"]').click()
    var url = form.attr('action');
    var formSerialize = form.serialize();
    var tabellaclass = this;
    var jqxhr = $.post(url, formSerialize, function (xhr) {
        var nomecontroller = getMainTabella();
        let tab = new Tabella(nomecontroller);
        tab.caricatabella();
        BiNotification.show("Registrazione effettuata");
        //alert("success");
    }).done(function () {
        //alert("second success");
    }).fail(function (xhr, status, error) {
        //in caso
        if (xhr.status === 400) {
            form.replaceWith(xhr.responseText).promise().done(function () {
                tabellaclass.formlabeladjust();
            });
        } else {
            bootbox.alert({
                size: "large",
                closeButton: false,
                title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                message: divboxerrori(xhr.responseText)
            });
        }
    }).always(function () {
        //alert("finished");
    });

    // Perform other work here ...
    // Set another completion function for the request above
    jqxhr.always(function () {
        //alert("second finished");
    });
});