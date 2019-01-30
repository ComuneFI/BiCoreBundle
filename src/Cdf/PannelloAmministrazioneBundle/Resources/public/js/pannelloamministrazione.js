$(document).unbind("keyup").keyup(function (e) {
    var code = e.which; // recommended to use e.which, it's normalized across browsers
    if (code == 13) {
        if (currentfunction) {
            e.preventDefault();
            //e.stopPropagation();
            $("#adminpanel" + currentfunction).click();
            currentfunction = "";
        }
    }
});
$(document).ready(function () {
    //Per gestire l'enter
    $("#symfonycommand").focusin(
            function () {
                currentfunction = "symfonycommand";
            }
    );

    $("#unixcommand").focusin(
            function () {
                currentfunction = "unixcommand";
            }
    );
    $("#entityform").focusin(
            function () {
                currentfunction = "";
            }
    );
    $("#entityfile").focusin(
            function () {
                currentfunction = "";
            }
    );

//Abilita tooltip bootstrap
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });


});

$(document).on("click", ".autocomplete-list-text", function (e) {
    e.preventDefault();
    var cmd = "";
    if ($(this).text().indexOf("Label")){
        cmd = $(this).text().slice(0, -5).trim();
    }else{
        cmd = $(this).text().trim();
    }
    
    $(this).closest("div").find(":input").val(cmd);
    $(this).closest("ul").removeClass("autocomplete-list-show");
    $(this).closest("div").find(":input").focus();
});