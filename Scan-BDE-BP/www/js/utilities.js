var scanner = null;
var cameras = []; // Contient les cameras possible
var currentCam = null;
var lightState = false;
var lastScan = false; // Timestamp du dernier scan

function changeCamera() {
    currentCam = (currentCam + 1) % cameras.length;
    // On demande la permission pour utiliser la caméra
    navigator.mediaDevices.getUserMedia({
        video: {
            deviceId: cameras[currentCam].id
        }
    })
    scanner.start(cameras[currentCam]);
}

function initiateCamera() {
    scanner = new Instascan.Scanner({
        video: document.getElementById('preview'),
        mirror: false
    });
    // Callback émis quand un QR Code est scanné
    scanner.addListener('scan', function (text) {
        if (lastScan === false || Date.now() - lastScan > 3000) { // 3 sec
            picture(); // Retour utilisateur
            getInfos(text, ($("#autoValid")[0].checked) ? 1 : 0);
            lastScan = Date.now(); // Pour éviter le spam
        }
    });

    navigator.mediaDevices.enumerateDevices().then(devices => {
        cams = devices.filter((device) => device.kind === 'videoinput');

        cams.forEach(element => {
            // Création d'un Objet camera reconnaissable par Instascan
            cameras.push(cam = new Instascan.Camera(element.deviceId));
        });

        // Initialisation de la lecture des QR Code
        currentCam = cameras.length - 1;
        scanner.start(cameras[currentCam]);

        // Pour utiliser la lampe torch
        if (cameras.length != 0) {
            navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: cams[cams.length - 1].deviceId,
                    facingMode: ['user', 'environment'],
                    height: {
                        ideal: 1080
                    },
                    width: {
                        ideal: 1920
                    }
                }
            }).then(stream => {
                const track = stream.getVideoTracks()[0];

                // On récupère les capacité de la camera
                const imageCapture = new ImageCapture(track)
                const photoCapabilities = imageCapture.getPhotoCapabilities().then(() => {

                    // Si le #switch est cliqué on allume la lampe
                    const btn = document.querySelector('#switch');
                    btn.addEventListener('click', function () {
                        lightState = !lightState;
                        track.applyConstraints({
                            advanced: [{
                                torch: lightState
                            }]
                        });
                    });
                });
            });
        } else {
            console.error('No cameras found.');
        }
    });
}

// Transform un boutton en checkbox
$('.button-checkbox').each(function () {
    // Settings
    var $widget = $(this),
        $button = $widget.find('button'),
        $checkbox = $widget.find('input:checkbox'),
        color = $button.data('color'),
        settings = {
            on: {
                icon: 'fa-check-square'
            },
            off: {
                icon: 'far fa-square'
            }
        };

    // Gestion d'événement
    $button.on('click', function () {
        $checkbox.prop('checked', !$checkbox.is(':checked'));
        $checkbox.triggerHandler('change');
        updateDisplay();
    });
    $checkbox.on('change', function () {
        updateDisplay();
    });

    // Actions
    function updateDisplay() {
        var isChecked = $checkbox.is(':checked');

        // Modify l'état du boutton
        $button.data('state', (isChecked) ? "on" : "off");

        // Change l'icon du boutton
        $button.find('.fa')
            .removeClass()
            .addClass('fa ' + settings[$button.data('state')].icon);

        // Change la couleur du boutton
        if (isChecked) {
            $button
                .removeClass('btn-default')
                .addClass('btn-' + color + ' active');
        } else {
            $button
                .removeClass('btn-' + color + ' active')
                .addClass('btn-default');
        }
    }

    // Initialisation
    function init() {
        updateDisplay();
        // Ajout de l'icon
        if ($button.find('.fa').length == 0) {
            $button.prepend('<i class="fa ' + settings[$button.data('state')].icon + '"></i> ');
        }
    }
    init();
});

function sendPOST(url, data, callback) {
    $.ajax({
        type: 'POST',
        dataType: "json",
        url: url,
        data: data,
        success: function (msg) {
            callback(msg);
        }
    });
}

