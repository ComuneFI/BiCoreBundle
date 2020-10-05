import bootbox from 'bootbox';
import Spinner from '../spinner/waitpage.js';
import BiAlert from '../modal/alertbuilder.js';

class Pannelloamministrazione {
    static eseguicomando(domanda, url, parametri)
    {
        parametri = parametri || {};
        bootbox.confirm({
            message: domanda,
            buttons: {
                cancel: {
                    className: 'btn-secondary biconfirmno',
                    label: '<i class="fas fa-times"></i> Annulla'
                },
                confirm: {
                    className: 'btn-primary biconfirmyes',
                    label: '<i class="fas fa-check"></i> Si'
                }
            },
            callback: function (confirm) {
                if (confirm) {
                    Spinner.show();
                    $.ajax({
                        url: url,
                        data: parametri
                    }).done(function (data) {
                        var boxstatus = $("<div>").attr("role", "alert").attr("class", "alert alert-success alert-dismissible fade show");
                        boxstatus.html('<strong>Operazione conclusa</strong>');
                        bootbox.alert({
                            size: "large",
                            message: $.merge(boxstatus, BiAlert.showMessaggi(data)),
                            buttons: {
                                ok: {
                                    className: 'btn btn-primary biconfirmok',
                                    label: '<i class="fas fa-check"></i> Ok'
                                }
                            }
                        });
                        Spinner.hide();
                    }).fail(function (jqXHR, textStatus) {
                        var boxstatus = $("<div>").attr("role", "alert").attr("class", "alert alert-warning alert-dismissible fade show");
                        boxstatus.html('<strong>Si è verificato un errore</strong>');
                        bootbox.alert({
                            size: "large",
                            closeButton: false,
                            message: $.merge(boxstatus, BiAlert.showErrori(jqXHR.responseText))
                        });
                        Spinner.hide();
                    });
                }
            }
        });
    }
}

export default Pannelloamministrazione;