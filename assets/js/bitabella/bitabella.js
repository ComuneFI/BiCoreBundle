import BiSpinner from '../spinner/waitpage.js';
import BiContextmenu from '../menu/contextmenuactionstabella.js';
import BiBootstrap from '../bibootstrap.js';
import BiNotification from '../notification/notification.js';
import BiFunctions from '../functions/string.js';


function getParametriTabellaDataset(nomecontroller)
{
    return document.querySelector('#Parametri' + nomecontroller + '.parametri-tabella').dataset;
}

function getMainTabella()
{
    var nomecontroller = document.querySelector('.main-tabella').dataset["nomecontroller"];
    return nomecontroller;
}

//Funzione di reload della tabella
function ricaricatabella(nomecontroller)
{
    caricatabella(getParametriTabellaDataset(nomecontroller));
}

global.ricaricatabella = ricaricatabella;

function caricatabella(parametri)
{
    console.log("caricatabella");
    beforeTabellaLoadComplete();
    BiSpinner.openloaderspinner();
    $.ajax({
        url: BiFunctions.getTabellaParameter(parametri.urltabella),
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
            BiSpinner.closeloaderspinner();
            return false;
        },
        beforeSend: function (xhr) {

        },
        success: function (response) {
            jQuery('#tabella' + BiFunctions.getTabellaParameter(parametri.nomecontroller)).html(response);
            afterTabellaLoadComplete(BiFunctions.getTabellaParameter(parametri.nomecontroller));
            BiSpinner.closeloaderspinner();
        }
    });
}

jQuery(document).on("submit", ".bitabellaform", function (e) {
    e.preventDefault();
    var form = jQuery(this).closest("form");
    var formid = jQuery(form).attr('id');
    //jQuery("#" + formid).children('input[type="submit"]').click()
    var url = form.attr('action');
    var formSerialize = form.serialize();
    var jqxhr = $.post(url, formSerialize, function (xhr) {
        var nomecontroller = BiTabella.getMainTabella();
        ricaricatabella(nomecontroller);
        BiNotification.binotification("Registrazione effettuata");
        //alert("success");
    }).done(function () {
        //alert("second success");
    }).fail(function (xhr, status, error) {
        //in caso
        if (xhr.status === 400) {
            form.replaceWith(xhr.responseText).promise().done(function () {
                BiBootstrap.formlabeladjust();
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
    jQuery('[data-toggle="tooltip"]').tooltip('disable');
    jQuery('[data-toggle="tooltip"]').tooltip('dispose');
    jQuery('[data-toggle="popover"]').popover('dispose');
}

function afterTabellaLoadComplete(nomecontroller)
{
    var parametri = getParametriTabellaDataset(nomecontroller);

    //Genera menu per edit e delete
    BiContextmenu.generatemenuconfirmation(parametri);

    //Abilita tooltip bootstrap
    jQuery(function () {
        jQuery('[data-toggle="popover"]').popover({container: 'body'});
        jQuery('[data-toggle="tooltip"]').tooltip('enable');
    });
    aggiustafootertabella(nomecontroller);

    //Sistema label per form inserimento
    BiBootstrap.formlabeladjust();

    var permessi = JSON.parse(BiFunctions.getTabellaParameter(parametri.permessi));
    if (permessi.update === true) {
        //Doppio click su riga per edit
        jQuery('#table' + nomecontroller + ' > tbody > tr').dblclick(function () {
            var biid = this.dataset['bitableid'];
            if (biid) {
                BiContextmenu.editmenu(biid, parametri);
            }
        });
    }
    jQuery(document).on("click", ".bitabellacheckboxselectall", function (e) {
        var table = jQuery(this).closest("table");
        jQuery("#" + jQuery(table).attr("id") + " > tbody > tr .biselecttablerow").prop("checked", jQuery(this).prop("checked"));
    });
}
//, .colonnatabellafiltro[readonly]
jQuery(document).on("click", "th.sorting .colonnatabellafiltro[readonly], th.sorting_asc .colonnatabellafiltro[readonly], th.sorting_desc .colonnatabellafiltro[readonly]", function (e) {
    var nomecampo = this.dataset["nomecampo"];
    var nomecontroller = this.dataset["nomecontroller"];
    var nuovotipoordinamento = 'ASC';
    var parametri = getParametriTabellaDataset(nomecontroller);
    var colonneordinamento = JSON.parse(BiFunctions.getTabellaParameter(parametri.colonneordinamento));
    if (typeof colonneordinamento[nomecampo] != 'undefined') {
        if (colonneordinamento[nomecampo] == 'ASC') {
            nuovotipoordinamento = 'DESC';
        } else {
            nuovotipoordinamento = 'ASC';
        }
    }
    setDataParameterTabella(nomecontroller, "colonneordinamento", '{"' + nomecampo + '": "' + nuovotipoordinamento + '" }');
    ricaricatabella(nomecontroller);
});


function aggiustafootertabella(nomecontroller)
{
    var colCount = 0;
    var parametri = getParametriTabellaDataset(nomecontroller);
    var nometabella = BiFunctions.getTabellaParameter(parametri.nomecontroller);
    jQuery('#tabella' + nometabella + ' tr:nth-child(2) th').each(function () {
        if (jQuery(this).attr('colspan')) {
            colCount += +jQuery(this).attr('colspan');
        } else {
            colCount++;
        }
    });
    jQuery("#bitraduzionefiltri" + nometabella).attr("colspan", colCount);
    jQuery("#bitollbarbottoni" + nometabella).attr("colspan", colCount);
    jQuery("#bititletable" + nometabella).attr("colspan", colCount);
}

function dumpParametriTabella(nomecontroller)
{
    var parametri = document.querySelector('#Parametri' + nomecontroller + '.parametri-tabella');
    $.each(parametri.dataset, function (key, value) {
        console.log(key + ":" + BiFunctions.getTabellaParameter(value));
    });
}

const BiTabella = {
    caricatabella,
    ricaricatabella,
    getParametriTabellaDataset,
    getMainTabella

};
export default BiTabella;
