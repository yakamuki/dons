jQuery(document).ready(function ($) {

    var timer,
        lastResult;

    gr_tw_start();


    $('div[id*=widget-wptt_twittertweets]').on('ajaxComplete', function () {
        gr_tw_start();
    });

    $(document).on("keyup", '.gr_tw-twitter_username', function () {
        clearTimeout(timer);
        var wrapper = $( this.closest( 'p' ) );
        var userInput = this.value,
            defaultValue = wrapper.find('.username-validator').prop("defaultValue"),
            delay = 700;
        if (userInput == '') {
            wrapper.find('.username-validator')
                .removeClass('gr_tw-user_valid gr_tw-user_invalid')
                .html(defaultValue);

            return;
        }
        wrapper.find('.username-validator').html('checking...');

        timer = setTimeout(function () {
            gr_tw_validateScreenName(userInput, wrapper);
        }, delay);

    });

    function gr_tw_validateScreenName(name, wrapper) {
        $.ajax({
            dataType: "json",
            url: ajaxurl,
            data: {gr_tw_userName: name, action: 'userValidate'},
            success: function (data) {
                gr_tw_setValidatorTo(data, wrapper);
                lastResult = data;
            }
        });
    }

    function gr_tw_setValidatorTo(obj, wrapper) {
        wrapper.find('.username-validator')
            .html(obj.data)
            .removeClass('gr_tw-user_valid gr_tw-user_invalid')
            .addClass(obj.class);
    }

    function gr_tw_start() {
        if (lastResult) {
            gr_tw_setValidatorTo(lastResult);
        }
    }
});
