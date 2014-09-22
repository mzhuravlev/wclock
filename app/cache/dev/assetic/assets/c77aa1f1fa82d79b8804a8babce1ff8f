$(document).ready(function() {
    var uid = $("#uid").val();
    $(".action").click(function() {
        $.ajax({
            type: 'POST',
            url: '/wclock/action',
            data: {action: $(this).data("action"), user: uid}
        }).done(function(msg) {

        });

    });
});