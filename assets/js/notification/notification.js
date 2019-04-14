import Swal from "sweetalert2";

function show(messaggio, tipo = "success", icon = "it-info-circle") {
    Swal.fire({
        position: 'bottom-end',
        type: tipo,
        title: messaggio,
        showConfirmButton: false,
        timer: 1500
    });
}

const BiNotification = {
    show
};
export default BiNotification;