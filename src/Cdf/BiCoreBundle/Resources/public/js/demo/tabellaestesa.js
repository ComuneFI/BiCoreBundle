class TabellaCliente extends Tabella {
    editmenu(biid)
    {
        console.log("Class TabellaEstesa");
        var tabellaclass = this;
        if (BiStringFunctions.getTabellaParameter(this.parametri.editinline) == 1) {
            var elencocampi = $("#table" + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] input");
            this.abilitainputinline(elencocampi, biid);

        } else {
            var editurl = BiStringFunctions.getTabellaParameter(this.parametri.baseurl) + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + "/" + biid + "/edit";
            $.ajax({
                url: editurl,
                type: "GET",
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
                    $('#' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
                    var form = document.getElementById('formdati' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller));
                    $(form).replaceWith(response).promise().done(function () {
                        tabellaclass.formlabeladjust();
                        //Gestione Submit
                        var form = document.getElementById('formdati' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller));
                        form.addEventListener("submit", function (e) {
                            e.preventDefault();
                            var form = $(this).closest("form");
                            var formid = $(form).attr('id');
                            //$("#" + formid).children('input[type="submit"]').click()
                            var url = form.attr('action');
                            var formSerialize = form.serialize();
                            var jqxhr = $.post(url, formSerialize, function (xhr) {
                                tabellaclass.caricatabella();
                                BiNotification.show("Registrazione effettuata");
                                //alert("success");
                            }).done(function () {
                                //alert("second success");
                            }).fail(function (xhr, status, error) {
                                //in caso
                                if (xhr.status === 400) {
                                    form.replaceWith(xhr.responseText).promise().done(function () {
                                        tabellaclass.formlabeladjust();
                                    });
                                } else {
                                    bootbox.alert({
                                        size: "large",
                                        closeButton: false,
                                        title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                                        message: BiAlert.showErrori(xhr.responseText)
                                    });
                                }
                            }).always(function () {
                                //alert("finished");
                            });

                            // Perform other work here ...
                            // Set another completion function for the request above
                            jqxhr.always(function () {
                                //alert("second finished");
                            });
                        }, false);
                        $('.nav-tabs a[href="#tab' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + '2a"]').tab('show');
                        estraiSottotabelle();
                    });
                }
            });
        }
    }
}