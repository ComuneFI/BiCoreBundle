$(document).on("keypress", '.inputeditinline input', function (e) {
    var biid = this.closest("tr").dataset["bitableid"];
    var idtabella = $(this).closest("tr").closest("table").attr("id");
    var nomecontroller = this.closest("tr").closest("table").dataset["nomecontroller"];
    /* Ignore tab key */
    var code = e.keyCode || e.which;

    /* Esc */
    if (code == '27') {
        var inputs = $("#" + idtabella + " > tbody > tr.inputeditinline input");
        reseteditinline(inputs);
    }

    /* Invio */
    if (code == '13') {
        var inputs = $("#" + idtabella + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] input");
        var values = Array();
        
        inputs.each(function (index, object) {
            var fieldname = object.closest("td").dataset["nomecampo"];
            var fieldtype = object.closest("td").dataset["tipocampo"];
            fieldvalue = $(object).val();
            if (fieldname) {
                values.push({fieldname: fieldname, fieldvalue: fieldvalue, fieldtype: fieldtype});
            }
        });
        
        var token = this.closest("tr").dataset["token"];
        var url = Routing.generate(nomecontroller + '_aggiorna', {id: biid});
        $.ajax({
            url: url,
            type: "POST",
            data: {values: values, token: token},
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
            }
        });


    }
});

function reseteditinline(inputs) {
    inputs.each(function (index, object) {
        var td = object.closest("td");

        var fieldtype = td.dataset["tipocampo"];
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
        } else {
            $(div).remove();
            $(object).appendTo(td);

        }
    });
    $(".biselecttablerow").attr("disabled", false);
}
