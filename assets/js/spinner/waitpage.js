function openloaderspinner()
{
    jQuery("#spinnerloader").addClass("is-active");
}
function closeloaderspinner()
{
    jQuery("#spinnerloader").removeClass("is-active");
}

const Spinner = {
    openloaderspinner,
    closeloaderspinner
};
export default Spinner;