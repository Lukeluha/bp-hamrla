$(document).ready(function(){
    $(document).foundation();
    $.nette.init();

    $('.fdatepicker').fdatepicker({
        language: 'cz',
        format: 'd. m. yyyy'
    }).on('keydown', function(){
        return false;
    })

    $(".liveSearch").keyup(function(event){
        var that = $(this);
        delay(function() {
            liveSearch(that);
        }, 700);
    })
})


var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

function loadView(elementId, url)
{
    var element = $("#" + elementId);
    if (url) {
        element.load(url);
    } else {
        url = element.data('url');
        element.load(url)
    }
}

function liveSearch(element)
{
    var url = $(element).data('url');
    var query = $(element).val();
    var spinner = $(element).data('spinner');

    if (spinner) {
        $("#" + spinner).show();
    }

    $.nette.ajax({
        'url': url,
        'data': {
            query: query
        }
    }).success(function(){
        if (spinner) {
           $("#" + spinner).hide();
        }
    });
}

