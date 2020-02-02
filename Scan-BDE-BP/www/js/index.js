var lastScan = false;
var authkey = 'none';
var cameras = null;
var currentCam = 0;
var scanner = null;
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

function changeCamera() {
    currentCam = (currentCam + 1) % cameras.length;
    scanner.start(cameras[currentCam]);
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
        Instascan.Camera.getCameras().then(function (cams) {
            cameras = cams;
            currentCam = 0
            if (cams.length > 1) {
                currentCam = 1
                scanner.start(cams[1]);
            } else {
                console.error('No cameras found.');
            }
        }).catch(function (e) {
            console.error(e);
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

        if ('mediaDevices' in navigator) {
            //Get the environment camera (usually the second one)
            navigator.mediaDevices.enumerateDevices().then(devices => {
                const cameras = devices.filter((device) => device.kind === 'videoinput');
    
                if (cameras.length === 0) {
                    console.log('No camera found on this device.');
                }
                const camera = cameras[cameras.length - 1];
    
                // Create stream and get video track
                navigator.mediaDevices.getUserMedia({
                    video: {
                        deviceId: camera.deviceId,
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
    
                        //todo: check if camera has a torch
    
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
            });
        }
    }
};

app.initialize();