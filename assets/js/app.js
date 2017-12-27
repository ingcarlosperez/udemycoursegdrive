var $ = require("jquery");
window.Popper = require("popper.js");
require("bootstrap");
require("datatables.net");

var greet = require('./greet');

$(document).ready(function() {
    // $('body').prepend('<h1>' + greet('john') + '</h1>');
    $("#loadfilesfromgdrive").click(function() {
        loadFilesFromGdrive();
    });

    $("#processfilesfromgdrive").click(function() {
        processFilesFromGdrive();
    });

    var tablefilesgdrive = $('#tablefilesgdrive').DataTable({
        "paging": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "processing": true,
        "serverSide": true,
        "deferLoading": 0,
        "ajax": {
            url: "getfilestoprocess"
        },
        // language: {
        //     url: '/js/frontend/agenda/lang/Spanish.json',
        // },
        "columnDefs": [
            { "targets": 0, "data": "filename" },
            { "targets": 1, "data": "date" },
            {
                "targets": 2,
                "data": "description",
                "render": function(data, type, row, meta) {
                    if (row.person)
                        return row.person.firstname + " " + row.person.middlename + " " + row.person.lastname;
                    else return "";
                }
            },
            { "targets": 3, "data": "type" },
            { "targets": 4, "data": "responsefile" },
        ],
        "rowCallback": function(row, data) {
            if (!data.validfile) {
                $(row).addClass('selected');
            }
        },
        "drawCallback": function(settings) {
            if (settings.json !== undefined) {
                localStorage.setItem("filestoprocess", settings.json.filestoprocess);
                $("#processfilesfromgdrive").removeClass("disabled");
                $("#processfilesfromgdrive").removeClass("btn-default");
                $("#processfilesfromgdrive").addClass("btn-success");
            }
        }
    });

    function loadFilesFromGdrive() {
        $("#processfilesfromgdrive").addClass("disabled");
        $("#processfilesfromgdrive").removeClass("btn-success");
        $("#processfilesfromgdrive").addClass("btn-default");
        tablefilesgdrive.ajax.reload(null, false);
    }

    function processFilesFromGdrive() {
        var filestoprocess = localStorage.getItem("filestoprocess");
        $.ajax({
            type: "GET",
            url: "processfilesfromgdrive",
            data: { 'filestoprocess': filestoprocess },
            dataType: "json",
            timeout: 20000,
            contentType: "application/json", //tell the server we're looking for json
            error: function() {
                console.error('Error al tratar de procesar archivos RIPS.');
            },
            beforeSend: function() {
                $('#modalcargaarchivos').modal('show');
            },
            success: function(data) {
                if (data.response === "Ok") {
                    $("#mensajemodal").html('<i class="fa fa-check" aria-hidden="true"></i> Archivos procesados correctamente!');
                    setTimeout(function() {
                        $('#modalcargaarchivos').modal('hide');
                        tablefilesgdrive.ajax.reload(null, false);
                    }, 2000);
                    $("#mensajemodal").html('<i class="fa fa-spinner fa-pulse fa-fw"></i> Un momento por favor. Se estan procesando los archivos válidos');
                } else {
                    console.log("Archivos no procesados");
                }
            }
        });

    }
});