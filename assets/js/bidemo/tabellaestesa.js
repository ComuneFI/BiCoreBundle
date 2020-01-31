import Tabella from "../bitabella/bitabella.js"
import estraiSottotabelle from "./sottotabelle.js"
import BiStringFunctions from "../functions/string.js";


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
        console.log(recordsdaaggiornareids.length);
        if (recordsdaaggiornareids.length > 0) {
            
        }

    }
}
;

export default TabellaCliente;
