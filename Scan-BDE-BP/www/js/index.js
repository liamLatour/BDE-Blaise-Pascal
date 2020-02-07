var authkey = 'none';

function getInfos(ID_billet, valid = 0) {
    // console.log(ID_billet);
    // console.log(authkey);

    $('#idiModal').modal('hide');

    if (authkey != "none") {
        checkAuthKeyIsValid(function () {
            sendPOST("https://events.bde-bp.fr/getreg.php", {
                'ID_billet': ID_billet,
                'auth_key': authkey,
                'valid_on_check': valid
            }, fillInfos)
        }, function () {
            $('#pwdModal').modal('show')
        });
    } else {
        // Si il n'y à pas d'authkey ou quelle n'est plus valid
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
    $('#pwdModal').modal('show'); // On redemande tout de suite de se reconnecter
}

function getAuthKey() {
    sendPOST('https://auth.bde-bp.fr/authkey.php', {
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
        sendPOST('https://events.bde-bp.fr/getreg.php', {
            'auth_key': authkey,
            'unvalid': idi
        }, function () {
            getInfos(idi);
        });
    } else if ($("#validateButton").data("status") == "not") {
        sendPOST('https://events.bde-bp.fr/getreg.php', {
            'auth_key': authkey,
            'valid': idi
        }, function () {
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
        // On remonte le flux vidéo pour centrer la camera
        $("#preview").css({
            "top": "-" + (window.innerHeight / 2 - 100) + "px"
        });

        // On demande les permissions pour la camera si on les à pas
        var permissions = cordova.plugins.permissions;
        permissions.hasPermission(permissions.CAMERA, function (status) {
            if (status.hasPermission) {
                initiateCamera();
            } else {
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

        // On vérifie si une authkey à été enregistré
        if (typeof (Storage) !== "undefined") {
            $("#deco").addClass("d-none");
            $("#connexion").removeClass("d-none");
            checkAuthKeyIsValid(null, function(){
                $('#pwdModal').modal('show');
            });
        } else {
            // Il n'y a pas d'authKey enregistré
            $('#pwdModal').modal('show');
        }
    }
};

app.initialize();