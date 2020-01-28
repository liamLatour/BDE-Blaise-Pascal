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
    $("#idi").html("IDI: " + data["idi"]);

    $("#name").html(data["payer_infos"]["nom"]);
    $("#email").html(data["payer_infos"]["email"]);
    $("#surname").html(data["payer_infos"]["prenom"]);
    $("#auth").html(data["payer_infos"]["auth"]);
    $("#classe").html(data["payer_infos"]["classe"]);
    $("#ida").html(data["payer_infos"]["ida"]);


    $("#type").html(data["order_infos"]["tarif"]);
    $("#options").html("");
    $("#otherInfos").html("");

    if(data["order_infos"].hasOwnProperty("options")){
        for (let [key, value] of Object.entries(data["order_infos"]["options"])) {
            $("#options").append("<tr><td>" + value + "</td></tr>");
        }
    }

    for (let [key, value] of Object.entries(data["order_infos"]["custom_infos"])) {
        $("#otherInfos").append("<tr><th>" + key + "</th><td>" + value + "</td></tr>");
    }

    $("#pType").html(data["paiement_infos"]["type"]);
    $("#pId").html(data["paiement_infos"]["paiement_id"]);
    $("#status").html(data["paiement_infos"]["status"]);
    $("#price").html(data["paiement_infos"]["prix"]);

    // WAITING  CONFIRMED_HELLOASSO  CONFIRMED_CB_MANUAL  CONFIRMED_OTHER_MANUAL
    if (data["paiement_infos"]["status"] == "WAITING") {
        $("#pStatus").html('<img src="img/hourglass-half-solid.svg" alt="Attente" height="20px" width="20px"/>');
    } else if (data["paiement_infos"]["status"] == "CONFIRMED_HELLOASSO") {
        $("#pStatus").html('<img src="img/robot-solid.svg" alt="HelloAsso" height="20px" width="20px"/>');
    } else if (data["paiement_infos"]["status"] == "CONFIRMED_CB_MANUAL") {
        $("#pStatus").html('<img src="img/credit-card-solid.svg" alt="CB" height="20px" width="20px"/>');
    } else if (data["paiement_infos"]["status"] == "CONFIRMED_OTHER_MANUAL") {
        $("#pStatus").html('<img src="img/hand-holding-usd-solid.svg" alt="Manuel" height="20px" width="20px"/>');
    } else {
        $("#pStatus").html('<img src="img/hourglass-half-solid.svg" alt="Attente" height="20px" width="20px"/>');
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