import Tabella from "../bitabella/bitabella.js"
import BiStringFunctions from "../functions/string.js"
import estraiSottotabelle from "./sottotabelle.js"

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
}

export default TabellaCliente;
