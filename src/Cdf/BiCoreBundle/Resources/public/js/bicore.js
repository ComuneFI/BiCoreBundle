function dumpParametriTabella(nomecontroller)
{
    var parametri = document.querySelector('#Parametri' + nomecontroller + '.parametri-tabella');
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

function ucfirst(str, force) {
    str = force ? str.toLowerCase() : str;
    return str.replace(/(\b)([a-zA-Z])/,
            function (firstLetter) {
                return   firstLetter.toUpperCase();
            });
}

/* copiata da quella sopra, quindi non so se fa
function lcfirst(str, force) {
    str = force ? str.toUpperCase() : str;
    return str.replace(/(\b)([a-zA-Z])/,
            function (firstLetter) {
                return firstLetter.toLowerCase();
            });
}
*/