(function ($) {
  'use strict';

  $(function () {

    //select
    $('.grooni-metabox-field select').each(function () {
      var $this = $(this),
        numberOfOptions = $(this).children('option').length;
      $this.addClass('select-hidden');
      $this.wrap('<div class="select"></div>');
      $this.after('<div class="select-styled"></div>');

      var $styledSelect = $this.next('div.select-styled');
      $styledSelect.text($this.children('option:selected').eq(0).text());

      var $list = $('<ul />', {
        'class': 'select-options'
      }).insertAfter($styledSelect);

      for (var i = 0; i < numberOfOptions; i++) {
        $('<li />', {
          text: $this.children('option').eq(i).text(),
          rel: $this.children('option').eq(i).val()
        }).appendTo($list);
      }

      var $listItems = $list.children('li');

      $styledSelect.on('click', function (e) {
        e.stopPropagation();
        $('div.select-styled.active').each(function () {
          $(this).removeClass('active').next('ul.select-options').hide();
        });
        $(this).toggleClass('active').next('ul.select-options').toggle();
      });

      $listItems.on('click', function (e) {
        e.stopPropagation();
        $styledSelect.text($(this).text()).removeClass('active');
        $this.val($(this).attr('rel')).change();
        $list.hide();
      });

      $(document).on('click', function () {
        $styledSelect.removeClass('active');
        $list.hide();
      });

    });

    /***************************************
     GROONI MENU Header Styles
     ***************************************/

    function groovyHeaderSelector() {
      var input = $('#grooni-metabox__header-types__options');

      input.on('change.header',
        function () {
          var align = $(this).data('align');
          var toolbar = $(this).data('toolbar');
          var style = $(this).data('style');

          var isHideAlignCenter = (style == '2' || style == '3');
          var isHideToolbarToggle = (style == '3');
          if (isHideToolbarToggle) {
            toolbar = false;
          }

          // show/hide disabled align and toolbar
          $('.grooni-metabox__header-types__options__align--center').toggle(!isHideAlignCenter);
          $('.grooni-metabox__header-types__options__toolbar-toggle').toggle(!isHideToolbarToggle);

          // set active align
          $('.grooni-metabox__header-types__options__align > span').removeClass('active');
          $('.grooni-metabox__header-types__options__align > span[rel="' + align + '"]').addClass('active');

          // set active style
          $('.grooni-metabox__header-types__options__list > span').removeClass('active');
          $('.grooni-metabox__header-types__options__list > span[rel="' + style + '"]').addClass('active');

          // set toolbar switcher
          if (toolbar) {
            $('#switch-toolbar-toggle').attr('checked', true);
          }

          $('#grooni-metabox__header-types')
            .attr('class', '')
            .addClass('style-' + style + '-align-' + align + ' toolbar-' + toolbar);
          $(this).val(JSON.stringify({
            'align': align,
            'style': style,
            'toolbar': toolbar.toString()
          }));
        });

      //change align
      $('.grooni-metabox__header-types__options__align span').on('click', function () {
        input.data('align', $(this).attr('rel')).trigger('change.header');
      });

      $('#switch-toolbar-toggle').on('click', function () {
        input.data('toolbar', $(this).is(':checked')).trigger('change.header');
      });

      //header type
      $('.grooni-metabox__header-types__options__list span').on('click', function () {
        input.data('style', $(this).attr('rel')).trigger('change.header');
      });

      input.trigger('change.header');

    }

    groovyHeaderSelector();

    $('.grooni-metabox__module[data-condition]').each(function () {
      var field = $(this);
      var condition = $(this).data('condition');
      if ($.isArray(condition) && $.isArray(condition[0])) {
        $.each(condition, function (i, cond) {
          createFieldListener(field, cond);
        });
      } else {
        createFieldListener(field, condition);
      }
    });
    $('.gm-header').change();

    $('.grooni-metabox-row :input').on('change', function () {
      window.onbeforeunload = function () {
        return crane_js_l10n['save_alert'];
      };
    });
    $('#post').on('submit', function () {
      window.onbeforeunload = function () {};
    });

    function addMediaField($this) {
      var tpl = $this.closest('.grooni-metabox-field').find('.grooni-meta-field-file-tpl').clone();
      tpl.removeClass('grooni-meta-field-file-tpl');
      $('.grooni-meta-field-file-container').append(tpl);
      initMediaField(tpl);
      return tpl;
    }

    $('.grooni-meta-field-file-add').on('click', function () {
      var tpl = addMediaField($(this));
      tpl
        .find('.grooni-meta-upload-btn')
        .trigger('click');
      return false;
    });

    function initMediaField(field) {

      $(field).find('.grooni-meta-upload-btn').on('click', function (e) {
        var btn = $(this);
        e.preventDefault();
        var image = wp
          .media({
            title: 'Upload Image',
            multiple: false
          })
          .open()
          .on('select', function () {
            var uploaded_image = image
              .state()
              .get('selection')
              .first()
              .toJSON();
            btn
              .closest('.grooni-meta-field-file')
              .find('.grooni-meta-upload-input')
              .val(uploaded_image.id)
              .data('url', uploaded_image.url)
              .change();
          });
        return false;
      });

      $(field)
        .find('.grooni-meta-remove-btn')
        .on('click', function () {
          if (confirm(crane_js_l10n['remove_image'])) {
            $(this).closest('.grooni-meta-field-file').remove();
          }
          return false;
        });

      $(field)
        .find('.grooni-meta-upload-input')
        .on('change', function () {
          if ($(this).val() != '') {
            $(this)
              .closest('.grooni-meta-field-file')
              .addClass('grooni-meta__module__media--selected')
              .find('.grooni-meta-media-preview')
              .html('<img src="' + $(this).data('url') + '" />');
          } else {
            $(this)
              .closest('.grooni-meta-field-file')
              .removeClass('grooni-meta__module__media--selected')
              .find('.grooni-meta-media-preview')
              .html('');
          }
        })
        .change();
    }

    $('.grooni-meta-field-file-container').each(function () {
      var images = $(this).data('images');
      var container = $(this);
      $.each(images, function (i, image) {
        var field = addMediaField(container);
        field.find('.grooni-meta-upload-input').val(image.id).data('url', image.path[0]).change();
      });
    });

    function checkCondition(field, val) {
      var show = false;
      if (field.length == 2) {
        field = field.not('[type="hidden"]');
      }
      if (field.attr('type') == 'checkbox') {
        show = field.is(':checked');
        if (!val) {
          show = !show;
        }
      } else {
        if ($.isArray(val)) {
          show = $.inArray(field.val(), val) > -1;
        } else {
          show = field.val() == val;
        }
      }
      return show;
    }

    function showHideRow(row) {
      var conditions = $(row).data('condition');
      var show = true;
      $.each(conditions, function (name, val) {
        var field = row.closest('.grooni-metabox').find('[name=' + name + ']');
        if (!checkCondition(field, val)) {
          show = false;
        }
        row.closest('.grooni-metabox').find('[name="' + name + '"]').on('change', function () {
          showHideRow(row);
        });
      });

      row.toggle(show);

    }

    $('.grooni-metabox-row[data-condition]').each(function () {
      showHideRow($(this));
    });
    $('.grooni-metabox .crane-radio-group .crane-radio__triple').on('change', function () {
      var radio_group_name = $(this).attr('name').split('__');
      radio_group_name = radio_group_name[1];
      var radio_group_param = $('.grooni-metabox .crane-radio-group input[name="triple-name__' + radio_group_name + '"]:checked').val();
      $('.grooni-metabox input[name="' + radio_group_name + '"]').val(radio_group_param).change();
    });

    $('.grooni-metabox .crane-meta-field-number').each(function () {
      var $block = $(this);
      var $switch = $block.find('.crane-meta-field-number__switch');
      var $front = $block.find('.crane-meta-field-number__front');
      var $value = $block.find('.crane-meta-field-number__value');

      if ($value.val()) {
        $switch.prop('checked', false);
      }

      var state = $switch.prop('checked');
      if (state) {
        $front.hide();
        $value.val('');
      }

      $switch.on('change', function () {
        var state_front = $(this).prop('checked');
        if (state_front) {
          $front.hide();
          $front.val('');
          $value.val('');
        } else {
          $front.show();
        }
      });

      $front.on('change', function () {
        $value.val($(this).val());
      });

    });

  });

})(jQuery);
(function ($) {
  'use strict';

  $(function () {

    try {
      $('#crane-dashboard-search input[name="s"]').autocomplete({
        source: function (request, response) {
          $.ajax({
            url: '',
            dataType: 'json',
            data: {
              page: 'crane-theme-dashboard',
              term: this.term
            },
            success: function (data) {
              response($.map(data.results, function (item) {
                return {
                  label: item.title,
                  value: item.title,
                  url: item.url
                };
              }));
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
          });
        },
        search: function (event) {
          $(event.currentTarget).addClass('sa_searching');
        },
        create: function () {
        },
        select: function (event, ui) {
          if (ui.item.url === '#') {
            return true;
          }
        },
        open: function (event) {
          var acData = $(this).data('uiAutocomplete');
          acData
            .menu
            .element
            .find('a')
            .each(function () {
              var $self = $(this),
                keywords = $.trim(acData.term).split(' ').join('|');
              $self.html($self.text().replace(new RegExp('(' + keywords + ')', 'gi'), '<span class="sa-found-text">$1</span>'));
            });
          $(event.target).removeClass('sa_searching');
        },
        close: function () {
        }
      });
    }
    catch (e) {}  // eslint-disable-line no-empty

  });

})(jQuery);
(function( $ ) {
  'use strict';

  $(function () {

    if ( 'import-finish' == crane_findGetParameter('step') ) {
      crane_importFinalStep();
    }


    function grooniCheckIfImportAnyContent( importAll ) {
      var importAnyContent = [];

      $('#crane-import').find('input.crane-import-checkbox:checked').each(function () {
        importAnyContent.push( $(this).val() );
      });

      $('#crane-import').find('.crane-import-page-inner.selected').each(function () {
        importAnyContent.push( $(this).attr('data-page') );
      });

      if ( importAll ) {
        importAnyContent.push('all-home');
      }

      $('.crane-import-page-confirm-block').show();

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'crane_check_necessary_plugins',
          steps: JSON.stringify(importAnyContent)
        },
        error: function () {
          //...
        },
        success: function (result) {
          $('.crane-import-page-confirm-text--plugins').html(result);
        }.bind(this)
      });

    }

    function grooniRemoveAnyImportContent() {
      $('#crane-import').find('input.crane-import-checkbox:checked').each(function () {
        var self = $(this);
        self.attr('checked', false);
      });

      $('#crane-import').find('.crane-import-page-inner.selected').each(function () {
        var self = $(this);
        self.removeClass('selected');
        self.find('.crane-import-page-select-btn').text(crane_js_l10n['select']);
      });

      grooniCheckIfImportAnyContent(false);
    }

    $('.crane-import-page-import-cancel').on('click', function () {
      grooniRemoveAnyImportContent();
    });

    grooniCheckIfImportAnyContent( false );

    $('.crane-import-radio').on('change', function () {

      if( $(this).prop('checked') && $(this).hasClass('crane-import-radio--custom') ) {

        $('.crane-import-wrapper').hide().removeClass('hidden');
        $('.crane-import-wrapper').fadeIn();

        grooniCheckIfImportAnyContent( false );

      } else {
        $('.crane-import-wrapper').fadeOut();
        grooniCheckIfImportAnyContent( true );
      }

    });

    $('#all-home').on('click', function () {

      if ($(this).prop('checked')) {

        $('.crane-import-page-inner').addClass('selected');
        $('.crane-import-page-select-btn').text(crane_js_l10n['unselect']);

      } else {
        $('.crane-import-page-inner').removeClass('selected');
        $('.crane-import-page-select-btn').text(crane_js_l10n['select']);
      }

    });

    $('.crane-import-page-select-btn').on('click', function () {

      if ($(this).closest('form').hasClass('process')) {
        return false;
      }

      var self = $(this);

      if (self.closest('.crane-import-page-inner').hasClass('selected')) {

        self.closest('.crane-import-page-inner').removeClass('selected');
        self.text(crane_js_l10n['select']);
        $('#all-home').prop('checked', false);

      } else {

        self.closest('.crane-import-page-inner').addClass('selected');
        self.text(crane_js_l10n['unselect']);

      }

      grooniCheckIfImportAnyContent( false );

    });


    $('.crane-import-checkbox').on('change', function () {
      grooniCheckIfImportAnyContent( false );
    });

    $('#crane-import').on('submit', function() {

      var confirm_text = crane_js_l10n['are_u_sure'];

      if( ! confirm( confirm_text ) ) {
        return false;
      }

      var doing_importing = true;

      $(this).addClass('process').slideUp();
      $(this).find(':input').attr('readonly', true);
      $(this).find(':input, button').on('click', function() { return false; });

      $('.crane-import-page-btn').attr('disabled', true).addClass('crane-import-page-btn--disabled').css('background', '#ddd');

      var type = $(this).find('.crane-import-radio:checked').val();
      var importOnlyPresets = true;
      var importContent = [];

      importContent.push('dummy');
      importContent.push('plugins');
      importContent.push('plugins_activate');

      if ( type === 'import-all' ) {

        // Import by old style
        importOnlyPresets = false;

        importContent.push('attachment');

        importContent.push('prexml');

        importContent.push('shop-attributes');


        $(this).find('input.crane-import-checkbox').each(function () {
          importContent.push($(this).val());
        });

        importContent.push('additional_menus');

        importContent.push('convertplug');

      }	else {

        var importAdditionalMenu = false;
        var importConvertPlug = false;

        $(this).find('input.crane-import-checkbox:checked').each(function () {
          if ($(this).attr('data-is_preset') === 'no' ) {
            if ($(this).val() !== 'all-home') {
              importOnlyPresets = false;
            }
          }
        });
        $(this).find('.crane-import-page-inner.selected').each(function () {
          if ($(this).attr('data-is_preset') === 'no') {
            importOnlyPresets = false;
          }
        });


        if ( ! importOnlyPresets) {


          importContent.push('attachment');

          importContent.push('prexml');

          var importAllHome = false;

          $(this).find('input.crane-import-checkbox:checked').each(function () {

            if ($(this).val() === 'shop') {
              importContent.push('shop-attributes');
            }

            if ($(this).val() === 'all-home') {
              importAllHome = true;
              importConvertPlug = true;
            }

            importContent.push($(this).val());

          });

          if (!importAllHome) {

            $(this).find('.crane-import-page-inner.selected').each(function () {

              if ($(this).data('page') == 'cargo' || $(this).data('page') == 'education' || $(this).data('page') == 'barber') {
                importConvertPlug = true;
              }

              importContent.push($(this).data('page'));

              if ($(this).data('page') == 'pages_home-9') {
                importContent.push('menu/second_menu');
              }

            });

          }

          if (importConvertPlug) {
            importContent.push('convertplug');
          }

          if (importAdditionalMenu) {
            importContent.push('additional_menus');
          }



        } else { // importOnlyPresets == true


          $(this).find('input.crane-import-checkbox:checked').each(function () {

            if ($(this).val() === 'shop') {
              importContent.push('shop-attributes');
            }

            importContent.push($(this).val());

          });

          $(this).find('.crane-import-page-inner.selected').each(function () {
            importContent.push($(this).data('page'));
          });

        }

      }

      importContent.push('import_all_after'); // After

      var length = importContent.length;
      var checkStatusFlag = false;

      function nextQueue() {

        var currentLength = importContent.length;
        var data = importContent.shift();

        var percents = 100;

        if ( currentLength > 0 ) {
          percents = 100-Math.ceil(currentLength/length*100);
        }

        $('.crane-import-progress-bar-container').addClass('importing');
        $('#import-progress-bar').width(percents+'%');
        $('.import-progress-bar-percentage').html(percents + '%');

        if (data) {

          var url = ajaxurl;
          var action = importOnlyPresets ? 'grooni_theme_addons_import_preset' : 'grooni_theme_addons_import_part';
          var steps = '';

          switch (data) {
          case 'plugins_activate':
            url = '?page=crane_import';
            action = '';
            break;
          case 'plugins':
            steps = importContent;
            break;
          }


          $.ajax({
            'url': url,
            'type': 'post',
            'data': {'action': action, 'content': data, 'steps': steps, 'type': type},
            success: function (response) {

              // Prevent doubled call
              if (!checkStatusFlag) {
                checkStatusFlag = true;
              }

              if (response.hasOwnProperty('status') && response.status !== undefined && response.status == 'critical_error') {
                $('#crane-import-status').css({'color': 'red'}).text(response.message);
                alert(response.message);
              } else {
                nextQueue();
              }
            },
            error: function(response) {

              if (response.hasOwnProperty('responseJSON') && response.responseJSON.hasOwnProperty('status') && response.responseJSON.status === 'critical_error') {
                $('#crane-import-status').css({'color':'red'}).text(response.responseJSON.message);
                alert(response.responseJSON.message);

              } else {
                nextQueue();
              }

            }

          });

        }	else {
          $(location).attr('search', $(location).attr('search') + '&step=import-finish' );
        }

      }


      var repeater;

      function checkImportStatus() {
        $.ajax({
          'url': ajaxurl,
          'type': 'post',
          'data': {'action': 'grooni_theme_addons_check_import_status'},
          success: function (resp) {
            var repet_call = true;

            if (resp.status !== undefined) {


              if (resp.status == 'success' || resp.status == 'start') {
                doing_importing = true; // prevent close browser tab
              } else {
                doing_importing = false; // STOP preventing close browser tab
              }

              if (resp.status == 'stop' || resp.status == '500' || resp.status == 'critical_error') {
                repet_call = false;
              }

              $('#crane-import-status').text(resp.message);


              if (repet_call) {
                repeater = setTimeout(checkImportStatus, 2100);
              }

            }

          },
          error: function () {
            // ... nothing yet
          }

        });

      }


      nextQueue();
			
      return false;
			
    });


    function getImportInfoData() {
      $.ajax({
        'url': ajaxurl,
        'type': 'post',
        'data': {'action': 'grooni_theme_addons_get_import_info'},
        success: function (response) {

          var loadLink = $('#crane-import-log .crane-import-log--load');

          if (response.message) {
            $('.crane-import-log-inner').remove();
            $('#crane-import-log')
              .append('<div class="crane-import-log-inner"></div>');
            $('.crane-import-log-inner')
              .append(response.message);
          } else {
            $('#crane-import-log').text('').append(loadLink);
          }

        },
        error: function () {
          // ... nothing yet
        }

      });
    }

    $('#crane-import-log .crane-import-log--load').on('click', function (e) {
      e.preventDefault();
      getImportInfoData();
    });


    function crane_importFinalStep() {
      $('#crane-import').fadeOut();
      $('.crane-import-page-btn').attr('disabled', true).addClass('crane-import-page-btn--disabled').css('background', '#ddd');
      $('.crane-import-progress-bar-container').addClass('importing');
      $('#import-progress-bar').width('100%');
      $('.import-progress-bar-percentage').html('100%');
      $('.crane-import-progress-bar-container').removeClass('importing');
      $('.import-result').fadeIn();
    }

    function crane_findGetParameter(parameterName) {
      var result = null,
        tmp = [];
      location.search
        .substr(1)
        .split('&')
        .forEach(function (item) {
          tmp = item.split('=');
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
      return result;
    }
		
  });

})(jQuery);
(function ($) {

  'use strict';
  $(function () {

    var showingTooltip;

    document.onmouseover = function(e) {
      var target = e.target;

      var tooltip = target.getAttribute('data-tooltip');
      if (!tooltip) return;

      var tooltipElem = document.createElement('div');
      tooltipElem.className = 'ct-tooltip';
      tooltipElem.innerHTML = tooltip;
      document.body.appendChild(tooltipElem);

      var coords = target.getBoundingClientRect();

      var left = coords.left + (target.offsetWidth - tooltipElem.offsetWidth) / 2;
      if (left < 0) left = 0; // не вылезать за левую границу окна

      var top = coords.top - tooltipElem.offsetHeight - 10;
      if (top < 0) { // не вылезать за верхнюю границу окна
        top = coords.top + target.offsetHeight + 5;
      }

      tooltipElem.style.left = left + 'px';
      tooltipElem.style.top = top + 'px';

      showingTooltip = tooltipElem;
    };

    document.onmouseout = function(e) {

      if (showingTooltip) {
        document.body.removeChild(showingTooltip);
        showingTooltip = null;
      }

    };

  });
})(jQuery);
/**
 * DEVELOPED BY
 * GIL LOPES BUENO
 * gilbueno.mail@gmail.com
 *
 * WORKS WITH:
 * IE8*, IE 9+, FF 4+, SF 5+, WebKit, CH 7+, OP 12+, BESEN, Rhino 1.7+
 * For IE8 (and other legacy browsers) WatchJS will use dirty checking
 *
 * FORK:
 * https://github.com/melanke/Watch.JS
 */

