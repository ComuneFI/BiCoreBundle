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

