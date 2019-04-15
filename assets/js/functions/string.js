class BiStringFunctions {
    static setTabellaParameter(parametro)
    {
        return window.btoa(parametro);
    }
    static getTabellaParameter(parametro)
    {
        return window.atob(parametro);
    }

    static ucfirst(str, force) {
        str = force ? str.toLowerCase() : str;
        return str.replace(/(\b)([a-zA-Z])/,
                function (firstLetter) {
                    return   firstLetter.toUpperCase();
                });
    }
}

export default BiStringFunctions;

