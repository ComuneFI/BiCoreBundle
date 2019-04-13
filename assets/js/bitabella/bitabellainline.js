import BiTabella from './bitabella.js';
import BiFunctions from '../functions/string.js';

function reseteditinline(inputs) {
    inputs.each(function (index, object) {
        var td = object.closest("td");
        var fieldtype = td.dataset["tipocampo"];
        var soggettoadecodifica = td.dataset["soggettoadecodifica"];
        var div = object.closest("div.form-group");
        jQuery(object).attr("disabled", true);
        if (fieldtype === 'boolean') {
            if (jQuery(object).is(":checked")) {
                obj = jQuery('<input />', {type: 'text', class: 'form-control', value: 'SI', disabled: true});
            } else {
                obj = jQuery('<input />', {type: 'text', class: 'form-control', value: 'NO', disabled: true});
            }
            jQuery(div).remove();
            jQuery(td).html(obj);
        } else if (fieldtype === 'join' || soggettoadecodifica == 1) {
            obj = jQuery('<input />', {type: 'text', class: 'form-control', value: jQuery(object).find('option:selected').text(), disabled: true});
            jQuery(div).remove();
            jQuery(td).html(obj);
        } else {
            jQuery(div).remove();
            jQuery(object).appendTo(td);
        }
    });
    jQuery(".biselecttablerow").attr("disabled", false);
}

function riempiselect(fieldname, selectedoption) {
    var fieldpieces = fieldname.split(".");
    var nomecontroller = BiFunctions.ucfirst(fieldpieces[1]);
    var url = Routing.generate(nomecontroller + '_lista');
    var select;
    $.ajax({url: url,
        type: "POST",
        async: false,
        dataType: "json",
        error: function (xhr, textStatus, errorThrown) {
            bootbox.alert({
                size: "large",
                closeButton: false,
                title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                message: divboxerrori(xhr.responseText)
            });
            return false;
        },
        success: function (risposta) {
            //Prende la risposta ed alimenta la select
            var div1 = jQuery('<div/>', {class: 'bootstrap-select-wrapper'});
            var div2 = jQuery('<div/>', {class: 'dropdown bootstrap-select'});
            var select = jQuery('<select />', {id: fieldname.replace(".", "_"), class: 'form-control', title: "Seleziona", 'data-live-search': true, 'data-live-search-placeholder': "Cerca..."});
            select.wrap(div2);
            div2.wrap(div1);
            $.each(risposta, function (key, value) {
                if (value.id == selectedoption) {
                    select.append('<option value="' + value.id + '" selected="selected">' + value.descrizione + '</option>');
                } else {
                    select.append('<option value="' + value.id + '">' + value.descrizione + '</option>');
                }
            });
        }
    });
    return select;
}
window.riempiselect = riempiselect;
function riempiselectdecodifiche(fieldname, decodifiche, selectedoption) {
    var fieldpieces = fieldname.split(".");
    var nomecontroller = ucfirst(fieldpieces[1]);

    var select;
    var div1 = jQuery('<div/>', {class: 'bootstrap-select-wrapper'});
    var div2 = jQuery('<div/>', {class: 'dropdown bootstrap-select'});
    var select = jQuery('<select />', {id: fieldname.replace(".", "_"), class: 'form-control', title: "Seleziona", 'data-live-search': true, 'data-live-search-placeholder': "Cerca..."});
    select.wrap(div2);
    div2.wrap(div1);
    $.each(decodifiche, function (key, value) {
        if (value == selectedoption) {
            select.append('<option value="' + key + '" selected="selected">' + value + '</option>');
        } else {
            select.append('<option value="' + key + '">' + value + '</option>');
        }
    });
    return select;
}
window.riempiselectdecodifiche = riempiselectdecodifiche;

jQuery(document).on("click", '.bibottonieditinline', function (e) {
    var biid = this.closest("tr").dataset["bitableid"];
    var idtabella = jQuery(this).closest("tr").closest("table").attr("id");
    var nomecontroller = this.closest("tr").closest("table").dataset["nomecontroller"];
    var azione = this.dataset["azione"];
    var inputs = jQuery("#" + idtabella + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] :input");

    if (azione == 'conferma') {
        var values = Array();

        inputs.each(function (index, object) {
            var fieldname = object.closest("td").dataset["nomecampo"];
            var fieldtype = object.closest("td").dataset["tipocampo"];
            var disabled = jQuery(object).attr("disabled");
            var fieldvalue;
            if (fieldtype == "boolean") {
                fieldvalue = jQuery(object).is(":checked");
            } else {
                fieldvalue = jQuery(object).val();
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
                    message: divboxerrori(xhr.responseText)
                });
                return false;
            },
            beforeSend: function (xhr) {

            },
            success: function (response) {
                reseteditinline(inputs);
                jQuery("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
                jQuery("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
                BiTabella.ricaricatabella(nomecontroller);
            }
        });


    }
    if (azione == 'annulla') {
        reseteditinline(inputs);
        jQuery("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
        jQuery("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
    }
});
