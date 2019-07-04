define([
    'jquery'
], function($){
    return {
        init: function(key, url, tags, ignore) {
            this.baseUrl = require.s.contexts._.config.baseUrl;

            this.key = key;
            this.url = url;
            this.tags = tags;
            this.ignore = ignore;

            this.pushed = [];
            var me = this;
            var jsBuild = Object.keys(require.s.contexts._.config.config.jsbuild||[]);
            var textBuild = Object.keys(require.s.contexts._.config.config.text||[]);
            $.each($.merge(jsBuild, textBuild), function(i, module){
                me.pushed.push(me.baseUrl + module);
            });
        },

        run: function() {
            var me = this;
            setInterval(function(){
                if (me._scripts().length > 0) {
                    me.doRequest();
                    me.pushed = $.merge(me.pushed, me._scripts());
                }
            }, 5000);
        },

        doRequest: function() {
            $.ajax(this.url, {
                method: 'post',
                data: {
                    key: this.key,
                    tags: this.tags,
                    base: this.baseUrl,
                    list: this._scripts()
                }
            });
        },

        _scripts: function() {
            var me = this;
            var jsList = Object.keys(require.s.contexts._.urlFetched);
            var textList = [];
            $.each(Object.keys(require.s.contexts._.defined), function(i, module){
                if (module.indexOf('text!') !== 0) {
                    return;
                }
                if (!module.match(/\.html$/)) {
                    return;
                }
                var name = module.replace(/^text!/, '');
                textList.push(require.toUrl(name));
            });
            var onPage = $.merge(jsList, textList);
            onPage = $.grep(
                onPage, function(v){
                    if (v.indexOf(me.baseUrl.replace(/(https*:\/\/[^\/]+\/).*/, "$1")) !== 0) {
                        return false;
                    }
                    return $.grep(me.ignore, function(ignoreString){
                        return v.indexOf(window.atob(ignoreString)) !== -1;
                    }).length === 0;
                }
            );
            return $(onPage).not(this.pushed).get();
        }
    };
});