import Spinner from "../spinner/waitpage.js";
import BiStringFunctions from "../functions/string.js";
import BiNotification from "../notification/notification.js";
import BiAlert from "../modal/alertbuilder.js";
import bootbox from 'bootbox';
require('jquery-contextmenu');

class Tabella {

    constructor(nometabella) {
        this.nometabella = nometabella;
        this.parametri = this.getParametriTabellaDataset(nometabella);
    }
    static getMainTabella()
    {
        var nomecontroller = document.querySelector('.main-tabella').dataset["nomecontroller"];
        return nomecontroller;
    }
    _caricatabellaStart()
    {
        $('[data-toggle="tooltip"]').tooltip('disable');
        $('[data-toggle="tooltip"]').tooltip('dispose');
        $('[data-toggle="popover"]').popover('dispose');
    }
    _caricatabellaComplete()
    {
        //Sistema label e input per form inserimento/modifica
        this._tabellaAdjust();
        //Genera menu per edit e delete
        this._generatemenuconfirmation(this.parametri);
        //Abilita tooltip
        $(function () {
            $('[data-toggle="popover"]').popover({container: 'body'});
            $('[data-toggle="tooltip"]').tooltip('enable');
        });
        //Per impostare il layout delle select come bootstrapitalia
        $(".bootstrap-select-wrapper select").selectpicker('refresh');
        //Sistema i footer delle tabelle per i campi generati dinamicamente
        var colCount = 0;
        var nometabella = BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller);
        $('#tabella' + nometabella + ' tr:nth-child(2) th').each(function () {
            if ($(this).attr('colspan')) {
                colCount += +$(this).attr('colspan');
            } else {
                colCount++;
            }
        });
        $("#bitraduzionefiltri" + nometabella).attr("colspan", colCount);
        $("#bitoolbarbottoni" + nometabella).attr("colspan", colCount);
        $("#bititletable" + nometabella).attr("colspan", colCount);
        //Se si hanno i permessi per update si abilita il doppioclick per scatenare l'edir
        var permessi = JSON.parse(BiStringFunctions.getTabellaParameter(this.parametri.permessi));
        if (permessi.update === true) {
            //Doppio click su riga per edit
            var tabellaclass = this;
            $('#table' + this.nometabella + ' > tbody > tr').dblclick(function () {
                var biid = this.dataset['bitableid'];
                if (biid) {
                    tabellaclass.modificarecord(biid);
                }
            });
        }
        //Se si preme si selectall seleziona tutti i record di una determinata tabellas
        $(document).on("click", ".bitabellacheckboxselectall", function (e) {
            var table = $(this).closest("table");
            $("#" + $(table).attr("id") + " > tbody > tr .biselecttablerow").prop("checked", $(this).prop("checked"));
        });
        //Si imposta il submit per il pulsante salva della form
        this._submitHandler();
    }
    _generateForm(formhtml, callback) {
        var tabellaclass = this;
        $('#' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
        var form = document.getElementById('formdati' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller));
        $(form).replaceWith(formhtml).promise().done(function () {
            tabellaclass._submitHandler();
            tabellaclass._tabellaAdjust();
            typeof callback == "function" && callback();
        });
    }
    _submitHandler() {
        var tabellaclass = this;
        //Gestione Submit
        //console.log(BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller));
        var form = document.getElementById('formdati' + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller));
        if (form) {
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
                            tabellaclass._tabellaAdjust();
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
        }
    }
    aggiungirecord(callback)
    {

        var tabellaclass = this;
        if (BiStringFunctions.getTabellaParameter(this.parametri.editinline) === 1) {
            var elencocampinuovariga = $("#table" + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='0'] input");
            var nuovariga = elencocampinuovariga.closest("tr");
            nuovariga.removeClass("sr-only");
            this.abilitainputinline(elencocampinuovariga, 0);
        } else {
            var parametriform;
            if (typeof this.parametri.parametriform !== "undefined") {
                parametriform = BiStringFunctions.getTabellaParameter(this.parametri.parametriform);
            }

            var newurl = BiStringFunctions.getTabellaParameter(this.parametri.baseurl) + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + "/new";
            $.ajax({
                url: newurl,
                type: "GET",
                data: {parametriform: parametriform},
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
                    tabellaclass._generateForm(response);
                    $('.nav-tabs a[href="#tab' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + '2a"]').tab('show');
                    typeof callback == "function" && callback();
                }
            });
        }
    }
    modificarecord(biid, callback)
    {
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
                    tabellaclass._generateForm(response);
                    $('.nav-tabs a[href="#tab' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + '2a"]').tab('show');
                    typeof callback == "function" && callback();
                }
            });
        }
    }
    cancellarecord(biid, callback)
    {
        var tabellaclass = this;
        bootbox.confirm({
            message: "Sei sicuro di voler cancellare?",
            buttons: {
                cancel: {
                    className: 'btn btn-default biconfirmno',
                    label: '<i class="fa fa-times"></i> Annulla'
                },
                confirm: {
                    className: 'btn btn-primary biconfirmyes',
                    label: '<i class="fa fa-check"></i> Si'
                }
            },
            callback: function (confirm) {
                if (confirm) {
                    var token = $("#table" + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller)).attr("data-tabletoken");
                    var deleteturl = BiStringFunctions.getTabellaParameter(tabellaclass.parametri.baseurl) + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + "/" + biid + "/" + token + "/delete";
                    $.ajax({
                        url: deleteturl,
                        type: "POST",
                        async: true,
                        error: function (xhr, textStatus, errorThrown) {
                            if (xhr.status === 501) {
                                bootbox.alert({
                                    size: "large",
                                    closeButton: false,
                                    title: '<div class="alert alert-warning" role="alert">Attenzione</div>',
                                    message: BiAlert.showErrori("Ci sono informazioni legate a questo elemento, impossibile eliminare")
                                });
                                return false;
                            } else {
                                bootbox.alert({
                                    size: "large",
                                    closeButton: false,
                                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                                    message: BiAlert.showErrori(xhr.responseText)
                                });
                                return false;
                            }
                        },
                        success: function (response) {
                            tabellaclass.caricatabella(callback);
                            BiNotification.show("Eliminato", "warning", "it-error");
                        }
                    });
                }
            }
        });
    }
    eliminaselezionati(callback)
    {
        var tabellaclass = this;
        var permessi = JSON.parse(BiStringFunctions.getTabellaParameter(this.parametri.permessi));
        if (permessi.update !== true) {
            BiNotification.show("Non si dispongono dei diritti per eliminare questo elemento", "warning", "it-error");
            return false;
        }
        var token = $("#table" + this.nometabella).attr("data-tabletoken");
        var recordsdacancellareids = $("#table" + tabellaclass.nometabella + " > tbody > tr .biselecttablerow").map(function () {
            if ($(this).prop("checked") === true) {
                return parseInt(this.dataset['bitableid']);
            }
        }).get();
        if (recordsdacancellareids.length > 0) {
            bootbox.confirm({
                message: "Sei sicuro di voler cancellare gli elementi selezionati?",
                buttons: {
                    cancel: {
                        className: 'btn btn-default biconfirmno',
                        label: '<i class="fa fa-times"></i> Annulla'
                    },
                    confirm: {
                        className: 'btn btn-primary biconfirmyes',
                        label: '<i class="fa fa-check"></i> Si'
                    }
                },
                callback: function (confirm) {
                    if (confirm) {
                        var deleteturl = BiStringFunctions.getTabellaParameter(tabellaclass.parametri.baseurl) + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + "/" + token + "/delete";
                        $.ajax({
                            url: deleteturl,
                            type: "POST",
                            async: true,
                            data: {id: recordsdacancellareids.join(",")},
                            error: function (xhr, textStatus, errorThrown) {
                                if (xhr.status === 501) {
                                    bootbox.alert({
                                        size: "large",
                                        closeButton: false,
                                        title: '<div class="alert alert-warning" role="alert">Attenzione</div>',
                                        message: BiAlert.showErrori("Ci sono informazioni legate a questo elemento, impossibile eliminare")
                                    });
                                    return false;
                                } else {
                                    bootbox.alert({
                                        size: "large",
                                        closeButton: false,
                                        title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                                        message: BiAlert.showErrori(xhr.responseText)
                                    });
                                    return false;
                                }
                            },
                            beforeSend: function (xhr) {

                            },
                            success: function (response) {
                                tabellaclass.caricatabella(callback);
                                BiNotification.show("Elementi eliminati", "warning", "it-error");
                            }
                        });
                    }
                }
            });
        } else {
            BiNotification.show("Selezionare almeno un elemento", "warning", "it-error");
            return false;
        }

    }
    abilitainputinline(elencocampi, biid) {
        var tabellaclass = this;
        elencocampi.each(function (index, object) {
            var fieldname = object.closest("td").dataset["nomecampo"];
            var fieldtype = object.closest("td").dataset["tipocampo"];
            var editable = object.closest("td").dataset["editabile"];
            var soggettoadecodifica = object.closest("td").dataset["soggettoadecodifica"];
            var decodifiche;
            var modellocolonne = JSON.parse(BiStringFunctions.getTabellaParameter(tabellaclass.parametri.modellocolonne));
            if (soggettoadecodifica) {
                $(modellocolonne).each(function (colidx, colobj) {
                    if (colobj.nomecampo == fieldname) {
                        if (typeof colobj.decodifiche !== "undefined") {
                            decodifiche = colobj.decodifiche;
                            fieldtype = "decodifiche";
                        }
                    }
                });
            } else {
                fieldtype = object.closest("td").dataset["tipocampo"];
            }
            var input;
            var div = $('<div />', {class: 'form-group'});
            $("#table" + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").removeClass("sr-only");
            $("#table" + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + "[data-biid='" + biid + "']").addClass("sr-only");
            if (fieldname && editable == true) {
                if (fieldname == BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller) + '.id' || fieldname.split(".").length > 2) {
                    //Id e campi di tabelle collegate non devono essere modificabili
                    input = $('<input />', {type: 'text', class: 'form-control', value: $(object).val(), disabled: true});
                } else {
                    //fieldvalue = $(object).val();
                    switch (fieldtype) {
                        case 'decodifiche':
                            input = tabellaclass.riempiselectdecodifiche(fieldname, decodifiche, $(object).val());
                            break;
                        case 'boolean':
                            input = $('<input />', {type: 'checkbox', class: 'form-control'});
                            if ($(object).val() == 'SI') {
                                input.attr("checked", true);
                            } else {
                                input.attr("checked", false);
                            }
                            break;
                        case 'join':
                            var jointableid = object.closest("td").dataset["idtabella"];
                            input = tabellaclass.riempiselect(fieldname, jointableid);
                            break;
                            /*case 'date':
                             input = $('<input />', {type: 'text', class: 'bidatepicker form-control', value: $(object).val()});
                             break;
                             case 'datetime':
                             input = $('<input />', {type: 'text', class: 'bidatetimepicker form-control', value: $(object).val()});
                             break;*/
                        default:
                            $(object).attr("disabled", false);
                            //input = object;
                            input = $('<input />', {type: 'text', class: 'form-control', value: $(object).val()});
                            break;
                    }
                }
            } else {
                input = $(object).clone().attr("disabled", true);
            }
            $(input).appendTo(div);
            $(object).closest("td").html(div);
            tabellaclass._tabellaAdjust();
        });
    }
    getDateTimeTabella(stringadata)
    {
        var date = new Date(stringadata.replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$3-$2-$1"));
        var tzoffset = date.getTimezoneOffset() * 60000; //offset in milliseconds
        var localISOTime = (new Date(date - tzoffset)).toISOString().slice(0, -1);
        return localISOTime;
    }
    getParametriTabellaDataset()
    {
        return document.querySelector('#Parametri' + this.nometabella + '.parametri-tabella').dataset;
    }
//Funzione per modificare il valore di un parametro della tabella
    setDataParameterTabella(parametro, valore)
    {
        var divparametri = this.getParametriTabellaDataset(this.nometabella);
        divparametri[parametro] = BiStringFunctions.setTabellaParameter(valore);
    }
//Funzione per prendere il valore di un parametro della tabella
    getDataParameterTabella(parametro)
    {
        var divparametri = this.getParametriTabellaDataset(this.nometabella);
        return divparametri[parametro];
    }
    download() {
        var url = BiStringFunctions.getTabellaParameter(this.parametri.baseurl) + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + '/exportxls';
        $.ajax({
            type: 'POST',
            url: url,
            parametri: this.parametri,
            data: {parametri: this.parametri},
            dataType: 'json'
        }).done(function (data) {
            if (data.status == '200') {
                var $a = $("<a>");
                $a.attr("href", data.file);
                $("body").append($a);
                $a.attr("download", BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller) + ".xls");
                $a[0].click();
                $a.remove();
            } else {
                bootbox.alert({
                    size: "large",
                    closeButton: false,
                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                    message: BiAlert.showErrori(data.file)
                });
                return false;
            }
        });
    }
    caricatabella(callback) {
        this._caricatabellaStart();
        Spinner.show();
        var tabellaclass = this;
        $.ajax({
            url: BiStringFunctions.getTabellaParameter(this.parametri.urltabella),
            type: "POST",
            parametri: this.parametri,
            async: true,
            data: {parametri: this.parametri},
            error: function (xhr, textStatus, errorThrown) {
                bootbox.alert({
                    size: "large",
                    closeButton: false,
                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                    message: BiAlert.showErrori(xhr.responseText)
                });
                Spinner.hide();
                return false;
            },
            beforeSend: function (xhr) {

            },
            success: function (response) {
                $('#tabella' + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller)).html(response);
                tabellaclass._caricatabellaComplete();
                typeof callback == "function" && callback();
                Spinner.hide();
            }
        });
    }
    __dumpParametriTabella()
    {
        var parametri = document.querySelector('#Parametri' + this.nometabella + '.parametri-tabella');
        $.each(parametri.dataset, function (key, value) {
            console.log(key + ":" + BiStringFunctions.getTabellaParameter(value));
        });
    }
    reseteditinline(inputs) {
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
                var obj = $('<input />', {type: 'text', class: 'form-control', value: $(object).find('option:selected').text(), disabled: true});
                $(div).remove();
                $(td).html(obj);
            } else {
                $(div).remove();
                $(object).appendTo(td);
            }
        });
        $(".biselecttablerow").attr("disabled", false);
    }
    riempiselect(fieldname, selectedoption) {
        var fieldpieces = fieldname.split(".");
        var nomecontroller = BiStringFunctions.ucfirst(fieldpieces[1]);
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
                    message: BiAlert.showErrori(xhr.responseText)
                });
                return false;
            },
            success: function (risposta) {
                //Prende la risposta ed alimenta la select
                var div1 = $('<div/>', {class: 'bootstrap-select-wrapper'});
                var div2 = $('<div/>', {class: 'dropdown bootstrap-select'});
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
    riempiselectdecodifiche(fieldname, decodifiche, selectedoption) {
        var fieldpieces = fieldname.split(".");
        var nomecontroller = BiStringFunctions.ucfirst(fieldpieces[1]);
        var select;
        var div1 = $('<div/>', {class: 'bootstrap-select-wrapper'});
        var div2 = $('<div/>', {class: 'dropdown bootstrap-select'});
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
    _tabellaAdjust()
    {
        //Sistema le label dei campi imput per evitare di venire sovrapposti
        $('.form-group label').each(function (index, object) {
            var fieldtowakeup = $(object).attr("for");
            if ($("#" + fieldtowakeup).val() || $("#" + fieldtowakeup).is('select')) {
                $(object).addClass("active");
            }
        });
        // Sistema correttamente i widget per i datetimepicker e select in stile boostrapitalia

        $('.bidatepicker').datetimepicker({
            locale: 'it',
            format: 'L'
        });
        $('.bidatetimepicker').datetimepicker({
            locale: 'it'
        });
        //Per impostare il layout delle select come bootstrapitalia
        $(".bootstrap-select-wrapper select").selectpicker('refresh');
    }
    _generatemenuconfirmation()
    {
        var bottoni = this._getContextmenuButtons();
        var permessi = JSON.parse(BiStringFunctions.getTabellaParameter(this.parametri.permessi));
        //var multiselezione = JSON.parse(BiStringFunctions.getTabellaParameter(this.parametri.multiselezione));
        var tabellaclass = this;
        if (bottoni.length > 0) {
            $('[data-toggle=confirmation-popout].bibottonimodificatabella' + BiStringFunctions.getTabellaParameter(this.parametri.nomecontroller)).confirmation({
                rootSelector: '[data-toggle=confirmation-popout]',
                title: 'Cosa vuoi fare?',
                popout: true,
                onConfirm: function (operazione) {
                    //Sul menu Cancella
                    var biid = this.dataset["biid"];
                    if (operazione === "delete") {
                        tabellaclass.cancellarecord(biid);
                    }
                    //Sul menu Modifica
                    if (operazione === "edit") {
                        tabellaclass.modificarecord(biid);
                    }
                },
                onCancel: function () {
                    //alert('You didn\'t choose anything');
                },
                buttons: bottoni
            });
        } else {
            $('[data-toggle=confirmation-popout].bibottonimodificatabella' + BiStringFunctions.getTabellaParameter(tabellaclass.parametri.nomecontroller)).hide();
        }
        $.contextMenu({
            selector: '.context-menu-crud',
            callback: function (key, options) {
                switch (key) {
                    /*case "selezionatutti":
                        //Sul menu Modifica
                        var table = options.$trigger.closest("table");
                        $("#" + $(table).attr("id") + " > tbody > tr .biselecttablerow").prop("checked", true);
                        break;*/
                    case "modifica":
                        //Sul menu Modifica
                        var biid = options.$trigger.attr("data-bitableid");
                        tabellaclass.modificarecord(biid);
                        break;
                    case "cancella":
                        var biid = options.$trigger.attr("data-bitableid");
                        tabellaclass.cancellarecord(biid);
                        break;
                }
            },
            items: {
                "modifica": {name: "Modifica", icon: "edit", disabled: permessi.update === false},
                "cancella": {name: "Cancella", icon: "delete", disabled: permessi.delete === false}/*,
                 "selezionatutti": {name: "Seleziona tutti", icon: "copy", disabled: multiselezione === 0}*/
            }
        });
    }
    _getContextmenuButtons()
    {
        var editbutton = {
            label: 'Modificare',
            value: 'edit',
            class: 'it-file'
        };
        var deletebutton = {
            label: 'Cancellare',
            value: 'delete',
            class: 'it-cancel'
        };
        var bottoni = new Array();
        var permessi = JSON.parse(BiStringFunctions.getTabellaParameter(this.parametri.permessi));
        if (permessi.update === true) {
            bottoni.push(editbutton);
        }
        if (permessi.delete === true) {
            bottoni.push(deletebutton);
        }
        return bottoni;
    }
}

export default Tabella;
