import BiTabella from './bitabella.js';

//Sul click del pulnsate aggiorna si lancia il refresh della tabella
jQuery(document).ready(function () {
    jQuery(document).on("click", ".tabellarefresh", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        BiTabella.ricaricatabella(nomecontroller);
    });
    jQuery(document).on("click", ".tabelladel", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        eliminaselezionati(nomecontroller);
    });
    jQuery(document).on("click", ".paginascelta", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        var divparametri = getParametriTabellaDataset(nomecontroller);
        divparametri["paginacorrente"] = BiFunctions.setTabellaParameter(this.dataset["paginascelta"]);
        BiTabella.ricaricatabella(nomecontroller);
    });

    jQuery(document).on("click", ".tabellaadd", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        var parametri = getParametriTabellaDataset(nomecontroller);

        if (BiFunctions.getTabellaParameter(parametri.editinline) == 1) {
            var elencocampinuovariga = jQuery("#table" + BiFunctions.getTabellaParameter(parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='0'] input");
            var nuovariga = elencocampinuovariga.closest("tr");
            nuovariga.removeClass("sr-only");
            abilitainputinline(parametri, elencocampinuovariga, 0);
        } else {
            var parametriform = [];
            if (typeof parametri.parametriform !== "undefined") {
                parametriform.push(BiFunctions.getTabellaParameter(parametri.parametriform));
            }
            var newurl = BiFunctions.getTabellaParameter(parametri.baseurl) + BiFunctions.getTabellaParameter(parametri.nomecontroller) + "/new";
            $.ajax({
                url: newurl,
                type: "GET",
                data: {parametriform: parametriform},
                async: true,
                error: function (xhr, textStatus, errorThrown) {
                    bootbox.alert({
                        size: "large",
                        closeButton: false,
                        title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                        message: divboxerrori(xhr.responseText)
                    });
                    return false;
                },
                beforeSend: function (xhr) {

                },
                success: function (response) {
                    jQuery('#' + BiFunctions.getTabellaParameter(parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
                    var form = jQuery('#formdati' + BiFunctions.getTabellaParameter(parametri.nomecontroller));
                    form.replaceWith(response).promise().done(function () {
                        formlabeladjust();
                        jQuery('.nav-tabs a[href="#tab' + BiFunctions.getTabellaParameter(parametri.nomecontroller) + '2a"]').click();
                    });
                }
            });
        }


    });

    jQuery(document).on("click", ".tabelladownload", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        var parametri = getParametriTabellaDataset(nomecontroller);
        var url = BiFunctions.getTabellaParameter(parametri.baseurl) + BiFunctions.getTabellaParameter(parametri.nomecontroller) + '/exportxls';
        $.ajax({
            type: 'POST',
            url: url,
            data: {parametri: parametri},
            dataType: 'json'
        }).done(function (data) {
            if (data.status == '200') {
                var $a = jQuery("<a>");
                $a.attr("href", data.file);
                jQuery("body").append($a);
                $a.attr("download", BiFunctions.getTabellaParameter(parametri.nomecontroller) + ".xls");
                $a[0].click();
                $a.remove();
            } else {
                bootbox.alert({
                    size: "large",
                    closeButton: false,
                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                    message: divboxerrori(data.file)
                });
                return false;
            }
        });

    });
});

function eliminaselezionati(nomecontroller)
{
    var parametri = getParametriTabellaDataset(nomecontroller);
    var permessi = JSON.parse(BiFunctions.getTabellaParameter(parametri.permessi));
    if (permessi.update !== true) {
        binotification("Non si dispongono dei diritti per eliminare questo elemento", "warning", "it-error");
        return false;
    }
    var token = jQuery("#table" + nomecontroller).attr("data-tabletoken");
    var recordsdacancellareids = jQuery("#table" + nomecontroller + " > tbody > tr .biselecttablerow").map(function () {
        if (jQuery(this).prop("checked") === true) {
            return parseInt(this.dataset['bitableid']);
        }
    }).get();

    if (recordsdacancellareids.length > 0) {
        bootbox.confirm({
            message: "Sei sicuro di voler cancellare gli elementi selezionati?",
            buttons: {
                cancel: {
                    className: 'btn btn-default biconfirmno',
                    label: '<i class="fa fa-times"></i> Annulla'
                },
                confirm: {
                    className: 'btn btn-primary biconfirmyes',
                    label: '<i class="fa fa-check"></i> Si'
                }
            },
            callback: function (confirm) {
                if (confirm) {
                    var deleteturl = BiFunctions.getTabellaParameter(parametri.baseurl) + BiFunctions.getTabellaParameter(parametri.nomecontroller) + "/" + token + "/delete";
                    $.ajax({
                        url: deleteturl,
                        type: "POST",
                        async: true,
                        data: {id: recordsdacancellareids.join(",")},
                        error: function (xhr, textStatus, errorThrown) {
                            if (xhr.status === 501) {
                                bootbox.alert({
                                    size: "large",
                                    closeButton: false,
                                    title: '<div class="alert alert-warning" role="alert">Attenzione</div>',
                                    message: divboxerrori("Ci sono informazioni legate a questo elemento, impossibile eliminare")
                                });
                                return false;
                            } else {
                                bootbox.alert({
                                    size: "large",
                                    closeButton: false,
                                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                                    message: divboxerrori(xhr.responseText)
                                });
                                return false;
                            }
                        },
                        beforeSend: function (xhr) {

                        },
                        success: function (response) {
                            BiTabella.ricaricatabella(nomecontroller);
                            binotification("Elementi eliminati", "warning", "it-error");
                        }
                    });
                }
            }
        });
    } else {
        binotification("Selezionare almeno un elemento", "warning", "it-error");
        return false;
    }

}
//Gestione symfony di passaggio parametri tra twig e javascript di parametri in attribute data-*
document.addEventListener('DOMContentLoaded', function (e) {
    e.preventDefault();
    var nomecontroller = BiTabella.getMainTabella();
    BiTabella.ricaricatabella(nomecontroller);
    //dumpParametriTabella(nomecontroller);
});

//Funzione per modificare il valore di un parametro della tabella
function setDataParameterTabella(nomecontroller, parametro, valore)
{
    var divparametri = getParametriTabellaDataset(nomecontroller);
    divparametri[parametro] = BiFunctions.setTabellaParameter(valore);
}
//Funzione per prendere il valore di un parametro della tabella
function getDataParameterTabella(nomecontroller, parametro)
{
    var divparametri = getParametriTabellaDataset(nomecontroller);
    return divparametri[parametro];
}

