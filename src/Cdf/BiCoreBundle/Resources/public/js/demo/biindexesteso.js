'use strict';

//Gestione symfony di passaggio parametri tra twig e javascript di parametri in attribute data-*
document.addEventListener('DOMContentLoaded', function (e) {
    e.preventDefault();
    var nomecontroller = TabellaCliente.getMainTabella();
    let tab = new TabellaCliente(nomecontroller);
    tab.caricatabella();
    //dumpParametriTabella(nomecontroller);
});