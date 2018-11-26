function caricatabella(parametri)
{
    beforeTabellaLoadComplete();
    openloaderspinner();
    $.ajax({
        url: getTabellaParameter(parametri.urltabella),
        type: "POST",
        async: true,
        data: {parametri: parametri},
        error: function (xhr, textStatus, errorThrown) {
            bootbox.alert({
                size: "large",
                closeButton: false,
                title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                message: divboxerrori(xhr.responseText)
            });
            closeloaderspinner();
            return false;
        },
        beforeSend: function (xhr) {

        },
        success: function (response) {
            $('#tabella' + getTabellaParameter(parametri.nomecontroller)).html(response);
            afterTabellaLoadComplete(getTabellaParameter(parametri.nomecontroller));
            closeloaderspinner();
        }
    });
}

$(document).on("submit", ".bitabellaform", function (e) {
    e.preventDefault();
    var form = $(this).closest("form");
    var formid = $(form).attr('id');
    //$("#" + formid).children('input[type="submit"]').click()
    var url = form.attr('action');
    var formSerialize = form.serialize();
    var jqxhr = $.post(url, formSerialize, function (xhr) {
        var nomecontroller = getMainTabella();
        ricaricatabella(nomecontroller);
        binotification("Registrazione effettuata");
        //alert("success");
    }).done(function () {
        //alert("second success");
    }).fail(function (xhr, status, error) {
        //in caso
        if (xhr.status === 400) {
            form.replaceWith(xhr.responseText).promise().done(function () {
                formlabeladjust();
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
        //ricaricatabella(nomecontroller);
        //alert("second finished");
    });
});

function beforeTabellaLoadComplete()
{
    $('[data-toggle="tooltip"]').tooltip('disable');
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('[data-toggle="popover"]').popover('dispose');
}

function afterTabellaLoadComplete(nomecontroller)
{
    var parametri = getParametriTabellaDataset(nomecontroller);

    //Genera menu per edit e delete
    generatemenuconfirmation(parametri);

    //Abilita tooltip bootstrap
    $(function () {
        $('[data-toggle="popover"]').popover({container: 'body'});
        $('[data-toggle="tooltip"]').tooltip('enable');
    });
    aggiustafootertabella(nomecontroller);

    //Sistema label per form inserimento
    formlabeladjust();

    var permessi = JSON.parse(getTabellaParameter(parametri.permessi));
    if (permessi.update === true) {
        //Doppio click su riga per edit
        $('tr').dblclick(function () {
            var biid = this.dataset['bitableid'];
            if (biid) {
                editmenu(biid, parametri);
            }
        });
    }
    $(document).on("click", ".bitabellacheckboxselectall", function (e) {
        var table = $(this).closest("table");
        $("#" + $(table).attr("id") + " > tbody > tr .biselecttablerow").prop("checked", $(this).prop("checked"));
    });
}

function aggiustafootertabella(nomecontroller)
{
    var colCount = 0;
    var parametri = getParametriTabellaDataset(nomecontroller);
    var nometabella = getTabellaParameter(parametri.nomecontroller);
    $('#tabella' + nometabella + ' tr:nth-child(2) th').each(function () {
        if ($(this).attr('colspan')) {
            colCount += +$(this).attr('colspan');
        } else {
            colCount++;
        }
    });
    $("#bitraduzionefiltri" + nometabella).attr("colspan", colCount);
    $("#bitollbarbottoni" + nometabella).attr("colspan", colCount);
    $("#bititletable" + nometabella).attr("colspan", colCount);
}