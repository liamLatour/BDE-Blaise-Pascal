
window.addEventListener("beforeunload", function (e) {
    $.ajax({
        type: 'GET',
        dataType: "json",
        url: url, // change url (le deuxième) à l'url que tu veux
        data: data, // data est sous forme de dictionnaire par ex tu veux: monsite.fr?patate=grosse&truc=beau tu met data = {'patate': 'grosse', 'truc': beau}
        success: function (msg) {
            // ça tu t'en branle
        }
    });
}, false);