import Tabella from "../bitabella/bitabella.js"
import BiTabellaFunctions from "../functions/tabella.js"
import BiStringFunctions from "../functions/string.js"
import estraiSottotabelle from "./sottotabelle.js"

class TabellaCliente extends Tabella {
    editmenu(biid)
    {
        console.log("Class TabellaEstesa");
        var tabellaclass = this;
        if (BiStringFunctions.getTabellaParameter(this.parametri.editinline) == 1) {
            var elencocampi = $("#table" + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] input");
            this.abilitainputinline(elencocampi, biid);

        } else {
            var editurl = BiStringFunctions.getTabellaParameter(this.parametri.baseurl) + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + "/" + biid + "/edit";
            $.ajax({
                url: editurl,
                type: "GET",
                async: true,
                error: function (xhr, textStatus, errorThrown) {
                    bootbox.alert({
                        size: "large",
                        closeButton: false,
                        title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                        message: BiAlert.showErrori(xhr.responseText)
                    });
                    return false;
                },
                beforeSend: function (xhr) {

                },
                success: function (response) {
                    $('#' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
                    var form = $('#formdati' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller));
                    form.replaceWith(response).promise().done(function () {
                        tabellaclass.formlabeladjust();
                        $('.nav-tabs a[href="#tab' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + '2a"]').tab('show');
                        estraiSottotabelle();
                    });
                }
            });
        }
    }
}

export default TabellaCliente;