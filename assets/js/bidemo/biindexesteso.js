'use strict';
import BiTabellaFunctions from "../functions/tabella.js"
import TabellaCliente from "./tabellaestesa.js";

//Gestione symfony di passaggio parametri tra twig e javascript di parametri in attribute data-*
document.addEventListener('DOMContentLoaded', function (e) {
    e.preventDefault();
    var nomecontroller = BiTabellaFunctions.getMainTabella();
    let tab = new TabellaCliente(nomecontroller);
    tab.caricatabella();
    //dumpParametriTabella(nomecontroller);
});