'use strict';
(function (factory) {
  if (typeof exports === 'object') {
    // Node. Does not work with strict CommonJS, but
    // only CommonJS-like enviroments that support module.exports,
    // like Node.
    module.exports = factory();
  } else if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(factory);
  } else {
    // Browser globals
    window.WatchJS = factory();
    window.watch = window.WatchJS.watch;
    window.unwatch = window.WatchJS.unwatch;
    window.callWatchers = window.WatchJS.callWatchers;
  }
}(function () {

  var WatchJS = {
      noMore: false,        // use WatchJS.suspend(obj) instead
      useDirtyCheck: false // use only dirty checking to track changes.
    },
    lengthsubjects = [];

  var dirtyChecklist = [];
  var pendingChanges = []; // used coalesce changes from defineProperty and __defineSetter__

  var supportDefineProperty = false;
  try {
    supportDefineProperty = Object.defineProperty && Object.defineProperty({}, 'x', {});
  } catch (ex) {  /* not supported */
  }

  var isFunction = function (functionToCheck) {
    var getType = {};
    return functionToCheck && getType.toString.call(functionToCheck) == '[object Function]';
  };

  var isArray = function (obj) {
    return Object.prototype.toString.call(obj) === '[object Array]';
  };

  var isObject = function (obj) {
    return {}.toString.apply(obj) === '[object Object]';
  };

  var getObjDiff = function (a, b) {
    var aplus = [],
      bplus = [];

    if (!(typeof a == 'string') && !(typeof b == 'string')) {

      if (isArray(a)) {
        for (var i = 0; i < a.length; i++) {
          if (b[i] === undefined) aplus.push(i);
        }
      } else {
        for (var iNested in a) {
          if (a.hasOwnProperty(iNested)) {
            if (b[iNested] === undefined) {
              aplus.push(iNested);
            }
          }
        }
      }

      if (isArray(b)) {
        for (var j = 0; j < b.length; j++) {
          if (a[j] === undefined) bplus.push(j);
        }
      } else {
        for (var jNested in b) {
          if (b.hasOwnProperty(jNested)) {
            if (a[jNested] === undefined) {
              bplus.push(jNested);
            }
          }
        }
      }
    }

    return {
      added: aplus,
      removed: bplus
    };
  };

  var clone = function (obj) {

    if (null == obj || 'object' != typeof obj) {
      return obj;
    }

    var copy = obj.constructor();

    for (var attr in obj) {
      copy[attr] = obj[attr];
    }

    return copy;

  };

  var defineGetAndSet = function (obj, propName, getter, setter) {
    try {
      Object.observe(obj, function (changes) {
        changes.forEach(function (change) {
          if (change.name === propName) {
            setter(change.object[change.name]);
          }
        });
      });
    }
    catch (e) {
      try {
        Object.defineProperty(obj, propName, {
          get: getter,
          set: function (value) {
            setter.call(this, value, true); // coalesce changes
          },
          enumerable: true,
          configurable: true
        });
      }
      catch (e2) {
        try {
          Object.prototype.__defineGetter__.call(obj, propName, getter);
          Object.prototype.__defineSetter__.call(obj, propName, function (value) {
            setter.call(this, value, true); // coalesce changes
          });
        }
        catch (e3) {
          observeDirtyChanges(obj, propName, setter);
          //throw new Error("watchJS error: browser not supported :/")
        }
      }
    }
  };

  var defineProp = function (obj, propName, value) {
    try {
      Object.defineProperty(obj, propName, {
        enumerable: false,
        configurable: true,
        writable: false,
        value: value
      });
    } catch (error) {
      obj[propName] = value;
    }
  };

  var observeDirtyChanges = function (obj, propName, setter) {
    dirtyChecklist[dirtyChecklist.length] = {
      prop: propName,
      object: obj,
      orig: clone(obj[propName]),
      callback: setter
    };
  };

  var watch = function () {

    if (isFunction(arguments[1])) {
      watchAll.apply(this, arguments);
    } else if (isArray(arguments[1])) {
      watchMany.apply(this, arguments);
    } else {
      watchOne.apply(this, arguments);
    }

  };


  var watchAll = function (obj, watcher, level, addNRemove) {

    if ((typeof obj == 'string') || (!(obj instanceof Object) && !isArray(obj))) { //accepts only objects and array (not string)
      return;
    }

    if (isArray(obj)) {
      defineWatcher(obj, '__watchall__', watcher, level); // watch all changes on the array
      if (level === undefined || level > 0) {
        for (var prop = 0; prop < obj.length; prop++) { // watch objects in array
          watchAll(obj[prop], watcher, level, addNRemove);
        }
      }
    }
    else {
      var prop, props = []; // eslint-disable-line no-redeclare
      for (prop in obj) { //for each attribute if obj is an object
        if (prop == '$val' || (!supportDefineProperty && prop === 'watchers')) {
          continue;
        }

        if (Object.prototype.hasOwnProperty.call(obj, prop)) {
          props.push(prop); //put in the props
        }
      }
      watchMany(obj, props, watcher, level, addNRemove); //watch all items of the props
    }


    if (addNRemove) {
      pushToLengthSubjects(obj, '$$watchlengthsubjectroot', watcher, level);
    }
  };


  var watchMany = function (obj, props, watcher, level, addNRemove) {

    if ((typeof obj == 'string') || (!(obj instanceof Object) && !isArray(obj))) { //accepts only objects and array (not string)
      return;
    }

    for (var i = 0; i < props.length; i++) { //watch each property
      var prop = props[i];
      watchOne(obj, prop, watcher, level, addNRemove);
    }

  };

  var watchOne = function (obj, prop, watcher, level, addNRemove) {
    if ((typeof obj == 'string') || (!(obj instanceof Object) && !isArray(obj))) { //accepts only objects and array (not string)
      return;
    }

    if (isFunction(obj[prop])) { //dont watch if it is a function
      return;
    }
    if (obj[prop] != null && (level === undefined || level > 0)) {
      watchAll(obj[prop], watcher, level !== undefined ? level - 1 : level); //recursively watch all attributes of this
    }

    defineWatcher(obj, prop, watcher, level);

    if (addNRemove && (level === undefined || level > 0)) {
      pushToLengthSubjects(obj, prop, watcher, level);
    }

  };

  var unwatch = function () {

    if (isFunction(arguments[1])) {
      unwatchAll.apply(this, arguments);
    } else if (isArray(arguments[1])) {
      unwatchMany.apply(this, arguments);
    } else {
      unwatchOne.apply(this, arguments);
    }

  };

  var unwatchAll = function (obj, watcher) {

    if (obj instanceof String || (!(obj instanceof Object) && !isArray(obj))) { //accepts only objects and array (not string)
      return;
    }

    if (isArray(obj)) {
      var props = ['__watchall__'];
      for (var prop = 0; prop < obj.length; prop++) { //for each item if obj is an array
        props.push(prop); //put in the props
      }
      unwatchMany(obj, props, watcher); //watch all itens of the props
    } else {
      var unwatchPropsInObject = function (obj2) {
        var props = [];
        for (var prop2 in obj2) { //for each attribute if obj is an object
          if (obj2.hasOwnProperty(prop2)) {
            if (obj2[prop2] instanceof Object) {
              unwatchPropsInObject(obj2[prop2]); //recurs into object props
            } else {
              props.push(prop2); //put in the props
            }
          }
        }
        unwatchMany(obj2, props, watcher); //unwatch all of the props
      };
      unwatchPropsInObject(obj);
    }
  };


  var unwatchMany = function (obj, props, watcher) {

    for (var prop2 in props) { //watch each attribute of "props" if is an object
      if (props.hasOwnProperty(prop2)) {
        unwatchOne(obj, props[prop2], watcher);
      }
    }
  };

  var timeouts = [],
    timerID = null;

  function clearTimerID() {
    timerID = null;
    for (var i = 0; i < timeouts.length; i++) {
      timeouts[i]();
    }
    timeouts.length = 0;
  }

  var getTimerID = function () {
    if (!timerID) {
      timerID = setTimeout(clearTimerID);
    }
    return timerID;
  };
  var registerTimeout = function (fn) { // register function to be called on timeout
    if (timerID == null) getTimerID();
    timeouts[timeouts.length] = fn;
  };

  // Track changes made to an array, object or an object's property
  // and invoke callback with a single change object containing type, value, oldvalue and array splices
  // Syntax:
  //      trackChange(obj, callback, recursive, addNRemove)
  //      trackChange(obj, prop, callback, recursive, addNRemove)
  var trackChange = function () {
    var fn = (isFunction(arguments[2])) ? trackProperty : trackObject;
    fn.apply(this, arguments);
  };

  // track changes made to an object and invoke callback with a single change object containing type, value and array splices
  var trackObject = function (obj, callback, recursive, addNRemove) {
    var change = null, lastTimerID = -1;
    var isArr = isArray(obj);
    var level, fn = function (prop, action, newValue, oldValue) {
      var timerID = getTimerID();
      if (lastTimerID !== timerID) { // check if timer has changed since last update
        lastTimerID = timerID;
        change = {
          type: 'update'
        };
        change['value'] = obj;
        change['splices'] = null;
        registerTimeout(function () {
          callback.call(this, change);
          change = null;
        });
      }
      // create splices for array changes
      if (isArr && obj === this && change !== null) {
        if (action === 'pop' || action === 'shift') {
          newValue = [];
          oldValue = [oldValue];
        }
        else if (action === 'push' || action === 'unshift') {
          newValue = [newValue];
          oldValue = [];
        }
        else if (action !== 'splice') {
          return; // return here - for reverse and sort operations we don't need to return splices. a simple update will do
        }
        if (!change.splices) change.splices = [];
        change.splices[change.splices.length] = {
          index: prop,
          deleteCount: oldValue ? oldValue.length : 0,
          addedCount: newValue ? newValue.length : 0,
          added: newValue,
          deleted: oldValue
        };
      }

    };
    level = (recursive == true) ? undefined : 0;
    watchAll(obj, fn, level, addNRemove);
  };

  // track changes made to the property of an object and invoke callback with a single change object containing type, value, oldvalue and splices
  var trackProperty = function (obj, prop, callback, recursive, addNRemove) {
    if (obj && prop) {
      watchOne(obj, prop, function (prop, action, newvalue, oldvalue) {
        var change = {
          type: 'update'
        };
        change['value'] = newvalue;
        change['oldvalue'] = oldvalue;
        if (recursive && isObject(newvalue) || isArray(newvalue)) {
          trackObject(newvalue, callback, recursive, addNRemove);
        }
        callback.call(this, change);
      }, 0);

      if (recursive && isObject(obj[prop]) || isArray(obj[prop])) {
        trackObject(obj[prop], callback, recursive, addNRemove);
      }
    }
  };


  var defineWatcher = function (obj, prop, watcher, level) {
    var newWatcher = false;
    var isArr = isArray(obj);

    if (!obj.watchers) {
      defineProp(obj, 'watchers', {});
      if (isArr) {
        // watch array functions
        watchFunctions(obj, function (index, action, newValue, oldValue) {
          addPendingChange(obj, index, action, newValue, oldValue);
          if (level !== 0 && newValue && (isObject(newValue) || isArray(newValue))) {
            var i, n, ln, wAll, watchList = obj.watchers[prop];
            if ((wAll = obj.watchers['__watchall__'])) {
              watchList = watchList ? watchList.concat(wAll) : wAll;
            }
            ln = watchList ? watchList.length : 0;
            for (i = 0; i < ln; i++) {
              if (action !== 'splice') {
                watchAll(newValue, watchList[i], (level === undefined) ? level : level - 1);
              }
              else {
                // watch spliced values
                for (n = 0; n < newValue.length; n++) {
                  watchAll(newValue[n], watchList[i], (level === undefined) ? level : level - 1);
                }
              }
            }
          }
        });
      }
    }

    if (!obj.watchers[prop]) {
      obj.watchers[prop] = [];
      if (!isArr) newWatcher = true;
    }

    for (var i = 0; i < obj.watchers[prop].length; i++) {
      if (obj.watchers[prop][i] === watcher) {
        return;
      }
    }

    obj.watchers[prop].push(watcher); //add the new watcher to the watchers array

    if (newWatcher) {
      var val = obj[prop];
      var getter = function () {
        return val;
      };

      var setter = function (newval, delayWatcher) {
        var oldval = val;
        val = newval;
        if (level !== 0
                    && obj[prop] && (isObject(obj[prop]) || isArray(obj[prop]))
                    && !obj[prop].watchers) {
          // watch sub properties
          var i, ln = obj.watchers[prop].length;
          for (i = 0; i < ln; i++) {
            watchAll(obj[prop], obj.watchers[prop][i], (level === undefined) ? level : level - 1);
          }
        }

        //watchFunctions(obj, prop);

        if (isSuspended(obj, prop)) {
          resume(obj, prop);
          return;
        }

        if (!WatchJS.noMore) { // this does not work with Object.observe
          //if (JSON.stringify(oldval) !== JSON.stringify(newval)) {
          if (oldval !== newval) {
            if (!delayWatcher) {
              callWatchers(obj, prop, 'set', newval, oldval);
            }
            else {
              addPendingChange(obj, prop, 'set', newval, oldval);
            }
            WatchJS.noMore = false;
          }
        }
      };

      if (WatchJS.useDirtyCheck) {
        observeDirtyChanges(obj, prop, setter);
      }
      else {
        defineGetAndSet(obj, prop, getter, setter);
      }
    }

  };

  var callWatchers = function (obj, prop, action, newval, oldval) {
    if (prop !== undefined) {
      var ln, wl, watchList = obj.watchers[prop];
      if ((wl = obj.watchers['__watchall__'])) {
        watchList = watchList ? watchList.concat(wl) : wl;
      }
      ln = watchList ? watchList.length : 0;
      for (var wr = 0; wr < ln; wr++) {
        watchList[wr].call(obj, prop, action, newval, oldval);
      }
    } else {
      // call all
      for (var prop in obj) { // eslint-disable-line no-redeclare
        if (obj.hasOwnProperty(prop)) {
          callWatchers(obj, prop, action, newval, oldval);
        }
      }
    }
  };

  var methodNames = ['pop', 'push', 'reverse', 'shift', 'sort', 'slice', 'unshift', 'splice'];
  var defineArrayMethodWatcher = function (obj, original, methodName, callback) {
    defineProp(obj, methodName, function () {
      var index = 0;
      var i, newValue, oldValue, response;
      // get values before splicing array
      if (methodName === 'splice') {
        var start = arguments[0];
        var end = start + arguments[1];
        oldValue = obj.slice(start, end);
        newValue = [];
        for (i = 2; i < arguments.length; i++) {
          newValue[i - 2] = arguments[i];
        }
        index = start;
      }
      else {
        newValue = arguments.length > 0 ? arguments[0] : undefined;
      }

      response = original.apply(obj, arguments);
      if (methodName !== 'slice') {
        if (methodName === 'pop') {
          oldValue = response;
          index = obj.length;
        }
        else if (methodName === 'push') {
          index = obj.length - 1;
        }
        else if (methodName === 'shift') {
          oldValue = response;
        }
        else if (methodName !== 'unshift' && newValue === undefined) {
          newValue = response;
        }
        callback.call(obj, index, methodName, newValue, oldValue);
      }
      return response;
    });
  };

  var watchFunctions = function (obj, callback) {

    if (!isFunction(callback) || !obj || (obj instanceof String) || (!isArray(obj))) {
      return;
    }

    for (var i = methodNames.length, methodName; i--;) {
      methodName = methodNames[i];
      defineArrayMethodWatcher(obj, obj[methodName], methodName, callback);
    }

  };

  var unwatchOne = function (obj, prop, watcher) {
    if (obj.watchers[prop]) {
      if (watcher === undefined) {
        delete obj.watchers[prop]; // remove all property watchers
      }
      else {
        for (var i = 0; i < obj.watchers[prop].length; i++) {
          var w = obj.watchers[prop][i];

          if (w == watcher) {
            obj.watchers[prop].splice(i, 1);
          }
        }
      }
    }
    removeFromLengthSubjects(obj, prop, watcher);
    removeFromDirtyChecklist(obj, prop);
  };

    // suspend watchers until next update cycle
  var suspend = function (obj, prop) {
    if (obj.watchers) {
      var name = '__wjs_suspend__' + (prop !== undefined ? prop : '');
      obj.watchers[name] = true;
    }
  };

  var isSuspended = function (obj, prop) {
    return obj.watchers
            && (obj.watchers['__wjs_suspend__'] ||
            obj.watchers['__wjs_suspend__' + prop]);
  };

  // resumes preivously suspended watchers
  var resume = function (obj, prop) {
    registerTimeout(function () {
      delete obj.watchers['__wjs_suspend__'];
      delete obj.watchers['__wjs_suspend__' + prop];
    });
  };

  var pendingTimerID = null;
  var addPendingChange = function (obj, prop, mode, newval, oldval) {
    pendingChanges[pendingChanges.length] = {
      obj: obj,
      prop: prop,
      mode: mode,
      newval: newval,
      oldval: oldval
    };
    if (pendingTimerID === null) {
      pendingTimerID = setTimeout(applyPendingChanges);
    }
  };


  var applyPendingChanges = function () {
    // apply pending changes
    var change = null;
    pendingTimerID = null;
    for (var i = 0; i < pendingChanges.length; i++) {
      change = pendingChanges[i];
      callWatchers(change.obj, change.prop, change.mode, change.newval, change.oldval);
    }
    if (change) {
      pendingChanges = [];
      change = null;
    }
  };

  var loop = function () {

    // check for new or deleted props
    for (var i = 0; i < lengthsubjects.length; i++) {

      var subj = lengthsubjects[i];

      if (subj.prop === '$$watchlengthsubjectroot') {

        var difference = getObjDiff(subj.obj, subj.actual);

        if (difference.added.length || difference.removed.length) {
          if (difference.added.length) {
            watchMany(subj.obj, difference.added, subj.watcher, subj.level - 1, true);
          }

          subj.watcher.call(subj.obj, 'root', 'differentattr', difference, subj.actual);
        }
        subj.actual = clone(subj.obj);


      } else {

        difference = getObjDiff(subj.obj[subj.prop], subj.actual);

        if (difference.added.length || difference.removed.length) {
          if (difference.added.length) {
            for (var j = 0; j < subj.obj.watchers[subj.prop].length; j++) {
              watchMany(subj.obj[subj.prop], difference.added, subj.obj.watchers[subj.prop][j], subj.level - 1, true);
            }
          }

          callWatchers(subj.obj, subj.prop, 'differentattr', difference, subj.actual);
        }

        subj.actual = clone(subj.obj[subj.prop]);

      }

    }

    // start dirty check
    var n, value;
    if (dirtyChecklist.length > 0) {
      for (var i = 0; i < dirtyChecklist.length; i++) {  // eslint-disable-line no-redeclare
        n = dirtyChecklist[i];
        value = n.object[n.prop];
        if (!compareValues(n.orig, value)) {
          n.orig = clone(value);
          n.callback(value);
        }
      }
    }

  };

  var compareValues = function (a, b) {
    var i, state = true;
    if (a !== b) {
      if (isObject(a)) {
        for (i in a) {
          if (!supportDefineProperty && i === 'watchers') continue;
          if (a[i] !== b[i]) {
            state = false;
            break;
          }
          
        }
      }
      else {
        state = false;
      }
    }
    return state;
  };

  var pushToLengthSubjects = function (obj, prop, watcher, level) {

    var actual;

    if (prop === '$$watchlengthsubjectroot') {
      actual = clone(obj);
    } else {
      actual = clone(obj[prop]);
    }

    lengthsubjects.push({
      obj: obj,
      prop: prop,
      actual: actual,
      watcher: watcher,
      level: level
    });
  };

  var removeFromLengthSubjects = function (obj, prop, watcher) {

    for (var i = 0; i < lengthsubjects.length; i++) {
      var subj = lengthsubjects[i];

      if (subj.obj == obj && subj.prop == prop && subj.watcher == watcher) {
        lengthsubjects.splice(i, 1);
      }
    }

  };

  var removeFromDirtyChecklist = function (obj, prop) {
    var notInUse;
    for (var i = 0; i < dirtyChecklist.length; i++) {
      var n = dirtyChecklist[i];
      var watchers = n.object.watchers;
      notInUse = (
        n.object == obj
                && n.prop == prop
                && watchers
                && ( !watchers[prop] || watchers[prop].length == 0 )
      );
      if (notInUse) {
        dirtyChecklist.splice(i, 1);
      }
    }

  };

  setInterval(loop, 50);

  WatchJS.watch = watch;
  WatchJS.unwatch = unwatch;
  WatchJS.callWatchers = callWatchers;
  WatchJS.suspend = suspend; // suspend watchers
  WatchJS.onChange = trackChange;  // track changes made to object or  it's property and return a single change object

  return WatchJS;

}));
(function ($) {
  'use strict';

  $(function () {

    if (typeof redux != 'undefined') {
      watch(redux, 'options', function () {
        $('#crane_backup_import_code_textarea').val(JSON.stringify(redux.options));
      }, 6, true);

      redux.field_objects = redux.field_objects || {};
      redux.field_objects.crane_backup_import = redux.field_objects.crane_backup_import || {};

      redux.field_objects.crane_backup_import.init = function (selector) {

        if (!selector) {
          selector = $(document)
            .find('.redux-group-tab:visible')
            .find('.redux-container-crane_backup_import:visible');
        }

        $(selector).each(
          function () {
            var el = $(this);
            var parent = el;
            if (!el.hasClass('redux-field-container')) {
              parent = el.parents('.redux-field-container:first');
            }

            if (parent.is(':hidden')) { // Skip hidden fields
              return;
            }

            if (parent.hasClass('redux-field-init')) {
              parent.removeClass('redux-field-init');
            } else {
              return;
            }

            el.each(
              function () {

                // Save backup
                $(this).find('.crane-backup_btn_save').on('click', function (e) {
                  e.preventDefault();
                  var _this_btn = $(this);
                  if (_this_btn.hasClass('disabled')) {
                    return;
                  }
                  var confirm_yes = el.find('.crane-backup_copy_status').hasClass('crane_backup_is_empty');
                  if (!confirm_yes) {
                    confirm_yes = confirm(crane_js_l10n['are_u_sure']);
                  }

                  if (confirm_yes) {
                    _this_btn.addClass('disabled');
                    var crane_salt = el.find('#crane_backup_import_salt').val();
                    $.ajax({
                      type: 'POST',
                      url: ajaxurl,
                      dataType: 'json',
                      data: {
                        action: 'crane_backup_btn_save',
                        crane_salt: crane_salt
                      },
                      error: function () {
                        _this_btn.removeClass('disabled');
                        alert(redux.ajax.alert);
                      },
                      success: function (result) {
                        if (result.success) {
                          _this_btn.removeClass('disabled');
                          el
                            .find('.crane-backup_copy_status')
                            .html(result.data.status)
                            .removeClass('crane_backup_is_empty');
                          el
                            .find('.crane-backup_btn_restore')
                            .removeClass('hidden');
                        }
                        alert(result.data.message);
                      }.bind(this)
                    });
                  }
                });

                // Restore backup
                $(this).find('.crane-backup_btn_restore').on('click', function (e) {
                  e.preventDefault();
                  var _this_btn = $(this);
                  if (_this_btn.hasClass('disabled')) {
                    return;
                  }
                  var confirm_yes = confirm(crane_js_l10n['are_u_sure']);

                  if (confirm_yes) {
                    _this_btn.addClass('disabled');
                    var crane_salt = el.find('#crane_backup_import_salt').val();
                    $.ajax({
                      type: 'POST',
                      url: ajaxurl,
                      dataType: 'json',
                      data: {
                        action: 'crane_backup_btn_restore',
                        crane_salt: crane_salt
                      },
                      error: function () {
                        _this_btn.removeClass('disabled');
                        alert(redux.ajax.alert);
                      },
                      success: function (result) {
                        if (result.success) {
                          location.reload(true);
                        }
                        _this_btn.removeClass('disabled');
                        alert(result.data.message);
                      }.bind(this)
                    });
                  }
                });

                // Import
                $(this).find('.crane-backup_btn_import').on('click', function (e) {
                  e.preventDefault();
                  var _this_btn = $(this);
                  if (_this_btn.hasClass('disabled')) {
                    return;
                  }
                  var confirm_yes = confirm(crane_js_l10n['are_u_sure']);

                  if (confirm_yes) {
                    _this_btn.addClass('disabled');
                    var crane_salt = el.find('#crane_backup_import_salt').val();
                    var import_data = el.find('#crane_backup_import_code_textarea').val();
                    $.ajax({
                      type: 'POST',
                      url: ajaxurl,
                      dataType: 'json',
                      data: {
                        action: 'crane_backup_btn_import',
                        import_data: import_data,
                        crane_salt: crane_salt
                      },
                      error: function () {
                        _this_btn.removeClass('disabled');
                        alert(redux.ajax.alert);
                      },
                      success: function (result) {
                        if (result.success) {
                          location.reload(true);
                        }
                        _this_btn.removeClass('disabled');
                        alert(result.data.message);
                      }.bind(this)
                    });
                  }
                });

              }
            );
          }
        );
      };

    }
  });
})(jQuery);
(function ($) {
  'use strict';

  var CTSidebarCreator = function () {

    this.sidebarsWrapper = $('.widget-liquid-right');
    this.customSidebars = $('#widgets-right .sidebar-crane-custom-sidebar');

    this.initForm();
    this.initCustomWidgets();
    this.bindEvents();

  };

  CTSidebarCreator.prototype = {

    initForm: function () {
      this.sidebarsWrapper.append($('.crane-sb__add-new-wrapper'));
      this.nonce = this.sidebarsWrapper.find('input[name="crane-sb__actions_nonce"]').val();
    },

    initCustomWidgets: function () {
      this.customSidebars.append('<span class="crane-sb__btn-edit" title="' + crane_js_l10n.edit_sidebar_btn + '"></span><span class="crane-sb__btn-delete" title="' + crane_js_l10n.delete_sidebar_btn + '"></span>');
    },

    bindEvents: function () {
      this.sidebarsWrapper.on('click', '.crane-sb__btn-delete', $.proxy(this.deleteSidebar, this));
      this.sidebarsWrapper.on('click', '.crane-sb__btn-edit', $.proxy(this.editSidebar, this));
      this.sidebarsWrapper.on('click', '.crane-sb__add-new', $.proxy(this.addNewBtn, this));
      this.sidebarsWrapper.on('click', '#crane-sb__create-new-btn', $.proxy(this.createSidebar, this));
    },

    createSidebar: function () {

      var sidebarName = $('#crane-sb__name').val(),
        obj = this;

      if ( ! sidebarName ) {
        alert(crane_js_l10n.sidebar_name_empty);
        return false;
      }

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'crane_add_sidebar',
          name: sidebarName,
          _wpnonce: obj.nonce
        },
        success: function (response) {
          if (response !== 'ok') {
            alert(response);
          } else {
            location.reload();
          }
        }
      });
    },

    deleteSidebar: function (e) {
      var deleteIt = confirm(crane_js_l10n.delete_sidebar);

      if (deleteIt == false) return false;

      var widget = $(e.currentTarget).parent('.sidebar-crane-custom-sidebar'),
        title = widget.find('.sidebar-name h3 , .sidebar-name h2'),
        sidebarId = widget.children().first().attr('id'),
        widgetName = $.trim(title.text()),
        obj = this;

      if (!sidebarId) {
        return false;
      }

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'crane_delete_sidebar',
          name: widgetName,
          sidebar_id: sidebarId,
          _wpnonce: obj.nonce
        },
        success: function (response) {
          if (response == 'ok') {
            widget.slideUp(200, function () {

              $('.widget-control-remove', widget).trigger('click'); //delete all widgets inside
              widget.remove();

            });
          }
        }
      });
    },

    editSidebar: function (e) {
      var widget = $(e.currentTarget).parent('.sidebar-crane-custom-sidebar'),
        title = widget.find('.sidebar-name h3 , .sidebar-name h2'),
        sidebarId = widget.children().first().attr('id'),
        widgetName = $.trim(title.text()),
        obj = this;

      var newSidebarName = prompt(crane_js_l10n.sidebar_new_name, widgetName);

      if (!newSidebarName) {
        alert(crane_js_l10n.sidebar_name_empty);
        return false;
      }

      if (!sidebarId) {
        return false;
      }

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'crane_rename_sidebar',
          name: newSidebarName,
          sidebar_id: sidebarId,
          _wpnonce: obj.nonce
        },
        success: function (response) {
          if (response !== 'ok') {
            alert(response);
          } else {
            title.text(newSidebarName);
          }
        }
      });

    },

    addNewBtn: function () {
      $('.crane-sb__name-wrapper').show(250);
      $('.crane-sb__add-new').hide(150);
    }

  };

  $(function () {
    new CTSidebarCreator();
  });

})(jQuery);
(function ($) {
  'use strict';
  
  $(function () {


    $('.crane-color-picker').wpColorPicker();


    // Type: NUMBER -------------------------------------------------------
    $('.crane-form-meta-type__number').each(function () {
      var $_this = $(this);
      var $_default = $_this.find('.crane-form-meta-type__number-default');
      var $_value = $_this.find('.crane-form-meta-type__number-value');
      var $_main = $_this.find('.crane-form-meta-type__number-main');

      if ($_main.val() == 'default') {
        $_default.prop('checked', true);
        $_value.hide();
        $_main.val('default');
      } else {
        $_default.prop('checked', false);
        $_value.show();
        $_main.val($_value.val());
      }

      $_default.on('change', function () {
        $_value.toggle();

        if ($(this).prop('checked')) {
          $_value.hide();
          $_main.val('default');
        } else {
          $_value.show();
          $_main.val($_value.val());
        }
      });

      $_value.on('change', function () {
        $_main.val($(this).val());
      });

    });

    // Type: COLOR -------------------------------------------------------
    $('.crane-form-meta-type__color').each(function () {
      var $_this = $(this);
      var $_default = $_this.find('.crane-form-meta-type__color-default');
      var $_value   = $_this.find('.crane-form-meta-type__color-value');
      var $_wrapper = $_this.find('.crane-form-meta-type__color-value-wrapper');

      if ($_value.val() == 'default') {
        $_default.prop('checked', true);
        $_wrapper.hide();
      } else {
        $_default.prop('checked', false);
        $_wrapper.show();
      }

      $_default.on('change', function () {
        $_wrapper.toggle();

        if ($(this).prop('checked')) {
          $_wrapper.hide();
          $_value.val('default');
        } else {
          $_wrapper.show();
        }
      });

    });


    // Type: TEXT -------------------------------------------------------
    $('.crane-form-meta-type__text').each(function () {
      var $_this = $(this);
      var $_default = $_this.find('.crane-form-meta-type__text-default');
      var $_value = $_this.find('.crane-form-meta-type__text-value');
      var $_wrapper = $_this.find('.crane-form-meta-type__text-value-wrapper');

      if ($_value.val() == 'default') {
        $_default.prop('checked', true);
        $_wrapper.hide();
      } else {
        $_default.prop('checked', false);
        $_wrapper.show();
      }

      $_default.on('change', function () {
        $_wrapper.toggle();

        if ($(this).prop('checked')) {
          $_wrapper.hide();
          $_value.val('default');
        } else {
          $_wrapper.show();
          $_value.val('');
        }
      });

    });

    // Type: PADDING -------------------------------------------------------
    $('.crane-form-meta-type__padding').each(function () {
      var $_this = $(this);
      var $_default = $_this.find('.crane-form-meta-type__padding-default');
      var $_values = $_this.find('.crane-form-meta-type__padding-values');
      var $_value_top = $_this.find('.crane-form-meta-type__padding-value--top');
      var $_value_bottom = $_this.find('.crane-form-meta-type__padding-value--bottom');
      var $_value_units = $_this.find('.crane-form-meta-type__padding-value--units');
      var $_main = $_this.find('.crane-form-meta-type__padding-main');

      if ($_main.val() == 'default') {
        $_default.prop('checked', true);
        $_values.hide();
        $_main.val('default');
      } else {
        $_default.prop('checked', false);
        $_values.show();
        $_main.val($_value_top.val() + '|' + $_value_bottom.val() + '|' + $_value_units.val());
      }

      $_default.on('change', function () {
        $_values.toggle();

        if ($(this).prop('checked')) {
          $_values.hide();
          $_main.val('default');
        } else {
          $_values.show();
          $_main.val($_value_top.val() + '|' + $_value_bottom.val() + '|' + $_value_units.val());
        }
      });

      $_value_top.on('change', function () {
        $_main.val($_value_top.val() + '|' + $_value_bottom.val() + '|' + $_value_units.val());
      });
      $_value_bottom.on('change', function () {
        $_main.val($_value_top.val() + '|' + $_value_bottom.val() + '|' + $_value_units.val());
      });
      $_value_units.on('change', function () {
        $_main.val($_value_top.val() + '|' + $_value_bottom.val() + '|' + $_value_units.val());
      });

    });

    // ----- custom_options
    $('#term_meta__custom_options').on('change', function () {
      $('.term_meta__custom_options__field').toggleClass('crane-hide_custom');
      if ($(this).prop('checked')) {
        $('#term_meta__custom_options__val').val('1');
      } else {
        $('#term_meta__custom_options__val').val('0');
      }
    });
    if ($('#term_meta__custom_options__val').val() == '0') {
      $('.term_meta__custom_options__field').addClass('crane-hide_custom');
    } else {
      $('#term_meta__custom_options').prop('checked', true);
    }

    // ----- has_sidebar
    $('#term_meta__has_sidebar').on('change', function () {
      $('.term_meta__sidebar').toggleClass('crane-hide_sidebar', ($('#term_meta__has_sidebar').val() == 'none' || $('#term_meta__has_sidebar').val() == 'default'));
    });
    if ($('#term_meta__has_sidebar').val() == 'none' || $('#term_meta__has_sidebar').val() == 'default') {
      $('.term_meta__sidebar').addClass('crane-hide_sidebar');
    }

    // ----- sortable
    $('#crane_term_meta__sortable').on('change', function () {
      $('.term_meta__sortable_depend').toggleClass('crane-hide_sidebar', ($('#crane_term_meta__sortable').val() == '0' || $('#crane_term_meta__sortable').val() == 'default'));
    });
    if ($('#crane_term_meta__sortable').val() == 'none' || $('#crane_term_meta__sortable').val() == 'default') {
      $('.term_meta__sortable_depend').addClass('crane-hide_sidebar');
    }

    // ----- sortable_style_depend
    $('#crane_term_meta__sortable_style').on('change', function () {
      $('.term_meta__sortable_style_depend').toggleClass('crane-hide_sidebar', ($('#crane_term_meta__sortable_style').val() !== 'outline'));
    });
    if ($('#crane_term_meta__sortable_style').val() !== 'outline') {
      $('.term_meta__sortable_style_depend').addClass('crane-hide_sidebar');
    }

    // ----- pagination_type_depend
    $('#crane_term_meta__pagination_type').on('change', function () {
      $('.term_meta__pagination_type_depend').toggleClass('crane-hide_sidebar', ($('#crane_term_meta__pagination_type').val() !== 'show_more'));
    });
    if ($('#crane_term_meta__pagination_type').val() !== 'show_more') {
      $('.term_meta__pagination_type_depend').addClass('crane-hide_sidebar');
    }

    // ----- template_depend
    function crane_update_template_depend_status() {
      $('.term_meta__template_depend').addClass('crane-hide_by_template');
      var template = $('#term_meta__template').val();
      if (template !== '') {
        $('.crane-is-' + template).removeClass('crane-hide_by_template');
      }
    }

    $('#term_meta__template').on('change', function () {
      crane_update_template_depend_status();
    });

    crane_update_template_depend_status();

    // ----- category-color-set
    $('#category-color-set').on('change', function () {
      $('#category-color-wrap').toggleClass('hide', ($('#category-color-set').val() != 'custom'));
    });

    if ($('#category-color-set').val() != 'custom') {
      $('#category-color-wrap').addClass('hide');
    }

    // ----- template_depend
    $('#term_meta__layout').on('change', function () {
      $('.term_meta__masonry_depend').toggleClass('crane-hide_by_layout', ($('#term_meta__layout').val() != 'masonry'));
      $('.term_meta__grid_depend').toggleClass('crane-hide_by_layout', ($('#term_meta__layout').val() != 'grid'));
    });

    if ($('#term_meta__layout').val() != 'masonry') {
      $('.term_meta__masonry_depend').addClass('crane-hide_by_layout');
    }

    if ($('#term_meta__layout').val() != 'grid') {
      $('.term_meta__grid_depend').addClass('crane-hide_by_layout');
    }

    // ----- product_attributes
    /**
     * Return an array of the selected option values
     */
    function crane_getSelectValues(select_element) {
      var selectedValues = [];
      select_element.each(function () {
        selectedValues.push($(this).val());
      });
      return selectedValues;
    }

    $('#term_meta__product_attributes').on('change', function () {
      $('#term_meta__product_attributes__val').val(crane_getSelectValues($('#term_meta__product_attributes')));
    });

    var selectedValues = $('#term_meta__product_attributes__val').val();
    if (selectedValues) {
      $.each(selectedValues.split(','), function (i, e) {
        $('#term_meta__product_attributes option[value=\'' + e + '\']').prop('selected', true);
      });
    }

    // ----- crane-imgtag -----
    var file_frame = [],
      $button = $('.crane-imgtag-upload-button'),
      $removebutton = $('.crane-imgtag-button-remove');

    $button.on('click', function (event) {
      event.preventDefault();

      var $this = $(this),
        id = $this.attr('id');

      // If the media frame already exists, reopen it.
      if (file_frame[id]) {
        file_frame[id].open();

        return;
      }

      // Create the media frame.
      file_frame[id] = wp.media.frames.file_frame = wp.media({
        title: $this.data('uploader_title'),
        button: {
          text: $this.data('uploader_button_text')
        },
        multiple: false
      });

      // When an image is selected, run a callback.
      file_frame[id].on('select', function () {
        var attachment = file_frame[id].state().get('selection').first().toJSON();
        $('#' + id + '-value').val(attachment.id);
        var img = '<img src="' + attachment.url + '" style="max-width:' + crane_image_width_thumbnail + 'px;" alt="" />';
        $this.next('input').next('.crane-imgtag-image-preview').html(img);
      });

      file_frame[id].open();
    });

    $removebutton.on('click', function (event) {
      event.preventDefault();

      var $this = $(this),
        id = $this.prev('input').attr('id');

      $this.next('.crane-imgtag-image-preview').html('');
      $('#' + id + '-value').val(0);
    });

  });
})(jQuery);
(function ($) {
  'use strict';

  $(function ($) {

    var current;
    var field_id;
    $('.crane-groovy-menu-select-wrapper .preset--current .preset-placeholder').on('click', function () {
      current = $(this).closest('.preset--current');
      field_id = $(this).attr('data-modal-id');

      if (field_id) {
        field_id = '#' + field_id;
        $(field_id + '-preset-modal').modal('show');
      }

      return false;
    });


    $('.groovy-menu-redux-modal .preset:not(.preset--current) .preset-placeholder').on('click', function () {
      var preset = $(this).closest('.preset');

      current.find('.preset-placeholder img').attr('src', preset.find('img').attr('src'));
      current.find('.preset-title__alpha').html(preset.find('.preset-title__alpha').html());
      current.data('id', preset.data('id'));
      current.data('id', preset.data('id'));
      $(field_id+'-value').val(preset.data('id'));

      $(field_id + '-preset-modal').modal('hide');

      current.closest('.grooni-metabox-row').find('.grooni-metabox-groovy-value').val(preset.data('id')).change();
      return false;
    });

  });
})(jQuery);
/** Script for crane_add_size (extension for Redux)
 *
 *  Adds fields for custom size
 *
 */

