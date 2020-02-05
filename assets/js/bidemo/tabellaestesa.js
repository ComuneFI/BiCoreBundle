import Tabella from "../bitabella/bitabella.js"
import estraiSottotabelle from "./sottotabelle.js"
import BiStringFunctions from "../functions/string.js";
import BiNotification from "../notification/notification.js";
import BiAlert from "../modal/alertbuilder.js";
import bootbox from 'bootbox';


class TabellaCliente extends Tabella {
    caricatabella()
    {
        console.log("caricatabella TabellaEstesa");
        super.caricatabella();
        //super.__dumpParametriTabella();

    }
    aggiungirecord(callback)
    {
        console.log("addrow TabellaEstesa");
        super.aggiungirecord(callback);

    }
    modificarecord(biid, callback)
    {
        console.log("editrow TabellaEstesa");
        super.modificarecord(biid, estraiSottotabelle);
    }
    cancellarecord(biid, callback)
    {
        console.log("deleterow TabellaEstesa");
        super.cancellarecord(biid, function () {
            console.log("Cancellato record " + biid);
        });
    }
    aggiornaselezionati(callback)
    {
        var tabellaclass = this;
        var permessi = JSON.parse(BiStringFunctions.getTabellaParameter(this.parametri.permessi));
        if (permessi.update !== true) {
            BiNotification.show("Non si dispongono dei diritti per eliminare questo elemento", "warning", "it-error");
            return false;
        }
        var token = $("#table" + this.nometabella).attr("data-tabletoken");
        var recordsdaaggiornareids = $("#table" + tabellaclass.nometabella + " > tbody > tr .biselecttablerow").map(function () {
            if ($(this).prop("checked") === true) {
                return parseInt(this.dataset['bitableid']);
            }
        }).get();

        if (recordsdaaggiornareids.length > 0) {
            bootbox.prompt({
                title: "Selezionare il dato da modificare",
                inputType: 'select',
                inputOptions: [
                    {
                        text: 'Attivo',
                        value: 'Cliente.attivo',
                    },
                    {
                        text: 'Punti',
                        value: 'Cliente.punti',
                    },
                    {
                        text: 'Data di nascita',
                        value: 'Cliente.datanascita',
                    }
                ],
                callback: function (result) {
                    console.log(result);
                }
            });
        }

    }
}
;

export default TabellaCliente;
