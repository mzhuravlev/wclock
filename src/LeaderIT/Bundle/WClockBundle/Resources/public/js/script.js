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


    /*$("#show_stat").click(function(){
        window.location.href = links.stat;
    });*/

    $(".total-hours").each(calculateTotal);

    setMarkFunc(cell);

    setMonthSelector();

    setProgressTooltip();

    //setMarkDatFunc();
});

function setMarkFunc(obj) {
    obj.tooltip();
    obj.each(function(){
        _this = $(this);
        var mark = _this.data('mark');
        switch(mark) {
            case 100:
                _this.addClass("marked-100");
                break;
            case 200:
                _this.addClass("marked-200");
                break;
            case 300:
                _this.addClass("marked-300");
                break;
            default:
                break;
        }
    });
}

function setMarkDatFunc() {

    var showMarkOption = function(obj){
        obj.toggleClass("red");
    }

    var hideMarkOption = function(obj){
        obj.removeClass("red");
    }

    var cells = $(".cell");
    cells.on('mouseover', function () {
        //$(this).timer =

        var toggle = function(obj){
            return function() {
                showMarkOption(obj);
            }
        }

        $(this).data("timer", setTimeout(toggle($(this)), 2000));
    }).on('mouseout', function(){
        clearTimeout($(this).data("timer"));
        hideMarkOption($(this));
    });
}

function setMonthSelector() {
    var month = $("#cur-month").text().substring(1);
    var dateMonth = $("#dateMonth");
    dateMonth.find("option").each(function(){
        if(this.text.substring(1) == month){
            $(this).prop('selected', 'true');
        }
    });
    dateMonth.on('change', function() {
        $("#change_date").click();
    });
}

function setProgressTooltip() {

        var pbar = $("#progressbar");
        var tooltip = $("#tooltip");

        pbar.mousemove(function (eventObject) {

        $data_tooltip = pbar.progressbar("value").toFixed(0)+"%";

        tooltip.text($data_tooltip)
            .css({
                "top" : eventObject.pageY + 5,
                "left" : eventObject.pageX + 5
            })
            .show();

    }).mouseout(function () {

        tooltip.hide()
            .text("")
            .css({
                "top" : 0,
                "left" : 0
            });
    });
}

calculateTotal = function(index, el) {
    var username = $(el).data('user');
    var cells = $("#"+username+"_row").find("td");

    var result = 0;

    for(var i=0; i<cells.length;i++) {
        var value = parseFloat(cells[i].dataset.val);
        if(value) {
            result += value;
        }
    }
    $(el).text(result.toFixed(1));

    return true;
};

function dateToString(date) {
    var hours = ('0'+date.h).slice(-2);
    var minutes = ('0'+date.i).slice(-2);
    return hours+":"+minutes;
}

function setButtonHandlers(actionButtons, refreshButton, cell, dialog, changeDate) {
    changeDate.click(function() {
        var year = $("#dateYear").val();
        var month = $("#dateMonth").val();
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
            var pbar = $( "#progressbar" );
            //var pbarText = pbar.find();
            //pbarText.text(progress.toFixed(0)+"%");//ui-progressbar-value ui-widget-header ui-corner-left
            pbar.progressbar({
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

function setDialogClickHandler(dialog) {
    var eventFields = dialog.find(".event-field");
    var markSection = {
        mark: dialog.find("#mark-field"),
        comment: dialog.find("#comment-field"),
        write: dialog.find("#write-mark")
    }

    markSection.write.unbind().click(function(){
        var ajaxurl = links.mark;
        var data = {
            date: markSection.write.data("date"),
            user: markSection.write.data("user"),
            mark: markSection.mark.val(),
            comment: markSection.comment.val()
        }
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data
        }).done(function(){
            location.reload();
        });

        //dialog.dialog("close");
    });

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
                location.reload();
                //window.location.href = links.report;
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
            location.reload();
            //error("Неизвестный статус: "+state);
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
