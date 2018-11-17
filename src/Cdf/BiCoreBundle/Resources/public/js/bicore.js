function dumpParametriTabella()
{
    var parametri = document.querySelector('.parametri-tabella');
    $.each(parametri.dataset, function (key, value) {
        console.log(key + ":" + getTabellaParameter(value));
    });
}
