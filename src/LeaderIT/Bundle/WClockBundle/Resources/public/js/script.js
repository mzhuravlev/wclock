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
        window.location.href = links.report+"/"+slug;
    });
    refreshButton.click(function() {
        var ajaxurl = links.state;
        $.ajax({
            type: 'POST',
            url: ajaxurl
        }).done(function(msg) {
            setButtonState(msg.state);
            $("#clock").text(msg.worktime);
        });
    });
    actionButtons.click(function() {
        var ajaxurl = links.action;
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {action: $(this).data("action")}
        }).done(function(msg) {
            setButtonState(msg.state);
            refreshButton.click();
        });
    });
    cell.click(function() {
        var username = $(this).data("user");
        var dayval = $(this).data("day");
        var ajaxurl = links.info;
        if(username != "" && dayval != "") {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
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

    workButton.removeClass("green");
    leaveButton.removeClass("green");
    breakButton.removeClass("green");

    switch(state) {
        case 100:
                disable(workButton);
                enable(breakButton);
                enable(leaveButton);
                workButton.addClass("green");
                workButton.val("Начать работу");
            break;
        case 200:
                enable(workButton);
                disable(breakButton);
                disable(leaveButton);
                breakButton.addClass("green");
                workButton.val("Продолжить работу");
            break;
        case 300:
                enable(workButton);
                disable(breakButton);
                disable(leaveButton);
                leaveButton.addClass("green");
                workButton.val("Начать работу");
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
