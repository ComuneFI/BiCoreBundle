class BiAlert {
    static showErrori(text)
    {
        return $("<div>", {
            "id": "corebundlemodalerror"
        }).css("height", "300px").css("overflow-y", "scroll").css("overflow-x", "hidden").html(text);
    }

    static showMessaggi(text)
    {
        return $("<div>", {
            "id": "corebundlemodalinfo"
        }).css("height", "300px").css("overflow-y", "scroll").css("overflow-x", "hidden").html(text);
    }
}

export default BiAlert;