var scanner = null;
var cameras = [];
var currentCam = null;

function changeCamera() {
    currentCam = (currentCam + 1) % cameras.length;
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
    scanner.addListener('scan', function (text) {
        if (lastScan === false || Date.now() - lastScan > 3000) { // 3 sec
            picture();
            getInfos(text, ($("#autoValid")[0].checked) ? 1 : 0);
            lastScan = Date.now();
        }
    });

    navigator.mediaDevices.enumerateDevices().then(devices => {
        cams = devices.filter((device) => device.kind === 'videoinput');

        cams.forEach(element => {
            cameras.push(cam = new Instascan.Camera(element.deviceId));
        });

        if (cameras.length != 0) {
            // For the light
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

                //Create image capture object and get camera capabilities
                const imageCapture = new ImageCapture(track)
                const photoCapabilities = imageCapture.getPhotoCapabilities().then(() => {

                    //let there be light!
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

            // For the QR code
            currentCam = cameras.length - 1;
            scanner.start(cameras[currentCam]);
        } else {
            console.error('No cameras found.');
        }
    });
}

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

    // Event Handlers
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

        // Set the button's state
        $button.data('state', (isChecked) ? "on" : "off");

        // Set the button's icon
        $button.find('.fa')
            .removeClass()
            .addClass('fa ' + settings[$button.data('state')].icon);

        // Update the button's color
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

    // Initialization
    function init() {

        updateDisplay();

        // Inject the icon if applicable
        if ($button.find('.fa').length == 0) {
            $button.prepend('<i class="fa ' + settings[$button.data('state')].icon + '"></i> ');
        }
    }
    init();
});

function send(url, data, callback) {
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

function fillInfos(data) {
    console.log(data);
    if (!data.hasOwnProperty("idi")) {
        alert("Le QR Code scanné n'est pas valide");
        return;
    }

    $("#idi").html("ID_billet: " + data["idi"]);

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
    if (data["paiement_infos"]["status"] == "WAITING") {
        $("#pStatus").html('<i class="fa fa-hourglass-half"></i><i class="fa fa-times-circle" style="color:#C82333;"></i>');
    } else if (data["paiement_infos"]["status"] == "CONFIRMED_HELLOASSO") {
        $("#pStatus").html('<i class="fa fa-robot"></i><i class="fa fa-check-circle" style="color:#008000;"></i>');
    } else if (data["paiement_infos"]["status"] == "CONFIRMED_CB_MANUAL") {
        $("#pStatus").html('<i class="fa fa-credit-card"></i><i class="fa fa-check-circle" style="color:#008000;"></i>');
    } else if (data["paiement_infos"]["status"] == "CONFIRMED_OTHER_MANUAL") {
        $("#pStatus").html('<i class="fa fa-hand-holding-usd"></i><i class="fa fa-check-circle" style="color:#008000;"></i>');
    } else {
        $("#pStatus").html('<i class="fa fa-hourglass-half"></i><i class="fa fa-times-circle" style="color:#C82333;"></i>');
    }

    $("#validateButton").removeClass("btn-success");
    $("#validateButton").removeClass("btn-warning");
    $("#validateButton").removeClass("btn-danger");
    $("#validateButton").data("idi", data["idi"]);

    if (data["validation_status"] == "JUST") {
        $("#validateButton").html("Validé");
        $("#validateButton").addClass("btn-success");
        $("#validateButton").data("status", "just");
    } else if (data["validation_status"] == "NOT") {
        $("#validateButton").html("À valider");
        $("#validateButton").addClass("btn-warning");
        $("#validateButton").data("status", "not");
    } else if (data["validation_status"] == "ALREADY") {
        $("#validateButton").html("Déjà validé");
        $("#validateButton").addClass("btn-danger");
        $("#validateButton").data("status", "already");
    } else {
        $("#validateButton").html("À valider");
        $("#validateButton").addClass("btn-warning");
        $("#validateButton").data("status", "not");
    }
}

function picture() {
    if (!navigator.vibrate(50)) {
        $("body").css("backgroundColor", "#ffffff")
            .animate({
                backgroundColor: "transparent"
            }, 100, null, function () {
                $("body").css("backgroundColor", "transparent");
            });
    }
}