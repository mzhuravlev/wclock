$(document).ready(function() {
    var refreshButton = $("#refresh");
    var actionButtons = $(".action");
    var cell = $(".cell");
    var dialog = $("#dialog");
    //var datePicker = $("#datepicker");
    var changeDate = $("#change_date");
    //datePicker.datepicker({ dateFormat: "dd.mm.yy", defaultDate: +1 });
    // установка обработчиков кнопок
    setButtonHandlers(actionButtons , refreshButton, cell, dialog, changeDate);

    refreshButton.click();
    setInterval(function() {refreshButton.click();}, 1000*60);


    $("#show_stat").click(function(){
        window.location.href = links.stat;
    });

    $(".user").click(function() {
        var username = $(this).data('user');
        var cells = $("#"+username+"_row").find("td");
        title = $(this).data('user');

        var result = 0;

        for(i=0; i<cells.length;i++) {
             var value = parseFloat(cells[i].dataset.val);
             if(value) {
                 result += value;
             }
        }
        var dialog = $("<div>Отработано часов: <b>"+result+"</b></div>").addClass("alert").dialog();
        dialog.dialog('option', 'title', title);
        //console.log(result);
    });
});

function dateToString(date) {
    hours = ('0'+date.h).slice(-2);
    minutes = ('0'+date.i).slice(-2);
    return hours+":"+minutes;
}

function setButtonHandlers(actionButtons, refreshButton, cell, dialog, changeDate) {
    changeDate.click(function() {
        year = $("#dateYear").val();
        month = $("#dateMonth").val();
        var slug = "01"+month+year;
        window.location.href = links.report+"/"+slug;
    });
    refreshButton.click(function() {
        var ajaxurl = links.state;
        $.ajax({
            type: 'POST',
            url: ajaxurl
        }).done(function(msg) {
            setButtonState(msg.state);
            $("#clock").text(dateToString(msg.worktime));
            $(".breaktime").find(".break-clock").text(dateToString(msg.breaktime));
            var progress = (msg.worktime.h*60 + msg.worktime.i) / 4.8;
            $( "#progressbar" ).progressbar({
                value: progress
            });
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
                setDialogClickHandler(dialog);
            });
        }

    });
}

function setDialogClickHandler(dialog, editEvent) {
    var eventFields = dialog.find(".event-field");
    eventFields.unbind().click(function() {
        showEditEventDialog($(this).data("id"), $(this).data("time"));
    });
}

function showEditEventDialog(id, time) {
    var ajaxurl = links.edit;
    var editEvent = $("#edit-event");
    var idField = editEvent.find("#id-field");
    var timeField = editEvent.find("#time-field");
    var typeField = editEvent.find("#type-field");
    var deleteField = editEvent.find("#delete-field");
    var copyField = editEvent.find("#copy-field");
    idField.text(id);
    timeField.val(time);
    editEvent.find("#write-fields").unbind().click(function() {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                id: id,
                time: timeField.val(),
                type: typeField.val(),
                delete: deleteField.prop("checked"),
                copy: copyField.prop("checked")
            }
        }).fail(function() {
            alert("Запрос к серверу не может быть выполнен");
        }).done(function(msg) {
            if(msg.code == 'success') {
                window.location.href = links.report;
            } else {
                alert("Ошибка при редактировании записи: "+msg.code);
            }
        });
    });
    editEvent.dialog();
}

function showBreakTime(show) {
    var breakClock = $(".breaktime");
    if(show) {
        breakClock.slideDown();
    } else {
        breakClock.slideUp();
    }
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
                showBreakTime(false);
            break;
        case 200:
                enable(workButton);
                disable(breakButton);
                disable(leaveButton);
                breakButton.addClass("green");
                workButton.val("Продолжить работу");
                showBreakTime(true);
            break;
        case 300:
                enable(workButton);
                disable(breakButton);
                disable(leaveButton);
                leaveButton.addClass("green");
                workButton.val("Начать работу");
                showBreakTime(false);
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
