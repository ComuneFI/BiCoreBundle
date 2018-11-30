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
                var deleteturl = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + "/" + biid + "/delete";
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
        var elencocampi = $("#tabella" + getTabellaParameter(parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] input");
        elencocampi.each(function (index, object) {
            var originalinput = $(object).clone();
            var fieldname = object.closest("td").dataset["nomecampo"];
            var fieldtype = object.closest("td").dataset["tipocampo"];
            var input;
            var div = $('<div />', {class: 'form-group'});

            if (fieldname) {
                if (fieldname == getTabellaParameter(parametri.nomecontroller) + '.id' || fieldname.split(".").length > 2) {
                    //Id e campi di tabelle collegate non devono essere modificabili
                    input = $('<input />', {type: 'text', class: 'form-control', value: $(object).val(), disabled: true});
                } else {
                    //fieldvalue = $(object).val();
                    switch (fieldtype) {
                        case 'boolean':
                            input = $('<input />', {type: 'checkbox', class: 'form-control'});
                            if ($(object).val() == 'SI') {
                                input.attr("checked", true);
                            } else {
                                input.attr("checked", false);
                            }
                            break;
                        case 'date':
                            input = $('<input />', {type: 'text', class: 'bidatepicker form-control', value: $(object).val()});
                            break;
                        case 'datetime':
                            input = $('<input />', {type: 'text', class: 'bidatetimepicker form-control', value: $(object).val()});
                            break;
                        default:
                            $(object).attr("disabled", false);
                            //input = object;
                            input = $('<input />', {type: 'text', class: 'form-control', value: $(object).val()});
                            break;
                    }
                }
            } else {
                input = $(object).clone().attr("disabled",true);

            }
            $(input).appendTo(div);
            $(object).closest("td").html(div);
            //$( ".inner" ).wrap( "<div class='new'></div>" );
            //$(object).wrap(div);
            //$(object).replaceWith(input);
            //div.append($(input));
            //console.log(div);
            //$(object).wrap(div);
            //$(object).closest("div.form-group").wrap(div);
            //$(object).closest("div.form-group").replaceWith(div);
            //$(object).replaceWith(div);
            //$(object).replaceWith(div);
            formlabeladjust();
        });

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