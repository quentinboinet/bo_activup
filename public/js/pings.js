$(document).ready(function() {

    //fonction pour changer l'affichage des données en fonction du device sélectionné sur la page pings
    $('#deviceSelect').change(function () {

        var selectedDevice = $('#deviceSelect').val();
        $("table tbody").css('display', 'none');
        $("#" + selectedDevice).css('display', 'table-row-group');
    });

});