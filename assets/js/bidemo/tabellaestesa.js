import Tabella from "../bitabella/bitabella.js"
import estraiSottotabelle from "./sottotabelle.js"
import BiStringFunctions from "../functions/string.js";
import BiNotification from "../notification/notification.js";
import BiAlert from "../modal/alertbuilder.js";
import bootbox from 'bootbox';
import Swal from 'sweetalert2';



class TabellaCliente extends Tabella {
    caricatabella()
    {
        console.log("caricatabella TabellaEstesa");
        super.caricatabella();
        //super.__dumpParametriTabella();

    }
    aggiungirecord(callback)
    {
        console.log("addrow TabellaEstesa");
        super.aggiungirecord(callback);

    }
    modificarecord(biid, callback)
    {
        console.log("editrow TabellaEstesa");
        super.modificarecord(biid, estraiSottotabelle);
    }
    cancellarecord(biid, callback)
    {
        console.log("deleterow TabellaEstesa");
        super.cancellarecord(biid, function () {
            console.log("Cancellato record " + biid);
        });
    }
    aggiornaselezionati(callback)
    {
        var tabellaclass = this;
        var permessi = JSON.parse(BiStringFunctions.getTabellaParameter(this.parametri.permessi));
        if (permessi.update !== true) {
            BiNotification.show("Non si dispongono dei diritti per eliminare questo elemento", "warning", "it-error");
            return false;
        }
        var token = $("#table" + this.nometabella).attr("data-tabletoken");
        var recordsdaaggiornareids = $("#table" + tabellaclass.nometabella + " > tbody > tr .biselecttablerow").map(function () {
            if ($(this).prop("checked") === true) {
                return parseInt(this.dataset['bitableid']);
            }
        }).get();

        if (recordsdaaggiornareids.length > 0) {
            var dialogcontent = Routing.generate('Cliente_preparazioneaggiornamentomultiplo');
            $.ajax({
                type: 'GET',
                url: dialogcontent,
                context: 'body',
                dataType: 'html',
                success: function (response) {
                    var el = document.createElement('html');
                    el.innerHTML = response;
                    var bodycontent = $(el).find('#selectmultipladiv');
                    var dialog = bootbox.dialog({
                        title: 'Seleziona il campo',
                        closeButton: false,
                        message: bodycontent,
                        size: 'large',
                        buttons: {
                            cancel: {
                                label: "Annulla",
                                className: 'btn-danger',
                                callback: function () {
                                    console.log('Custom cancel clicked');
                                    callback();
                                }
                            },
                            ok: {
                                label: "Conferma",
                                className: 'btn-info',
                                callback: function () {
                                    var urlaggiornamentomultiplo = Routing.generate('Cliente_aggiornamentomultiplo');
                                    var camposelezionato = $("#selectmultipla").val();
                                    var valoreselezionato = $("#selectmultiplainputtext").val();

                                    $.ajax({
                                        type: 'POST',
                                        url: urlaggiornamentomultiplo,
                                        data: {camposelezionato: camposelezionato, valoreselezionato: valoreselezionato, idsselezionati: recordsdaaggiornareids},
                                        context: 'body',
                                        dataType: 'json',
                                        success: function (response) {
                                            if (response.errcode < 0) {
                                                Swal.fire('Oops...', response.message, 'error')
                                            } else {
                                                tabellaclass.caricatabella();
                                                Swal.fire(response.message);
                                            }
                                        }
                                    });
                                    callback();
                                }
                            }
                        }
                    });
                    $(".bootstrap-select-wrapper select").selectpicker('refresh');

                }
            });


        }

    }
}
;

export default TabellaCliente;
