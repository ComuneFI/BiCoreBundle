function generatemenuconfirmation(parametri)
{
    var bottoni = getButtons(parametri);
    if (bottoni.length > 0) {
        let tab = new Tabella(getTabellaParameter(parametri.nomecontroller));
        $('[data-toggle=confirmation-popout].bibottonimodificatabella' + getTabellaParameter(parametri.nomecontroller)).confirmation({
            rootSelector: '[data-toggle=confirmation-popout]',
            title: 'Cosa vuoi fare?',
            popout: true,
            onConfirm: function (operazione) {
                //Sul menu Cancella
                var biid = this.dataset["biid"];
                if (operazione === "delete") {
                    tab.deletemenu(biid);
                }
                //Sul menu Modifica
                if (operazione === "edit") {
                    tab.editmenu(biid);
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

