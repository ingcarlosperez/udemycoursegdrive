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
                $("#time-icon").addClass("d-none");
                $("#download-icon").removeClass("d-none");
                $('#processfilesfromgdrive').removeAttr("disabled");
            }
        }
    });

    function loadFilesFromGdrive() {

        $("#time-icon").removeClass("d-none");
        $("#download-icon").addClass("d-none");
        $("#processfilesfromgdrive").addClass("disabled");
        $('#processfilesfromgdrive').attr("disabled");
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
                console.log('Error. Unable to process Google Drive files.');
            },
            beforeSend: function() {
                $('#modalprocessfiles').modal('show');
            },
            success: function(data) {
                if (data.response === "Ok") {
                    $("#mensajemodal").html('<i class="fa fa-check" aria-hidden="true"></i> Google Drive files were processed successful!');
                    setTimeout(function() {
                        $('#modalprocessfiles').modal('hide');
                        tablefilesgdrive.ajax.reload(null, false);
                    }, 2000);
                    $("#modalmessage").html('<i class="fa fa-spinner fa-pulse fa-fw"></i> One moment please. Google Drive files are processing');
                } else {
                    console.log("Unable to process Google Drive files");
                }
            }
        });

    }

    var tableprocessedfiles = $('#tableprocessedfiles').DataTable({
        "paging": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "listprocessedfiles"
        },
        // language: {
        //     url: '/js/frontend/agenda/lang/Spanish.json',
        // },
        "columnDefs": [{
                "targets": 0,
                "data": null,
                "render": function(data, type, row, meta) {
                    if (row.person)
                        return row.person.firstname;
                    else return "";
                }
            },
            { "targets": 1, "data": "description" },
            { "targets": 2, "data": "filename" },
            { "targets": 3, "data": "fileid" },
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
                $("#time-icon").addClass("d-none");
                $("#download-icon").removeClass("d-none");
                $('#processfilesfromgdrive').removeAttr("disabled");
            }
        }
    });
});