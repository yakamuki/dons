jQuery(function () {
  jQuery(document).ready(function ($) {


    $('.crane-bn_widget_action_field').on('change', function () {
      if ($(this).val() == 'url') {
        $(this).closest('.crane-bn_widget_wrapper').find('.crane-bn_link_wrap').show();
      } else {
        $(this).closest('.crane-bn_widget_wrapper').find('.crane-bn_link_wrap').hide();
      }
    });

    $('.crane-bn_widget_action_field').each(function (index) {
      if ($(this).val() == 'url') {
        $(this).closest('.crane-bn_widget_wrapper').find('.crane-bn_link_wrap').show();
      } else {
        $(this).closest('.crane-bn_widget_wrapper').find('.crane-bn_link_wrap').hide();
      }
    });


    function media_upload(button_class) {

      // Uploading files
      var file_frame;

      $(document).on('click', button_class, function (event) {

        event.preventDefault();

        var _this = $(this).parent();

        // Create the media frame.
        file_frame = wp.media.frames.downloadable_file = wp.media({
          title: crane_js_l10n['choose_image'],
          button: {text: crane_js_l10n['use_image']},
          multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
          var attachment = file_frame.state().get('selection').first().toJSON();
          _this.find('.image-id').val(attachment.id).change();

          if (attachment.sizes.thumbnail !== undefined) {
            $attach_url = attachment.sizes.thumbnail.url;
          } else {
            $attach_url = attachment.sizes.full.url;
          }

          _this.parent().find('.crane-banner-widget-upload-image').find('img').attr('src', $attach_url).show();
        });
        // Open the modal.
        file_frame.open();
      });

    }

    media_upload('.crane-banner-widget-upload-image-button');


  });
});



jQuery(function () {
  jQuery(document).ready(function ($) {

    $(".crane-image-widget-checkbox").on("change", function () {
      if ($(this).prop("checked")) {
        $(this).parent().find(".crane-image-widget-value").val("yes");
      } else {
        $(this).parent().find(".crane-image-widget-value").val("no");
      }
    });

  });
});

