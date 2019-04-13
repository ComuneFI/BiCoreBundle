function openloaderspinner()
{
    jQuery("#spinnerloader").addClass("is-active");
}
function closeloaderspinner()
{
    jQuery("#spinnerloader").removeClass("is-active");
}

const BiSpinner = {
    openloaderspinner,
    closeloaderspinner
};
export default BiSpinner;