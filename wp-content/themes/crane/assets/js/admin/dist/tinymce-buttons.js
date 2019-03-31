(function () {
  'use strict';
  
  tinymce.PluginManager.add('quote_with_author', function (editor, url) {
    // Add Button to Visual Editor Toolbar
    editor.addButton('quote_with_author', {
      title: crane_js_l10n['quote_with_author'],
      cmd: 'quote_with_author',
      image: url + '/../../../images/quote-circle.png'
    });

    editor.addCommand('quote_with_author', function () {
      var selected_text = editor.selection.getContent({
        'format': 'html'
      });
      if (selected_text.length === 0) {
        alert(crane_js_l10n['select_some_quote_text']);
        return;
      }

      editor.windowManager.open({
        title: crane_js_l10n['quote_with_author'],
        body: [
          {
            type: 'textbox',
            name: 'quote',
            placeholder: crane_js_l10n['quote_text'],
            value: selected_text,
            multiline: true,
            minWidth: jQuery(window).width() * 0.65,
            minHeight: 120
          },
          {
            type: 'textbox',
            name: 'authorName',
            placeholder: crane_js_l10n['quote_author_name'],
            multiline: false,
            minWidth: jQuery(window).width() * 0.65,
            minHeight: 30
          },
          {
            type: 'textbox',
            name: 'authorUrl',
            placeholder: crane_js_l10n['quote_author_url'],
            multiline: false,
            minWidth: jQuery(window).width() * 0.65,
            minHeight: 30
          },

        ],
        onsubmit: function (e) {

          if (e.data.quote) {

            var quoteText = e.data.quote;
            var citeAuthor = '';
            if (e.data.authorName && !e.data.authorUrl) {
              citeAuthor = e.data.authorName;
            } else if (!e.data.authorName && e.data.authorUrl) {
              citeAuthor = '<a href="' + e.data.authorUrl + '">' + e.data.authorUrl + '</a>';
            } else if (e.data.authorName && e.data.authorUrl) {
              citeAuthor = '<a href="' + e.data.authorUrl + '">' + e.data.authorName + '</a>';
            }

            citeAuthor = citeAuthor ? ' <br><cite>' + citeAuthor + '</cite>' : '';
            quoteText = '<blockquote>' + quoteText + citeAuthor + '</blockquote>';

            editor.execCommand('mceReplaceContent', false, quoteText);

          } else {
            alert(crane_js_l10n['quote_must_not_empty']);
            return;
          }

        }
      });

      return;
    });

  });
  
})();