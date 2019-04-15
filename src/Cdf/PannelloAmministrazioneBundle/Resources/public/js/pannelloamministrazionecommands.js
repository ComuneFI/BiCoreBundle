'use strict';

$(document).ready(function () {
    $("#adminpanelcc").click(function () {
        var domanda = "Vuoi pulire tutte le cache?";
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_clearcache"));
    });
    $("#adminpanelvcs").click(function () {
        var vcs = this.dataset["vcs"];
        var domanda = "Vuoi prendere l'ultima versione dei sorgenti dal server " + vcs + "?";
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_getVcs"));
    });

    //PHPUNIT
    $("#adminpanelphpunittest").click(function () {
        var domanda = "Vuoi eseguire tutti i test unitari?";
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_phpunittest"));
    });

    $("#adminpanelunixcommand").click(function () {
        var unixcommand = $("#unixcommand").val();
        if (unixcommand.trim().length <= 0) {
            bootbox.alert({
                size: "medium",
                closeButton: false,
                title: '<div class="alert alert-warning" role="alert">Attenzione</div>',
                message: "Specificare un comando valido"
            });
            return false;
        }

        var domanda = "Vuoi eseguire il comando unix: " + unixcommand;
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_unixcommand"), {unixcommand: unixcommand});
    });

    $("#adminpanelgenerateentity").click(function () {
        var entityfile = $("#entityfile").val();
        if (!entityfile) {
            bootbox.alert({
                size: "medium",
                closeButton: false,
                title: '<div class="alert alert-warning" role="alert">Attenzione</div>',
                message: "Specificare un modello mysqlworkbench"
            });
            return false;
        }
        var domanda = "Vuoi creare i fle di configurazione per le entità partendo dal file: " + entityfile;
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_generateentity"), {file: entityfile});
    });

    $("#adminpanelgenerateformcrud").click(function () {
        var entityform = $("#entityform").val();
        if (!entityform) {
            bootbox.alert({
                size: "medium",
                closeButton: false,
                title: '<div class="alert alert-warning" role="alert">Attenzione</div>',
                message: "Specificare una entity"
            });
            return false;
        }
        var generatemplate = $("#generatemplate").prop("checked");
        var domanda = "Vuoi creare il crud per il form " + entityform;
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_generateformcrud"), {entityform: entityform, generatemplate: generatemplate});
    });

    $("#adminpanelaggiornadatabase").click(function () {
        var domanda = "Vuoi aggiornare il database partendo dalla definizione dalle entità esistenti";
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_aggiornaschemadatabase"));
    });

    $("#adminpanelsymfonycommand").click(function () {
        var symfonycommand = $("#symfonycommand").val();
        if (symfonycommand.trim().length <= 0) {
            bootbox.alert({
                size: "medium",
                closeButton: false,
                title: '<div class="alert alert-warning" role="alert">Attenzione</div>',
                message: "Specificare un comando valido"
            });
            return false;
        }
        var domanda = "Vuoi eseguire il comando " + symfonycommand;
        Pannelloamministrazione.eseguicomando(domanda, Routing.generate("fi_pannello_amministrazione_symfonycommand"), {symfonycommand: symfonycommand});
    });
});