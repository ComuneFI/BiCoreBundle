//Sul click del pulnsate aggiorna si lancia il refresh della tabella
$(document).ready(function () {
    $(document).on("click", "#tabellarefresh", function (e) {
        e.preventDefault();
        ricaricatabella();
    });
    $(document).on("click", "#tabelladel", function (e) {
        e.preventDefault();
        eliminaselezionati();
    });
    $(document).on("click", ".paginascelta", function (e) {
        e.preventDefault();
        var divparametri = getParametriTabellaDataset();
        divparametri["paginacorrente"] = setTabellaParameter(this.dataset["paginascelta"]);
        ricaricatabella();
    });

    $(document).on("click", "#tabellaadd", function (e) {
        e.preventDefault();
        var parametri = getParametriTabellaDataset();
        var newurl = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + "/new";
        $.ajax({
            url: newurl,
            type: "GET",
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
                var form = $('#formdati' + getTabellaParameter(parametri.nomecontroller));
                form.replaceWith(response).promise().done(function () {
                    formlabeladjust();
                    $('.nav-tabs a[href="#tab2a"]').click();
                });
            }
        });
    });
    $(document).on("click", "#tabelladownload", function (e) {
        e.preventDefault();
        var parametri = getParametriTabellaDataset();
        var url = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + '/exportxls';
        $.ajax({
            type: 'POST',
            url: url,
            data: {parametri: parametri},
            dataType: 'json'
        }).done(function (data) {
            if (data.status == '200') {
                var $a = $("<a>");
                $a.attr("href", data.file);
                $("body").append($a);
                $a.attr("download", getTabellaParameter(parametri.nomecontroller) + ".xls");
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


function getParametriTabellaDataset()
{
    return document.querySelector('.parametri-tabella').dataset;
}
//Funzione di reload della tabella
function ricaricatabella()
{
    caricatabella(getParametriTabellaDataset());
}

function eliminaselezionati()
{
    var parametri = getParametriTabellaDataset();
    var permessi = JSON.parse(getTabellaParameter(parametri.permessi));
    if (permessi.update !== true) {
        binotification("Non si dispongono dei diritti per eliminare questo elemento", "warning", "it-error");
        return false;
    }
    var nomecontroller = getTabellaParameter(parametri["nomecontroller"]);
    var recordsdacancellareids = $("#tabella" + nomecontroller + " > tbody > tr .biselecttablerow").map(function () {
        if ($(this).prop("checked") === true) {
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
                    var deleteturl = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + "/delete";
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
                            ricaricatabella();
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
    ricaricatabella();
    //dumpParametriTabella();
});

//Funzione per modificare il valore di un parametro della tabella
function setDataParameterTabella(parametro, valore)
{
    var divparametri = getParametriTabellaDataset();
    divparametri[parametro] = setTabellaParameter(valore);
}
//Funzione per prendere il valore di un parametro della tabella
function getDataParameterTabella(parametro)
{
    var divparametri = getParametriTabellaDataset();
    return divparametri[parametro];
}

