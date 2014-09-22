function error(msg) {
    alert("Ошибка: "+msg);
}

function disable(button) {
    button.prop("disabled", true);
}

function enable(button) {
    button.prop("disabled", false);
}

$(document).ready(function() {
    var uid = $("#uid").val();
    $(".action").click(function() {
        $.ajax({
            type: 'POST',
            url: '/wclock/action',
            data: {action: $(this).data("action"), user: uid}
        }).done(function(msg) {
            //var obj = $.parseJSON(msg);
            setButtonState(msg.state);
        });
    });
});

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
        default:

    }
}