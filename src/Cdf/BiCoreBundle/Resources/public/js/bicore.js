function dumpParametriTabella(nomecontroller)
{
    var parametri = document.querySelector('#' + nomecontroller + '.parametri-tabella');
    $.each(parametri.dataset, function (key, value) {
        console.log(key + ":" + getTabellaParameter(value));
    });
}

function findKeyArrayByValue(obj, val) {
    for (var i in obj) {
        if (obj.hasOwnProperty(i) && obj[i] === val) {
            return i;
        }
    }
    return null;
}