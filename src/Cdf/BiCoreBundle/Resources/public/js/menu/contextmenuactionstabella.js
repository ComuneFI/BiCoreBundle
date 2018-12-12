function generatemenuconfirmation(parametri)
{
    var bottoni = getButtons(parametri);
    if (bottoni.length > 0) {
        $('[data-toggle=confirmation-popout].bibottonimodificatabella' + getTabellaParameter(parametri.nomecontroller)).confirmation({
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
        $('[data-toggle=confirmation-popout].bibottonimodificatabella' + getTabellaParameter(parametri.nomecontroller)).hide();
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
    var permessi = JSON.parse(getTabellaParameter(parametri.permessi));
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
                var token = $("#table" + getTabellaParameter(parametri.nomecontroller)).attr("data-tabletoken");
                var deleteturl = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + "/" + biid + "/" + token + "/delete";
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
                        ricaricatabella(getTabellaParameter(parametri.nomecontroller));
                        binotification("Eliminato", "warning", "it-error");
                    }
                });
            }
        }
    });
}

function editmenu(biid, parametri)
{
    if (getTabellaParameter(parametri.editinline) == 1) {
        var elencocampi = $("#table" + getTabellaParameter(parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] input");
        abilitainputinline(parametri, elencocampi, biid);

    } else {
        var editurl = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + "/" + biid + "/edit";
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
                $('#' + getTabellaParameter(parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
                var form = $('#formdati' + getTabellaParameter(parametri.nomecontroller));
                form.replaceWith(response).promise().done(function () {
                    formlabeladjust();
                    $('.nav-tabs a[href="#tab' + getTabellaParameter(parametri.nomecontroller) + '2a"]').tab('show');
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
        var modellocolonne = JSON.parse(getTabellaParameter(parametri.modellocolonne));

        if (soggettoadecodifica) {
            $(modellocolonne).each(function (colidx, colobj) {
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
        var div = $('<div />', {class: 'form-group'});
        $("#table" + getTabellaParameter(parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").removeClass("sr-only");
        $("#table" + getTabellaParameter(parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + getTabellaParameter(parametri.nomecontroller) + "[data-biid='" + biid + "']").addClass("sr-only");
        if (fieldname && editable == true) {
            if (fieldname == getTabellaParameter(parametri.nomecontroller) + '.id' || fieldname.split(".").length > 2) {
                //Id e campi di tabelle collegate non devono essere modificabili
                input = $('<input />', {type: 'text', class: 'form-control', value: $(object).val(), disabled: true});
            } else {
                //fieldvalue = $(object).val();
                switch (fieldtype) {
                    case 'decodifiche':
                        input = riempiselectdecodifiche(fieldname, decodifiche, $(object).val());
                        break;
                    case 'boolean':
                        input = $('<input />', {type: 'checkbox', class: 'form-control'});
                        if ($(object).val() == 'SI') {
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
                         input = $('<input />', {type: 'text', class: 'bidatepicker form-control', value: $(object).val()});
                         break;
                         case 'datetime':
                         input = $('<input />', {type: 'text', class: 'bidatetimepicker form-control', value: $(object).val()});
                         break;*/
                    default:
                        $(object).attr("disabled", false);
                        //input = object;
                        input = $('<input />', {type: 'text', class: 'form-control', value: $(object).val()});
                        break;
                }
            }
        } else {
            input = $(object).clone().attr("disabled", true);

        }
        $(input).appendTo(div);
        $(object).closest("td").html(div);
        formlabeladjust();
    });

}