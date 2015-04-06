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
        }, 1000);
    })
})

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

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

function liveSearch(element)
{
    var url = $(element).data('url');
    var query = $(element).val();
    $.nette.ajax({
        'url': url,
        'data': {
            query: query
        }
    });
}

