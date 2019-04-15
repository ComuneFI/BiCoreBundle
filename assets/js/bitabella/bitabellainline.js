'use strict';
import Tabella from "./bitabella.js"

$(document).on("click", '.bibottonieditinline', function (e) {
    var biid = this.closest("tr").dataset["bitableid"];
    var idtabella = $(this).closest("tr").closest("table").attr("id");
    var nomecontroller = this.closest("tr").closest("table").dataset["nomecontroller"];
    var azione = this.dataset["azione"];
    var inputs = $("#" + idtabella + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] :input");

    if (azione === 'conferma') {
        var values = Array();

        inputs.each(function (index, object) {
            var fieldname = object.closest("td").dataset["nomecampo"];
            var fieldtype = object.closest("td").dataset["tipocampo"];
            var disabled = $(object).attr("disabled");
            var fieldvalue;
            if (fieldtype === "boolean") {
                fieldvalue = $(object).is(":checked");
            } else {
                fieldvalue = $(object).val();
            }
            if (fieldname && typeof disabled === "undefined") {
                values.push({fieldname: fieldname, fieldvalue: fieldvalue, fieldtype: fieldtype});
            }
        });

        var token = this.closest("tr").dataset["token"];
        var url = Routing.generate(nomecontroller + '_aggiorna', {id: biid, token: token});
        $.ajax({
            url: url,
            type: "POST",
            data: {values: values},
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
                let tab = new Tabella(nomecontroller);
                tab.reseteditinline(inputs);
                $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
                $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
                tab.caricatabella();

            }
        });


    }
    if (azione === 'annulla') {
        let tab = new Tabella(nomecontroller);
        tab.reseteditinline(inputs);
        $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
        $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
    }
});
