var lastScan = false;
var authkey = 'none';
var lightState = false;
// https://github.com/bitpay/cordova-plugin-qrscanner

function getInfos(ID_billet, valid = 0) {
    console.log(ID_billet);
    console.log(authkey);

    $('#idiModal').modal('hide');
    if (authkey != "none") {
        send("https://events.bde-bp.fr/getreg.php", {
            'ID_billet': ID_billet,
            'auth_key': authkey,
            'valid_on_check': valid
        }, fillInfos);
    } else {
        $('#pwdModal').modal('show');
    }
}

function deco() {
    if (typeof (Storage) !== "undefined") {
        localStorage.auth_key = "none";
    }
    authkey = "none";
    $("#deco").addClass("d-none");
    $("#connexion").removeClass("d-none");
    $('#pwdModal').modal('show');
}

function getAuthKey() {
    send('https://auth.bde-bp.fr/authkey.php', {
        'id': $("#idInput").val(),
        'password': $("#pwdInput").val()
    }, function (msg) {
        if (msg["auth_key"] == undefined) {
            alert("Mot de passe ou Id incorrect");
        } else {
            if (typeof (Storage) !== "undefined") {
                localStorage.auth_key = msg["auth_key"];
            }

            authkey = msg["auth_key"];
            $("#deco").removeClass("d-none");
            $("#connexion").addClass("d-none");
        }
        $('#pwdModal').modal('hide');
    });
}

function validate() {
    let idi = $("#validateButton").data("idi");
    if ($("#validateButton").data("status") == "just" || $("#validateButton").data("status") == "already") {
        send('https://events.bde-bp.fr/getreg.php', {
            'auth_key': authkey,
            'unvalid': idi
        }, function (msg) {
            getInfos(idi);
        });
    } else if ($("#validateButton").data("status") == "not") {
        send('https://events.bde-bp.fr/getreg.php', {
            'auth_key': authkey,
            'valid': idi
        }, function (msg) {
            getInfos(idi);
        });
    } else {
        alert("No QR-Code scanned");
    }
}

var app = {
    initialize: function () {
        document.addEventListener('deviceready', this.onDeviceReady.bind(this), false);
    },

    onDeviceReady: function () {
        this.receivedEvent('deviceready');
    },

    receivedEvent: function (id) {
        $("#preview").css({
            "top": "-" + (window.innerHeight / 2 - 100) + "px"
        });

        var permissions = cordova.plugins.permissions;
        permissions.hasPermission(permissions.CAMERA, function (status) {
            if (status.hasPermission) {
                initiateCamera();
            }
            else {
                permissions.requestPermission(permissions.CAMERA, success, error);

                function error() {     
                    alert('Please accept the Android permissions.');
                    console.log(codes.error);
                }

                function success(status) {
                    if (status.hasPermission) {
                        initiateCamera();
                    }
                }
            }
        });

        if (typeof (Storage) !== "undefined") {

            $("#deco").addClass("d-none");
            $("#connexion").removeClass("d-none");

            if (localStorage.auth_key && localStorage.auth_key != "none") {
                send('https://auth.bde-bp.fr/authkey.php', {
                    'key': localStorage.auth_key
                }, function (data) {
                    if (!data['verif_key']) {
                        localStorage.auth_key = "none";
                        authkey = "none";
                        $('#pwdModal').modal('show');
                    } else {
                        authkey = localStorage.auth_key;
                        $("#deco").removeClass("d-none");
                        $("#connexion").addClass("d-none");
                    }
                });
            } else {
                $('#pwdModal').modal('show');
            }
        } else {
            $('#pwdModal').modal('show');
        }
    }
};

app.initialize();