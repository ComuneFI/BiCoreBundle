class Spinner {
    static show()
    {
        var elem = document.createElement('div');
        elem.setAttribute("class", 'loader loader-default is-active');
        elem.setAttribute("id", 'bispinnerloader');
        document.body.appendChild(elem);
    }
    static hide()
    {
        $("#bispinnerloader").remove();
    }
}