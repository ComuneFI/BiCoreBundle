const Swal = require("sweetalert2");

class BiNotification {
    static show(messaggio, tipo = "success") {
        Swal.fire({
            position: 'top-end',
            type: tipo,
            title: messaggio,
            showConfirmButton: false,
            timer: 1500
        });

    }
}

export default BiNotification;