function checkAuthKeyIsValid(successCallback, failCallback) {
    if (localStorage.auth_key && localStorage.auth_key != "none") {
        sendPOST('https://auth.bde-bp.fr/authkey.php', {
            'key': localStorage.auth_key
        }, function (data) {
            if (!data['verif_key']) { // Current key is outdated
                localStorage.auth_key = "none";
                authkey = "none";
                failCallback();
            } else { // Key is still valid
                authkey = localStorage.auth_key;
                $("#deco").removeClass("d-none");
                $("#connexion").addClass("d-none");
                successCallback();
            }
        });
    } else {
        failCallback();
    }
}

function fillInfos(data) {
    // Si il n'y a pas de champs 'idi' on considère le QR Code comme non valide
    if (!data.hasOwnProperty("idi")) {
        alert("Le QR Code scanné n'est pas valide");
        return;
    }

    $("#idi").html(data["idi"]);

    $("#name").html(data["payer_infos"]["nom"]);
    $("#surname").html(data["payer_infos"]["prenom"]);
    $("#classe").html(data["payer_infos"]["classe"]);
    $("#email").html(data["payer_infos"]["email"]);
    $("#auth").html((data["payer_infos"]["auth"]) ? '<i class="fa fa-check-circle" style="color:#008000;"></i>' : '<i class="fa fa-times-circle" style="color:#C82333;"></i>');
    $("#ida").html(data["payer_infos"]["ida"]);
    $("#otherInfosPayer").html("");

    for (let [key, value] of Object.entries(data["payer_infos"]["custom_infos"])) {
        $("#otherInfosPayer").append("<tr><th>" + key + "</th><td>" + value + "</td></tr>");
    }

    $("#type").html(data["order_infos"]["tarif"]);
    $("#otherInfos").html("");

    for (let [key, value] of Object.entries(data["order_infos"]["custom_infos"])) {
        $("#otherInfos").append("<tr><th>" + key + "</th><td>" + value + "</td></tr>");
    }

    $("#pType").html(data["paiement_infos"]["type"]);
    $("#pId").html(data["paiement_infos"]["paiement_id"]);
    $("#status").html(data["paiement_infos"]["status"]);
    $("#price").html(data["paiement_infos"]["prix"]);

    // WAITING  CONFIRMED_HELLOASSO  CONFIRMED_CB_MANUAL  CONFIRMED_OTHER_MANUAL
    switch (data["paiement_infos"]["status"]) {
        case "CONFIRMED_HELLOASSO":
            $("#pStatus").html('<i class="fa fa-robot"></i><i class="fa fa-check-circle" style="color:#008000;"></i>');
            break;
        case "CONFIRMED_CB_MANUAL":
            $("#pStatus").html('<i class="fa fa-credit-card"></i><i class="fa fa-check-circle" style="color:#008000;"></i>');
            break;
        case "CONFIRMED_OTHER_MANUAL":
            $("#pStatus").html('<i class="fa fa-hand-holding-usd"></i><i class="fa fa-check-circle" style="color:#008000;"></i>');
            break;
        default: // Si WAITING ou autre
            $("#pStatus").html('<i class="fa fa-hourglass-half"></i><i class="fa fa-times-circle" style="color:#C82333;"></i>');
    }

    $("#validateButton").removeClass("btn-success");
    $("#validateButton").removeClass("btn-warning");
    $("#validateButton").removeClass("btn-danger");
    $("#validateButton").data("idi", data["idi"]);

    switch (data["validation_status"]) {
        case "JUST":
            $("#validateButton").html("Validé");
            $("#validateButton").addClass("btn-success");
            $("#validateButton").data("status", "just");
            break;
        case "ALREADY":
            $("#validateButton").html("Déjà validé");
            $("#validateButton").addClass("btn-danger");
            $("#validateButton").data("status", "already");
            break;
        default: // Si NOT ou autre chose
            $("#validateButton").html("À valider");
            $("#validateButton").addClass("btn-warning");
            $("#validateButton").data("status", "not");
    }
}

function picture() {
    if (!navigator.vibrate(50)) {
        // Si on n'a pas pu faire vibrer on fais une animation du body avec un passage au blanc
        $("body").css("backgroundColor", "#ffffff")
            .animate({
                backgroundColor: "transparent"
            }, 100, null, function () {
                $("body").css("backgroundColor", "transparent");
            });
    }
}