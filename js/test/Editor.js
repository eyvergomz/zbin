'use strict';
require('../common');

describe('Editor', function () {
    describe('show, hide, getText, setText & isPreview', function () {
        this.timeout(30000);

        jsc.property(
            'returns text fed into the textarea, handles editor tabs',
            'string',
            function (text) {
                var clean = jsdom(),
                    results = [];
                $('body').html(
                    '<ul id="editorTabs" class="nav nav-tabs hidden"><li ' +
                    'role="presentation" class="active"><a id="messageedit" ' +
                    'href="#">Editor</a></li><li role="presentation"><a ' +
                    'id="messagepreview" href="#">Preview</a></li></ul><div ' +
                    'id="placeholder" class="hidden">+++ no document text +++</div>' +
                    '<div id="prettymessage" class="hidden"><pre id="prettyprint" ' +
                    'class="prettyprint linenums:1"></pre></div><div ' +
                    'id="plaintext" class="hidden"></div><p><textarea ' +
                    'id="message" name="message" cols="80" rows="25" ' +
                    'class="form-control hidden"></textarea></p>'
                );
                $.Zbin.Editor.init();
                results.push(
                    $('#editorTabs').hasClass('hidden') &&
                    $('#message').hasClass('hidden')
                );
                $.Zbin.Editor.show();
                results.push(
                    !$('#editorTabs').hasClass('hidden') &&
                    !$('#message').hasClass('hidden')
                );
                $.Zbin.Editor.hide();
                results.push(
                    $('#editorTabs').hasClass('hidden') &&
                    $('#message').hasClass('hidden')
                );
                $.Zbin.Editor.show();
                $.Zbin.Editor.focusInput();
                results.push(
                    $.Zbin.Editor.getText().length === 0
                );
                $.Zbin.Editor.setText(text);
                results.push(
                    $.Zbin.Editor.getText() === $('#message').val()
                );
                $.Zbin.Editor.setText();
                results.push(
                    !$.Zbin.Editor.isPreview() &&
                    !$('#message').hasClass('hidden')
                );
                $('#messagepreview').trigger('click');
                results.push(
                    $.Zbin.Editor.isPreview() &&
                    $('#message').hasClass('hidden')
                );
                $('#messageedit').trigger('click');
                results.push(
                    !$.Zbin.Editor.isPreview() &&
                    !$('#message').hasClass('hidden')
                );
                clean();
                return results.every(element => element);
            }
        );
    });
});
