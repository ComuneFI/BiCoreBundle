class Tabella {

    constructor(nometabella) {
        this.nometabella = nometabella;
        this.parametri = this.getParametriTabellaDataset(nometabella);
    }
    beforeTabellaLoadComplete()
    {
        $('[data-toggle="tooltip"]').tooltip('disable');
        $('[data-toggle="tooltip"]').tooltip('dispose');
        $('[data-toggle="popover"]').popover('dispose');
    }
    aggiustafootertabella()
    {
        var colCount = 0;
        var nometabella = getTabellaParameter(this.parametri.nomecontroller);
        $('#tabella' + nometabella + ' tr:nth-child(2) th').each(function () {
            if ($(this).attr('colspan')) {
                colCount += +$(this).attr('colspan');
            } else {
                colCount++;
            }
        });
        $("#bitraduzionefiltri" + nometabella).attr("colspan", colCount);
        $("#bitollbarbottoni" + nometabella).attr("colspan", colCount);
        $("#bititletable" + nometabella).attr("colspan", colCount);
    }
    editmenu(biid)
    {
        var tabellaclass = this;
        if (getTabellaParameter(this.parametri.editinline) == 1) {
            var elencocampi = $("#table" + getTabellaParameter(this.parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='" + biid + "'] input");
            this.abilitainputinline(elencocampi, biid);

        } else {
            var editurl = getTabellaParameter(this.parametri.baseurl) + getTabellaParameter(this.parametri.nomecontroller) + "/" + biid + "/edit";
            $.ajax({
                url: editurl,
                type: "GET",
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
                    $('#' + getTabellaParameter(tabellaclass.parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
                    var form = $('#formdati' + getTabellaParameter(tabellaclass.parametri.nomecontroller));
                    form.replaceWith(response).promise().done(function () {
                        tabellaclass.formlabeladjust();
                        $('.nav-tabs a[href="#tab' + getTabellaParameter(tabellaclass.parametri.nomecontroller) + '2a"]').tab('show');
                    });
                }
            });
        }
    }
    deletemenu(biid)
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
                    var token = $("#table" + getTabellaParameter(tabellaclass.parametri.nomecontroller)).attr("data-tabletoken");
                    var deleteturl = getTabellaParameter(tabellaclass.parametri.baseurl) + getTabellaParameter(tabellaclass.parametri.nomecontroller) + "/" + biid + "/" + token + "/delete";
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
                                    message: divboxerrori("Ci sono informazioni legate a questo elemento, impossibile eliminare")
                                });
                                return false;
                            } else {
                                bootbox.alert({
                                    size: "large",
                                    closeButton: false,
                                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                                    message: divboxerrori(xhr.responseText)
                                });
                                return false;
                            }
                        },
                        beforeSend: function (xhr) {

                        },
                        success: function (response) {
                            tabellaclass.caricatabella();
                            binotification("Eliminato", "warning", "it-error");
                        }
                    });
                }
            }
        });
    }
    eliminaselezionati()
    {
        let tab = new Tabella(this.nometabella);
        var parametri = tab.getParametriTabellaDataset();
        var permessi = JSON.parse(getTabellaParameter(parametri.permessi));
        if (permessi.update !== true) {
            binotification("Non si dispongono dei diritti per eliminare questo elemento", "warning", "it-error");
            return false;
        }
        var token = $("#table" + this.nometabella).attr("data-tabletoken");
        var recordsdacancellareids = $("#table" + this.nometabella + " > tbody > tr .biselecttablerow").map(function () {
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
                        var deleteturl = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + "/" + token + "/delete";
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
                                        message: divboxerrori("Ci sono informazioni legate a questo elemento, impossibile eliminare")
                                    });
                                    return false;
                                } else {
                                    bootbox.alert({
                                        size: "large",
                                        closeButton: false,
                                        title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                                        message: divboxerrori(xhr.responseText)
                                    });
                                    return false;
                                }
                            },
                            beforeSend: function (xhr) {

                            },
                            success: function (response) {
                                tab.caricatabella();
                                binotification("Elementi eliminati", "warning", "it-error");
                            }
                        });
                    }
                }
            });
        } else {
            binotification("Selezionare almeno un elemento", "warning", "it-error");
            return false;
        }

    }
    aggiungirecord()
    {

        var parametri = this.getParametriTabellaDataset();
        var tabellaclass = this;

        if (getTabellaParameter(parametri.editinline) == 1) {
            var elencocampinuovariga = $("#table" + getTabellaParameter(parametri.nomecontroller) + " > tbody > tr.inputeditinline[data-bitableid='0'] input");
            var nuovariga = elencocampinuovariga.closest("tr");
            nuovariga.removeClass("sr-only");
            this.abilitainputinline(elencocampinuovariga, 0);
        } else {
            var parametriform = [];
            if (typeof parametri.parametriform !== "undefined") {
                parametriform.push(getTabellaParameter(parametri.parametriform));
            }
            var newurl = getTabellaParameter(parametri.baseurl) + getTabellaParameter(parametri.nomecontroller) + "/new";
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
                        message: divboxerrori(xhr.responseText)
                    });
                    return false;
                },
                beforeSend: function (xhr) {

                },
                success: function (response) {
                    $('#' + getTabellaParameter(parametri.nomecontroller) + 'SubTabellaDettagliContainer').remove();
                    var form = $('#formdati' + getTabellaParameter(parametri.nomecontroller));
                    form.replaceWith(response).promise().done(function () {
                        tabellaclass.formlabeladjust();
                        $('.nav-tabs a[href="#tab' + getTabellaParameter(parametri.nomecontroller) + '2a"]').click();
                    });
                }
            });
        }
    }
    afterTabellaLoadComplete()
    {
        //Genera menu per edit e delete
        generatemenuconfirmation(this.parametri);

        //Abilita tooltip bootstrap
        $(function () {
            $('[data-toggle="popover"]').popover({container: 'body'});
            $('[data-toggle="tooltip"]').tooltip('enable');
        });
        this.aggiustafootertabella();

        //Sistema label per form inserimento
        this.formlabeladjust();

        var permessi = JSON.parse(getTabellaParameter(this.parametri.permessi));
        if (permessi.update === true) {
            //Doppio click su riga per edit
            var tabellaclass = this;
            $('#table' + this.nometabella + ' > tbody > tr').dblclick(function () {
                var biid = this.dataset['bitableid'];
                if (biid) {
                    tabellaclass.editmenu(biid);
                }
            });
        }
        $(document).on("click", ".bitabellacheckboxselectall", function (e) {
            var table = $(this).closest("table");
            $("#" + $(table).attr("id") + " > tbody > tr .biselecttablerow").prop("checked", $(this).prop("checked"));
        });
    }
    abilitainputinline(elencocampi, biid) {
        var tabellaclass = this;
        elencocampi.each(function (index, object) {
            var fieldname = object.closest("td").dataset["nomecampo"];
            var fieldtype = object.closest("td").dataset["tipocampo"];
            var editable = object.closest("td").dataset["editabile"];
            var soggettoadecodifica = object.closest("td").dataset["soggettoadecodifica"];
            var decodifiche;
            var modellocolonne = JSON.parse(getTabellaParameter(tabellaclass.parametri.modellocolonne));

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
            $("#table" + getTabellaParameter(tabellaclass.parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonieditinline[data-biid='" + biid + "']").removeClass("sr-only");
            $("#table" + getTabellaParameter(tabellaclass.parametri.nomecontroller) + " > tbody > tr > td.colonnazionitabella a.bibottonimodificatabella" + getTabellaParameter(tabellaclass.parametri.nomecontroller) + "[data-biid='" + biid + "']").addClass("sr-only");
            if (fieldname && editable == true) {
                if (fieldname == getTabellaParameter(tabellaclass.parametri.nomecontroller) + '.id' || fieldname.split(".").length > 2) {
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
            tabellaclass.formlabeladjust();
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
        //console.log(this.nometabella);
        return document.querySelector('#Parametri' + this.nometabella + '.parametri-tabella').dataset;
    }
//Funzione per modificare il valore di un parametro della tabella
    setDataParameterTabella(parametro, valore)
    {
        var divparametri = this.getParametriTabellaDataset(this.nometabella);
        divparametri[parametro] = setTabellaParameter(valore);
    }
//Funzione per prendere il valore di un parametro della tabella
    getDataParameterTabella(parametro)
    {
        var divparametri = this.getParametriTabellaDataset(this.nometabella);
        return divparametri[parametro];
    }
    download() {


        var url = getTabellaParameter(this.parametri.baseurl) + getTabellaParameter(this.parametri.nomecontroller) + '/exportxls';
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
                $a.attr("download", getTabellaParameter(this.parametri.nomecontroller) + ".xls");
                $a[0].click();
                $a.remove();
            } else {
                bootbox.alert({
                    size: "large",
                    closeButton: false,
                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                    message: divboxerrori(data.file)
                });
                return false;
            }
        });
    }
    getMainTabella()
    {
        var nomecontroller = document.querySelector('.main-tabella').dataset["nomecontroller"];
        return nomecontroller;
    }
    caricatabella() {
        this.beforeTabellaLoadComplete();
        openloaderspinner();
        var tabellaclass = this;
        $.ajax({
            url: getTabellaParameter(this.parametri.urltabella),
            type: "POST",
            parametri: this.parametri,
            async: true,
            data: {parametri: this.parametri},
            error: function (xhr, textStatus, errorThrown) {
                bootbox.alert({
                    size: "large",
                    closeButton: false,
                    title: '<div class="alert alert-warning" role="alert">Si è verificato un errore</div>',
                    message: divboxerrori(xhr.responseText)
                });
                closeloaderspinner();
                return false;
            },
            beforeSend: function (xhr) {

            },
            success: function (response) {
                $('#tabella' + getTabellaParameter(this.parametri.nomecontroller)).html(response);
                tabellaclass.afterTabellaLoadComplete();
                closeloaderspinner();
            }
        });
    }
    dumpParametriTabella()
    {
        var parametri = document.querySelector('#Parametri' + this.nometabella + '.parametri-tabella');
        $.each(parametri.dataset, function (key, value) {
            console.log(key + ":" + getTabellaParameter(value));
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
        var nomecontroller = ucfirst(fieldpieces[1]);
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
        var nomecontroller = ucfirst(fieldpieces[1]);

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
    formlabeladjust()
    {
        $('.form-group label').each(function (index, object) {
            var fieldtowakeup = $(object).attr("for");
            if ($("#" + fieldtowakeup).val() || $("#" + fieldtowakeup).is('select')) {
                $(object).addClass("active");
            }
        });
        $(function () {
            $('.bidatepicker').datetimepicker({
                locale: 'it',
                format: 'L'
            });
            $('.bidatetimepicker').datetimepicker({
                locale: 'it'
            });

            //Per impostare il layout delle select come bootstrapitalia
            $(".bootstrap-select-wrapper select").selectpicker('refresh');

        });
    }
}