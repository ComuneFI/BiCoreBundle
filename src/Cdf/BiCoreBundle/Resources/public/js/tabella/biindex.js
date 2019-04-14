//Gestione symfony di passaggio parametri tra twig e javascript di parametri in attribute data-*
document.addEventListener('DOMContentLoaded', function (e) {
    e.preventDefault();
    var nomecontroller = getMainTabella();
    let tab = new Tabella(nomecontroller);
    tab.caricatabella();
    //dumpParametriTabella(nomecontroller);
});

//Sul click del pulnsate aggiorna si lancia il refresh della tabella
$(document).ready(function () {
    $(document).on("click", ".tabellarefresh", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.caricatabella();
    });
    $(document).on("click", ".tabelladel", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.eliminaselezionati();
    });
    $(document).on("click", ".paginascelta", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        var divparametri = tab.getParametriTabellaDataset();
        divparametri["paginacorrente"] = setTabellaParameter(this.dataset["paginascelta"]);
        tab.caricatabella();
    });

    $(document).on("click", ".tabellaadd", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.aggiungirecord();

    });

    $(document).on("click", ".tabelladownload", function (e) {
        e.preventDefault();
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.download();
    });

    $(document).on("click", '.birimuovifiltri', function (e) {
        var nomecontroller = this.dataset["nomecontroller"];
        let tab = new Tabella(nomecontroller);
        tab.setDataParameterTabella("filtri", JSON.stringify([]));
        tab.caricatabella();
    });
});

function getMainTabella()
{
    var nomecontroller = document.querySelector('.main-tabella').dataset["nomecontroller"];
    return nomecontroller;
}
