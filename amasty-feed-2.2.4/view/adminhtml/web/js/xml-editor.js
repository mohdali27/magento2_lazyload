define([
        'Amasty_Feed/js/code_mirror/lib/codemirror',
        'Amasty_Feed/js/code_mirror/addon/mode/simple',
        'prototype'
    ], function (CodeMirror) {

        CodeMirror.defineSimpleMode("amasty_feed", {
            start: [
                {regex: /"(?:[^\\]|\\.)*?"/, token: "atom"},
                {
                    regex: /(?:attribute|format|optional|parent|modify)\b/,
                    token: "string"
                },
                {regex: /attribute|custom_field|text|images/, token: "atom"},
                {regex: /\<!\[CDATA\[/, token: "amcdata", next: "amcdata"},
                {regex: /\</, token: "amtag", next: "amtag"},

                {regex: /[\{|%]/, token: "def"},
                {regex: /[\}|%]/, token: "def"},

            ],
            amtag: [
                {regex: /.*?>/, token: "amtag", next: "start"},
                {regex: /.*/, token: "amtag"}
            ],
            amcdata: [
                {regex: /.*?]]>/, token: "amcdata", next: "start"},
                {regex: /.*/, token: "amcdata"}
            ]
        });


        var xmlEditor = {
            editor: null,
            header: null,
            footer: null,
            selectedRow: {},
            updateMode: false,
            navigator: {},
            modifyTemplate: null,
            modifyConfig: null,
            modifyArgs: null,
            modifyCount: 0,
            buttons: {
                insert: null,
                update: null
            },
            updateBtn: null,
            clearSelectedRow: function () {
                this.updateMode = false;
                this.selectedRow = {
                    tag: null,
                    attribute: null,
                    format: null,
                    optional: null,
                    parent: null
                };

                var modifyContainer = $('feed_xml_content_modify_container');
                if (modifyContainer) {
                    modifyContainer.innerHTML = '';
                }
            },
            refresh: function () {
                this.editor.refresh();
                this.editor.save();

                this.header.refresh();
                this.header.save();

                this.footer.refresh();
                this.footer.save();
            },
            init: function (modifyTemplate, modifyConfig, modifyArgs) {

                this.modifyTemplate = modifyTemplate;
                this.modifyConfig = modifyConfig;
                this.modifyArgs = modifyArgs;

                this.editor = CodeMirror.fromTextArea($('feed_xml_content'), {
                    mode: 'amasty_feed',
                    alignCDATA: true,
                    lineNumbers: false,
                    viewportMargin: Infinity
                });

                this.header = CodeMirror.fromTextArea($('feed_xml_header'), {
                    mode: 'amasty_feed',
                    alignCDATA: true,
                    lineNumbers: false,
                    viewportMargin: Infinity
                });

                this.footer = CodeMirror.fromTextArea($('feed_xml_footer'), {
                    mode: 'amasty_feed',
                    alignCDATA: true,
                    lineNumbers: false,
                    viewportMargin: Infinity
                });

                this.editor.setSize(null, 400);
                this.header.setSize(null, 100);
                this.footer.setSize(null, 100);

                this.editor.on("cursorActivity", this.cursorActivity.bind(this));

                this.clearSelectedRow();
                this.initNavigator();
                this.initButtons();
                this.updateNavigator();

                setInterval(this.refresh.bind(this), 100);
            },
            initNavigator: function () {
                var container = $('xml_table');
                this.navigator = {
                    tag: container.down("#feed_xml_content_tag"),
                    attribute: container.down("#feed_xml_content_attribute"),
                    format: container.down("#feed_xml_content_format"),
                    optional: container.down("#feed_xml_content_optional"),
                    parent: container.down("#feed_xml_content_parent")
                }
            },
            initButtons: function () {
                var container = $('xml_table');

                this.buttons.insert = container.down("#insert_button");
                this.buttons.update = container.down("#update_button");

                this.buttons.insert.observe('click', this.inserRow.bind(this));
                this.buttons.update.observe('click', this.updateRow.bind(this));
            },
            getXMLRowFormat: function () {
                var ret = "";
                switch (this.navigator.insert_type.value) {
                    case "images":
                        ret = this.navigator.insert_image_format.value;
                        break;
                    default:
                        ret = this.navigator.insert_format.value;
                        break;
                }

                return ret;
            },
            getRow: function () {
                var tpl = '{attribute=":attribute" format=":format" parent=":parent" optional=":optional" modify=":modify"}';

                var modifyArr = [];
                for (var idx = 0; idx < this.modifyCount; idx++) {
                    var modify = $('field_row_' + idx + '_modify');

                    if (modify) {
                        var modifyValue = modify.value;
                        var args = [];
                        if (this.modifyArgs[modifyValue]) {
                            args = this.modifyArgs[modifyValue];
                        }

                        var modifyString = modify.value;

                        if (args.length > 0) {
                            modifyString += ':';
                            var values = [];
                            for (var argIdx = 0; argIdx < args.length; argIdx++) {
                                var arg = $('field_row_' + idx + '_arg' + argIdx);
                                if (arg) {
                                    values.push(arg.value);
                                }
                            }
                            modifyString += values.join("^");
                        }
                        modifyArr.push(modifyString);
                    }
                }

                var repl = {
                    ':tag': this.navigator.tag.value,
                    ':attribute': this.navigator.attribute.value,
                    ':format': this.navigator.format.value,
                    ':optional': this.navigator.optional.value,
                    ':parent': this.navigator.parent.value,
                    ':modify': modifyArr.join("|")
                };

                $H(repl).each(function (item) {
                    tpl = tpl.replace(eval('/' + item.key + '/g'), item.value);
                });

                if (this.navigator.tag.value) {
                    tpl = "<" + this.navigator.tag.value + ">" + tpl + "</" + this.navigator.tag.value + ">";
                }

                return tpl;
            },
            updateRow: function () {

                var originLine = this.editor.getLine(this.editor.getCursor().line);

                var line = this.getRow();

                this.editor.replaceRange(line, {
                    line: this.editor.getCursor().line,
                    ch: 0
                }, {
                    line: this.editor.getCursor().line,
                    ch: originLine.length
                });
            },
            inserRow: function () {
                this.editor.replaceSelection(this.getRow() + '\n');
            },
            cursorActivity: function () {
                this.clearSelectedRow();

                var line = this.editor.getLine(this.editor.getCursor().line);

                var tagMatch = line.match(/<([^>]+)>(.*?)<\/\1>/);

                if (tagMatch && tagMatch.length == 3) {

                    this.selectedRow.tag = tagMatch[1];

                    this.updateMode = true;
                }

                var varsRe = /(attribute|format|optional|parent)="(.*?)"/g;
                var varsArr;
                while ((varsArr = varsRe.exec(line)) != null) {
                    if (varsArr && varsArr.length == 3) {
                        if (this.selectedRow[varsArr[1]] !== undefined) {
                            this.selectedRow[varsArr[1]] = varsArr[2];
                        }
                        this.updateMode = true;
                    }
                }

                this.restoreModify(line);
                this.updateNavigator();
            },
            restoreModify: function (line) {

                var varsRe = /(modify)="(.*?)"/g;
                var varsArr = varsRe.exec(line);

                if (varsArr && varsArr.length == 3) {
                    var modificators = varsArr[2].split("|");
                    for (var idx in modificators) {
                        var modificator = modificators[idx];
                        if (typeof(modificator) != 'function') {
                            var modificatorArr = modificator.split(/:(.+)?/, 2);

                            var modify = modificatorArr[0];

                            if ($(this.modifyConfig).indexOf(modify) != -1) {
                                var rowIndex = this.modifyItem();
                                var select = $('field_row_' + rowIndex + '_modify');
                                if (select) {
                                    select.value = modify;
                                    this.changeModifier(select);
                                }

                                var args = [];

                                if (this.modifyArgs[select.getValue()]) {
                                    args = this.modifyArgs[select.getValue()];
                                }

                                if (args.length > 0 && modificatorArr[1]) {
                                    var values = modificatorArr[1].split("^");

                                    for (var idx = 0; idx < args.length; idx++) {
                                        var id = select.id.replace("_modify", "_arg" + idx);
                                        var input = $(id);
                                        if (input && values[idx]) {
                                            input.value = values[idx];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            },
            updateNavigator: function () {
                this.setValue(this.navigator.tag, this.selectedRow.tag);
                this.setValue(this.navigator.attribute, this.selectedRow.attribute);
                this.setValue(this.navigator.format, this.selectedRow.format);
                this.setValue(this.navigator.optional, this.selectedRow.optional);
                this.setValue(this.navigator.parent, this.selectedRow.parent);

                if (this.updateMode) {
                    this.buttons.update.removeClassName('hidden');
                    this.buttons.insert.addClassName('hidden');
                } else {
                    this.buttons.update.addClassName('hidden');
                    this.buttons.insert.removeClassName('hidden');
                }
            },
            setValue: function (input, value) {
                if (value !== null) {
                    input.setValue(value)
                }
            },
            modifyItem: function (a) {
                var container = $('feed_xml_content_modify_container');
                var data = {
                    index: this.modifyCount++
                };
                if (container) {
                    Element.insert(container, {
                        bottom: this.modifyTemplate({
                            data: data
                        })
                    });
                }
                return data.index;
            },
            changeModifier: function (select) {
                var td = select.up('td');

                var args = [];

                if (this.modifyArgs[select.getValue()]) {
                    args = this.modifyArgs[select.getValue()];
                }

                td.select('input').each(function (input) {
                    input.hide();
                });

                for (var idx = 0; idx < args.length; idx++) {
                    var id = select.id.replace("_modify", "_arg" + idx);
                    var input = td.down("#" + id);
                    if (input) {
                        input.show();
                        input.setAttribute('placeholder', args[idx]);
                    }
                }
            },
            deleteItem: function (event) {
                var tr = Event.findElement(event, 'tr');
                if (tr) {
                    Element.select(tr, ['input', 'select']).each(function (element) {
                        element.remove();
                    });
                    Element.remove(tr);
                }
                return false;
            }
        };

        return xmlEditor;
    }
);
