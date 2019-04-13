jQuery(document).ready(function () {
    bootbox.setDefaults({
        locale: "it"
    });
});

function formlabeladjust()
{
    jQuery('.form-group label').each(function (index, object) {
        var fieldtowakeup = jQuery(object).attr("for");
        if (jQuery("#" + fieldtowakeup).val() || jQuery("#" + fieldtowakeup).is('select')) {
            jQuery(object).addClass("active");
        }
    });
    jQuery(function () {
        jQuery('.bidatepicker').datetimepicker({
            locale: 'it',
            format: 'L'
        });
        jQuery('.bidatetimepicker').datetimepicker({
            locale: 'it'
        });

        //Per impostare il layout delle select come bootstrapitalia
        jQuery(".bootstrap-select-wrapper select").selectpicker('refresh');

    });
}

window.formlabeladjust = formlabeladjust;

const BiBootstrap = {
    formlabeladjust

};
export default BiBootstrap;
