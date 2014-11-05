var js = {
    popup: function(url, callback, custom, loading) {
        $('#' + callback).modal('hide');
        if (typeof(loading) == 'undefined')
            var loading = 'loading';
        if (typeof(custom) == 'undefined')
            var custom = '';
        var html = '<div style="top:5%;" class="modal fade  ' + custom + '" id="' + callback + '"><div id="css_loading">&nbsp;</div></div>';
        $('#' + loading).html(html);
        $.get(url, function(data) {
            $('#' + callback).html(data);
        })
    },
    submitForm: function(obj, url, id) {
        var html = '<div id="css_loading">&nbsp;</div>';
        $('#' + id).html(html);
        data = obj.serialize();
        $.post(url, data, function(cb) {
            $('#' + id).html(cb);
            $("#" + id).fadeIn('slow');
        });
        
    }
    
  
}