(function ($) {
  'use strict';
  
  $(function ($) {

    function craneAddImageSizeChange() {
      $('.crane-add-image-sizes-group input').on('change', function () {

        var imageSizes = [];
        var $wrapper = $(this).closest('.crane-add-image-sizes-wrapper');

        $.each($wrapper.find('.crane-add-image-size-group-element'), function (i, groupElement) {

          var element    = {};
          element.id = $(this).data('id');
          element.width  = $(groupElement).find('input[data-name="width"]').val();
          element.height = $(groupElement).find('input[data-name="height"]').val();
          element.crop   = $(groupElement).find('input[data-name="crop"]').attr('checked') ? 1 : 0;
          imageSizes.push(element);

        });

        var $valueTag = $wrapper.find('.crane-image-sizes-value');
        $valueTag.val(JSON.stringify(imageSizes));

      });
    }

    function randId() {
      return Math.random().toString(36).substr(2, 10);
    }

    function deleteImageSize() {

      var self = $(this);
      var sizeToDelete = self.closest('.crane-add-image-size-group-element').data('id');
      var imgSizesDom = $('.crane-image-sizes-value');
      var imgSizes = JSON.parse(imgSizesDom.val());
      var prevImgSizes = Array.from(imgSizes);
      var newImgSizes = $.grep(prevImgSizes, function(imgSize){
        return imgSize.id !== sizeToDelete;
      });

      imgSizesDom.val(JSON.stringify(newImgSizes));

      $.each(imgSizes, function(k, v) {
        if (v.id === sizeToDelete) {
          self.closest('.crane-add-image-size-group-element').remove();
        }
      });

    }

    $('.crane-del-image-size').on('click', deleteImageSize);

    $('.crane-add-more-size-btn').on('click', function () {
      var $wrapper = $(this).parent();
      $wrapper.find('.crane-add-image-sizes-group').append(
        '<div class=\'crane-add-image-size-group-element\' data-id=\'' + randId() + '\'>' +
        '<label>Width <input data-name=\'width\' type=\'number\' min=\'0\' max=\'9999\' value=\'600\' required /></label>' +
        '<label>Height <input data-name=\'height\' type=\'number\' min=\'0\' max=\'9999\' value=\'600\' required /></label>' +
        '<label>Crop image<input data-name=\'crop\' type=\'checkbox\' /></label>' +
        '<span class=\'crane-del-image-size button button-primary\'>X</span>' +
        '</div>');
      craneAddImageSizeChange();
    });

    craneAddImageSizeChange();

  });
})(jQuery);
(function ($) {
  'use strict';

  $(function () {

    $('.gm-show-preset-preview').on('click', function () {

      var id = $(this).closest('.preset').data('id');
      var url = groovyMenuLocalize.GroovyMenuAdminUrl + '&action=preview&id=' + id;
      var modalId = '#' + $(this).attr('data-showmodal');

      $(modalId).find('.modal-body-iframe')
        .html('<iframe id="groovy-preview-iframe" frameborder="0" scrolling="no" width="100%" height="100%" src="' + url + '"></iframe>');
      $(modalId).modal('show');
      // add name to preset preview frame
      $('#preview-modal .modal-preview-name').html($(this).closest('.preset').data('name'));

    });

    // reset states in preview mode after close modal
    $('.crane-groovy-menu-select-wrapper .close').on('click', resetModalState);

    function resetModalState() {

      //remove active class from all settings
      $('.preview-size-change').find('.active').removeClass('active');
      // reset sticky setting
      $('.preview-sticky-change__tabs').find('[data-sticky="false"]').addClass('active');
      // reset bg color setting
      $('.preview-color-change__tabs').find('[data-color="transparent"]').addClass('active');
      // reset size setting
      $('.preview-size-change__tabs').find('[data-size="desktop"]').addClass('active');
      $(this).closest('.modal-body').removeClass('iframe--size-tablet').addClass('iframe--size-desktop');
      $('.modal-body-iframe').width('');
    }

  });

})(jQuery);