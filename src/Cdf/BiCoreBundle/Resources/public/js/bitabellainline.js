function reseteditinline(inputs) {
    inputs.each(function (index, object) {
        var td = object.closest("td");
        var fieldtype = td.dataset["tipocampo"];
        var soggettoadecodifica = td.dataset["soggettoadecodifica"];
        var div = object.closest("div.form-group");
        $(object).attr("disabled", true);
        if (fieldtype === 'boolean') {
            if ($(object).is(":checked")) {
                obj = $('<input />', {type: 'text', class: 'form-control', value: 'SI', disabled: true});
            } else {
                obj = $('<input />', {type: 'text', class: 'form-control', value: 'NO', disabled: true});
            }
            $(div).remove();
            $(td).html(obj);
        } else if (fieldtype === 'join' || soggettoadecodifica == 1) {
            obj = $('<input />', {type: 'text', class: 'form-control', value: $(object).find('option:selected').text(), disabled: true});
            $(div).remove();
            $(td).html(obj);
        } else {
            $(div).remove();
            $(object).appendTo(td);
        }
    });
    $(".biselecttablerow").attr("disabled", false);
}

function riempiselect(fieldname, selectedoption) {
    fieldpieces = fieldname.split(".");
    nomecontroller = ucfirst(fieldpieces[1]);
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
            div1 = $('<div/>', {class: 'bootstrap-select-wrapper'});
            div2 = $('<div/>', {class: 'dropdown bootstrap-select'});
            select = $('<select />', {id: fieldname.replace(".", "_"), class: 'form-control', title: "Seleziona", 'data-live-search': true, 'data-live-search-placeholder': "Cerca..."});
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

function riempiselectdecodifiche(fieldname, decodifiche, selectedoption) {
    fieldpieces = fieldname.split(".");
    nomecontroller = ucfirst(fieldpieces[1]);

    var select;
    div1 = $('<div/>', {class: 'bootstrap-select-wrapper'});
    div2 = $('<div/>', {class: 'dropdown bootstrap-select'});
    select = $('<select />', {id: fieldname.replace(".", "_"), class: 'form-control', title: "Seleziona", 'data-live-search': true, 'data-live-search-placeholder': "Cerca..."});
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

$(document).on("click", '.bibottonieditinline', function (e) {
    var biid = this.closest("tr").dataset["bitableid"];
    var idtabella = $(this).closest("tr").closest("table").attr("id");
    var nomecontroller = this.closest("tr").closest("table").dataset["nomecontroller"];
    var azione = this.dataset["azione"];
    var inputs = $("#" + idtabella + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] :input");

    if (azione == 'conferma') {
        var values = Array();

        inputs.each(function (index, object) {
            var fieldname = object.closest("td").dataset["nomecampo"];
            var fieldtype = object.closest("td").dataset["tipocampo"];
            var disabled = $(object).attr("disabled");
            fieldvalue = $(object).val();
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
                $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
                $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
            }
        });


    }
    if (azione == 'annulla') {
        reseteditinline(inputs);
        $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").addClass("sr-only");
        $("#table" + nomecontroller + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + nomecontroller + "[data-biid='" + biid + "']").removeClass("sr-only");
    }
});