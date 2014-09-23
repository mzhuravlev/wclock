$(document).ready(function() {
    var refreshButton = $("#refresh");
    var actionButtons = $(".action");
    var cell = $(".cell");
    var dialog = $("#dialog");
    var datePicker = $("#datepicker");
    var changeDate = $("#change_date");
    datePicker.datepicker({ dateFormat: "dd.mm.yy", defaultDate: +1 });
    // установка обработчиков кнопок
    setButtonHandlers(actionButtons , refreshButton, cell, dialog, changeDate, datePicker)

    refreshButton.click();
    setInterval(function() {refreshButton.click();}, 1000*60);
});

function setButtonHandlers(actionButtons, refreshButton, cell, dialog, changeDate, datePicker) {
    changeDate.click(function() {
        var date = datePicker.val();
        var slug = date.replace(".", "").replace(".", "");
        window.location.href = $("#url").val()+"/"+slug;
    });
    refreshButton.click(function() {
        $.ajax({
            type: 'POST',
            url: '/wclock/state'
        }).done(function(msg) {
            setButtonState(msg.state);
            $("#clock").text(msg.worktime);
        });
    });
    actionButtons.click(function() {
        $.ajax({
            type: 'POST',
            url: '/wclock/action',
            data: {action: $(this).data("action")}
        }).done(function(msg) {
            //var obj = $.parseJSON(msg);
            setButtonState(msg.state);
        });
    });
    cell.click(function() {
        var username = $(this).data("user");
        var dayval = $(this).data("day");
        if(username != "" && dayval != "") {
            $.ajax({
                type: 'POST',
                url: '/wclock/info',
                data: {
                    user: username,
                    day: dayval
                }
            }).done(function (msg) {
                dialog.html(msg);
                dialog.dialog();
            });
        }

    });
}

function setButtonState(state) {
    var workButton = $("#work");
    var leaveButton = $("#leave");
    var breakButton = $("#break");

    switch(state) {
        case 100:
                disable(workButton);
                enable(breakButton);
            break;
        case 200:
                enable(workButton);
                disable(breakButton);
            break;
        case 300:
                enable(workButton);
                enable(breakButton);
            break;
        case 0:
            break;
        default:
            error("Неизвестный статус: "+state);
    }
}

function error(msg) {
    alert("Ошибка: "+msg);
}

function disable(button) {
    button.prop("disabled", true);
}

function enable(button) {
    button.prop("disabled", false);
}
