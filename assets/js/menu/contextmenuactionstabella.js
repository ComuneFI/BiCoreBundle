import BiTabella from '../bitabella/bitabella.js';
import BiBootstrap from '../bibootstrap.js';
import BiFunctions from '../functions/string.js';

function generatemenuconfirmation(parametri)
{
    var bottoni = getButtons(parametri);
    if (bottoni.length > 0) {
        jQuery('[data-toggle=confirmation-popout].bibottonimodificatabella' + BiFunctions.getTabellaParameter(parametri.nomecontroller)).confirmation({
            rootSelector: '[data-toggle=confirmation-popout]',
            title: 'Cosa vuoi fare?',
            popout: true,
            onConfirm: function (operazione) {
                //Sul menu Cancella
                var biid = this.dataset["biid"];
                if (operazione === "delete") {
                    deletemenu(biid, parametri);
                }
                //Sul menu Modifica
                if (operazione === "edit") {
                    editmenu(biid, parametri);
                }
            },
            onCancel: function () {
                //alert('You didn\'t choose anything');
            },
            buttons: bottoni
        });
    } else {
        jQuery('[data-toggle=confirmation-popout].bibottonimodificatabella' + BiFunctions.getTabellaParameter(parametri.nomecontroller)).hide();
    }
}

function getButtons(parametri)
{
    var editbutton = {
        label: 'Modificare',
        value: 'edit',
        class: 'it-file'
    };
    var deletebutton = {
        label: 'Cancellare',
        value: 'delete',
        class: 'it-cancel'
    };
    var bottoni = new Array();
    var permessi = JSON.parse(BiFunctions.getTabellaParameter(parametri.permessi));
    if (permessi.update === true) {
        bottoni.push(editbutton);
    }
    if (permessi.delete === true) {
        bottoni.push(deletebutton);
    }
    return bottoni;
}

function deletemenu(biid, parametri)
{
    bootbox.confirm({
        message: "Sei sicuro di voler cancellare?",
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
                var token = jQuery("#table" + BiFunctions.getTabellaParameter(parametri.nomecontroller)).attr("data-tabletoken");
                var deleteturl = BiFunctions.getTabellaParameter(parametri.baseurl) + BiFunctions.getTabellaParameter(parametri.nomecontroller) + "/" + biid + "/" + token + "/delete";
                $.ajax({
                    url: deleteturl,
                    type: "POST",
                    async: true,
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
                        BiTabella.ricaricatabella(BiFunctions.getTabellaParameter(parametri.nomecontroller));
                        binotification("Eliminato", "warning", "it-error");
                    }
                });
            }
        }
    });
}

function editmenu(biid, parametri)
{
    if (BiFunctions.getTabellaParameter(parametri.editinline) == 1) {
        var elencocampi = jQuery("#table" + BiFunctions.getTabellaParameter(parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] input");
        abilitainputinline(parametri, elencocampi, biid);

    } else {
        var editurl = BiFunctions.getTabellaParameter(parametri.baseurl) + BiFunctions.getTabellaParameter(parametri.nomecontroller) + "/" + biid + "/edit";
        $.ajax({
            url: editurl,
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
                jQuery('#' + BiFunctions.getTabellaParameter(parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
                var form = jQuery('#formdati' + BiFunctions.getTabellaParameter(parametri.nomecontroller));
                form.replaceWith(response).promise().done(function () {
                    BiBootstrap.formlabeladjust();
                    jQuery('.nav-tabs a[href="#tab' + BiFunctions.getTabellaParameter(parametri.nomecontroller) + '2a"]').tab('show');
                });


            }
        });
    }
}


function abilitainputinline(parametri, elencocampi, biid) {
    elencocampi.each(function (index, object) {
        var fieldname = object.closest("td").dataset["nomecampo"];
        var fieldtype = object.closest("td").dataset["tipocampo"];
        var editable = object.closest("td").dataset["editabile"];
        var soggettoadecodifica = object.closest("td").dataset["soggettoadecodifica"];
        var decodifiche;
        var modellocolonne = JSON.parse(BiFunctions.getTabellaParameter(parametri.modellocolonne));

        if (soggettoadecodifica) {
            jQuery(modellocolonne).each(function (colidx, colobj) {
                if (colobj.nomecampo == fieldname) {
                    if (typeof colobj.decodifiche !== "undefined") {
                        decodifiche = colobj.decodifiche;
                        fieldtype = "decodifiche";
                    }
                }
            });
        } else {
            fieldtype = object.closest("td").dataset["tipocampo"];
        }
        var input;
        var div = jQuery('<div />', {class: 'form-group'});
        jQuery("#table" + BiFunctions.getTabellaParameter(parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").removeClass("sr-only");
        jQuery("#table" + BiFunctions.getTabellaParameter(parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + BiFunctions.getTabellaParameter(parametri.nomecontroller) + "[data-biid='" + biid + "']").addClass("sr-only");
        if (fieldname && editable == true) {
            if (fieldname == BiFunctions.getTabellaParameter(parametri.nomecontroller) + '.id' || fieldname.split(".").length > 2) {
                //Id e campi di tabelle collegate non devono essere modificabili
                input = jQuery('<input />', {type: 'text', class: 'form-control', value: jQuery(object).val(), disabled: true});
            } else {
                //fieldvalue = jQuery(object).val();
                switch (fieldtype) {
                    case 'decodifiche':
                        input = riempiselectdecodifiche(fieldname, decodifiche, jQuery(object).val());
                        break;
                    case 'boolean':
                        input = jQuery('<input />', {type: 'checkbox', class: 'form-control'});
                        if (jQuery(object).val() == 'SI') {
                            input.attr("checked", true);
                        } else {
                            input.attr("checked", false);
                        }
                        break;
                    case 'join':
                        var jointableid = object.closest("td").dataset["idtabella"];
                        input = riempiselect(fieldname, jointableid);
                        break;
                        /*case 'date':
                         input = jQuery('<input />', {type: 'text', class: 'bidatepicker form-control', value: jQuery(object).val()});
                         break;
                         case 'datetime':
                         input = jQuery('<input />', {type: 'text', class: 'bidatetimepicker form-control', value: jQuery(object).val()});
                         break;*/
                    default:
                        jQuery(object).attr("disabled", false);
                        //input = object;
                        input = jQuery('<input />', {type: 'text', class: 'form-control', value: jQuery(object).val()});
                        break;
                }
            }
        } else {
            input = jQuery(object).clone().attr("disabled", true);

        }
        jQuery(input).appendTo(div);
        jQuery(object).closest("td").html(div);
        formlabeladjust();
    });

}

const BiContextmenu = {
    generatemenuconfirmation,
    editmenu,
    deletemenu

};
export default BiContextmenu;
