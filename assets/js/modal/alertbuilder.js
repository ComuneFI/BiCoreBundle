function divboxerrori(text)
{
    return $("<div>", {
        "id": "corebundlemodalerror"
    }).css("height", "300px").css("overflow-y", "scroll").css("overflow-x", "hidden").html(text);
}

function divboxmessaggi(text)
{
    return $("<div>", {
        "id": "corebundlemodalinfo"
    }).css("height", "300px").css("overflow-y", "scroll").css("overflow-x", "hidden").html(text);
}

const BiAlertBuilder = {
    divboxerrori,
    divboxmessaggi
};
export default BiAlertBuilder;