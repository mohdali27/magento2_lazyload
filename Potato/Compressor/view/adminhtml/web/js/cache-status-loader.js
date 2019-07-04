define([
    'jquery'
], function($) {
    return {
        container: '#potato_compressor_cache_status_container',
        run: function(url){
            var me = this;
            $.ajax({
                url: url
            }).done(function(data){
                $(me.container).after(data.html);
                $(me.container).remove();
            });
        }
    };
});