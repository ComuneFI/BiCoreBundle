import Tabella from "../bitabella/bitabella.js"
import BiStringFunctions from "../functions/string.js"
import estraiSottotabelle from "./sottotabelle.js"

class TabellaCliente extends Tabella {
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
}

export default TabellaCliente;