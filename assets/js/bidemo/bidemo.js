import Tabella from '../bitabella/bitabella.js';

function loadSubTabellaOrdine() {
//var url = Routing.generate("Ordine_indexdettaglio");
    var url = "/Ordine/indexDettaglio";
    var clienteid = $('#tabellaOrdineCliente').data('clienteid');
    var clientenominativo = $('#tabellaOrdineCliente').data('clientenominativo');
    var data = {
        prefiltri: [{nomecampo: "Ordine.Cliente.id", operatore: "=", valore: clienteid}],
        titolotabella: 'Ordini del cliente ' + clientenominativo,
        modellocolonne: [{nomecampo: 'Ordine.Cliente', escluso: true}],
        colonneordinamento: {'Ordine.data': 'DESC', 'Ordine.quantita': 'DESC'},
        parametriform: {'cliente_id': clienteid},
        multiselezione: true
    };
    $.ajax({
        type: 'POST',
        url: url,
        data: {parametripassati: JSON.stringify(data)}
    }).done(function (data) {
        $("#tabellaOrdineCliente").html(data).promise().done(function () {
            let tab = new Tabella("Ordine");
            tab.caricatabella();
        });
    });
}
function loadSubTabellaMagazzino() {
//        var url = Routing.generate("Magazzino_indexdettaglio");
    var url = "/Magazzino/indexDettaglio";
    var clienteid = $('#tabellaMagazzinoCliente').data('clienteid');
    var clientenominativo = $('#tabellaMagazzinoCliente').data('clientenominativo');
    var data = {
        prefiltri: [{nomecampo: "Magazzino.Ordine.Cliente.id", operatore: "=", valore: clienteid}],
        modellocolonne: [{nomecampo: 'Magazzino.giornodellasettimana', escluso: false, decodifiche: ["Domenica", "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato"]}],
        titolotabella: 'Roba in magazzino del cliente clientenominativo'};
    $.ajax({
        type: 'POST',
        url: url,
        data: {parametripassati: JSON.stringify(data)}
    }).done(function (data) {
        $("#tabellaMagazzinoCliente").html(data).promise().done(function () {
            let tab = new Tabella("Magazzino");
            tab.caricatabella();
        });
    });
}