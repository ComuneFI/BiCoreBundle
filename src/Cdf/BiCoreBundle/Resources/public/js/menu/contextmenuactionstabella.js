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