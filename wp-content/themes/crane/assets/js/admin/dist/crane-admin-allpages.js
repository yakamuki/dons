(function ($) {
  'use strict';

  $(function () {

    $(this)
      .find('.crane-theme-migrate__button')
      .on('click', function (e) {

        e.preventDefault();

        var _this_btn = $(this);
        if (_this_btn.hasClass('disabled')) {
          return;
        }

        _this_btn.addClass('disabled');

        $.ajax({
          type: 'POST',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: 'crane_ajax_start_migrate'
          },
          error: function (result) {
            _this_btn.removeClass('disabled');
            $('.crane-theme-migrate__notice-wrapper').append(result.message);
          },
          success: function (result) {
            if (result.code < 1) {
              $('.crane-theme-migrate__notice-wrapper').remove();
              alert(result.message);
            }

            if (result.code == 1) {
              $('.crane-theme-migrate__notice-wrapper').html(result.message);
            }

          }.bind(this)
        });

      });

    $(this).on('click', '.crane-theme-migrate__notice-info .notice-dismiss', function () {

      $.ajax(ajaxurl,
        {
          type: 'POST',
          data: {
            action: 'crane_dismissed_migration_notice_info'
          }
        });
    });


    // Migration debug page actions.
    $(this)
      .on('click', '.crane-migrate-debug-action-btn', function () {

        var actionValue = $(this).attr('data-action');
        var versionValue = $(this).attr('data-version');

        $.ajax({
          type: 'POST',
          url: ajaxurl,
          dataType: 'json',
          data: {
            action: actionValue,
            version: versionValue
          },
          error: function (result) {
            console.log('ajax error or action not implemented: ' + actionValue);
            alert('ajax error');
          },
          success: function (result) {
            if (actionValue === 'crane_migrate_log') {
              $('.crane-debug-log-block-wrapper').removeClass('crane-debug-log-hidden');
              $('#crane-debug-log-block .crane-debug-log-block-title span').html(versionValue);
              $('#crane-debug-log-block .crane-debug-log-block-content').html(result.message);
            } else {
              window.location.reload(false);
            }
          }.bind(this)
        });

      });


  });
})(jQuery);
(function ($) {

  'use strict';
  $(function () {

    var $gmNeedsToUpdate = $('.gm-needs-to-update-first');
    var $craneImportPage = $('.crane-admin-page');
    var $gmDashboardGlobalSettings = $('.gm-dashboard-container');
    var $gmDashboardBtnGroup = $('.gm-dashboard-header__btn-group');

    var $craneImportPagePlaceholder = '<div class="gm-needs-to-update-placeholder"><p>To prevent data corruption please, first update the <strong>Groovy menu</strong> to version 1.5 or higher.</p></div>';

    if ($craneImportPage.length && $gmNeedsToUpdate.length) {
      $craneImportPage.append($craneImportPagePlaceholder);
    }

    if ($gmDashboardGlobalSettings.length && $gmNeedsToUpdate.length) {
      $gmDashboardBtnGroup.prepend($craneImportPagePlaceholder);
    }

  });
})(jQuery);