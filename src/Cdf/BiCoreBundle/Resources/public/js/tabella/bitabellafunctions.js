'use strict';

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
});
