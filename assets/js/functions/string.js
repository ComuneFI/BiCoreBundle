function setTabellaParameter(parametro)
{
    return window.btoa(parametro);
}
function getTabellaParameter(parametro)
{
    return window.atob(parametro);
}

function ucfirst(str, force) {
    str = force ? str.toLowerCase() : str;
    return str.replace(/(\b)([a-zA-Z])/,
            function (firstLetter) {
                return   firstLetter.toUpperCase();
            });
}


const BiFunctions = {
    setTabellaParameter,
    getTabellaParameter,
    ucfirst
};
export default BiFunctions;