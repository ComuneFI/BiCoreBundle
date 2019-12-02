import Tabella from './bitabella.js';
import BiStringFunctions from "../functions/string.js";
import bootbox from 'bootbox';
import BiAlert from "../modal/alertbuilder.js";

//Gestione symfony di passaggio parametri tra twig e javascript di parametri in attribute data-*
document.addEventListener('DOMContentLoaded', function (e) {
    e.preventDefault();
    var nomecontroller = Tabella.getMainTabella();
    let tab = new Tabella(nomecontroller);
    tab.caricatabella();
    //dumpParametriTabella(nomecontroller);
});

//Gestione filtri
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
                if (typeof $(this).data('decodifiche') !== 'undefined' ) {
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
                        case "text":
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
                            var date = tab.getDateTimeTabella(valorefiltro + " 00:00:00");
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

//Gestione Ordinamento
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

$(document).ready(function () {
    //Sul click del pulsante aggiorna si lancia il refresh della tabella
    $(document).on("click", ".tabellarefresh", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.caricatabella();
    });
    //Sul click del pulsante cancella si lancia la detete dei records selezionati
    $(document).on("click", ".tabelladel", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.eliminaselezionati();
    });
    //Sul click del link $pagina si lancia la refresh per andare alla pagina selezionata
    $(document).on("click", ".paginascelta", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        var divparametri = tab.getParametriTabellaDataset();
        divparametri["paginacorrente"] = BiStringFunctions.setTabellaParameter(this.dataset["paginascelta"]);
        tab.caricatabella();
    });

    //Sul click del pulsante aggiungi si lancia la creazione di un nuovo record tramite form
    $(document).on("click", ".tabellaadd", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.aggiungirecord();

    });

    //Sul click del pulsante download si lancia il download in formato excel dei dati della tabella corrente
    $(document).on("click", ".tabelladownload", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.download();
    });

    //Sul click del pulsante rimuovi filtri si lancia il reset dei filtri e la refresh della tabella
    $(document).on("click", '.birimuovifiltri', function (e) {
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.setDataParameterTabella("filtri", JSON.stringify([]));
        tab.caricatabella();
    });

    $(document).on("click", '.bibottonieditinline', function (e) {
        var biid = this.closest("tr").dataset["bitableid"];
        var idtabella = $(this).closest("tr").closest("table").attr("id");
        var nomecontroller = this.closest("tr").closest("table").dataset["nomecontroller"];
        var azione = this.dataset["azione"];
        var inputs = $("#" + idtabella + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] :input");

        if (azione === 'conferma') {
            var values = Array();

            inputs.each(function (index, object) {
                var fieldname = object.closest("td").dataset["nomecampo"];
                var fieldtype = object.closest("td").dataset["tipocampo"];
                var disabled = $(object).attr("disabled");
                var fieldvalue;
                if (fieldtype === "boolean") {
                    fieldvalue = $(object).is(":checked");
                } else {
                    fieldvalue = $(object).val();
                }
                if (fieldname && typeof disabled === "undefined") {
                    values.push({fieldname: fieldname, fieldvalue: fieldvalue, fieldtype: fieldtype});
                }
            });

            var token = this.closest("tr").dataset["token"];
            var url = Routing.generate(nomecontroller + '_aggiorna', {id: biid, token: token});
            $.ajax({
                url: url,
                type: "POST",
                data: {values: values},
                async: true,
                error: function (xhr, textStatus, errorThrown) {
                    bootbox.alert({
                        size: "large",
                        closeButton: false,
                        title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                        message: BiAlert.showErrori(xhr.responseText)
                    });
                    return false;
                },
                beforeSend: function (xhr) {

                },
                success: function (response) {
                    let tab = new Tabella(nomecontroller);
                    tab.reseteditinline(inputs);
                    $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
                    $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
                    tab.caricatabella();

                }
            });


        }
        if (azione === 'annulla') {
            let tab = new Tabella(nomecontroller);
            tab.reseteditinline(inputs);
            $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
            $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
        }
    });
});