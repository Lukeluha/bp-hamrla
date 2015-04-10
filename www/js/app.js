$(document).ready(function(){
    $(document).foundation();
    $.nette.init();

    $('.fdatepicker').fdatepicker({
        language: 'cz',
        format: 'd. m. yyyy'
    }).on('keydown', function(){
        return false;
    })

})

$(document).on('click', '.myAjax', function(e){
    if ($(this).hasClass('button')) {
        $(this).addClass('disabled');
        $(this).text("Počkejte...");
    }

    var url = $(this).attr('href')

    $.nette.ajax({
        'url': url
    })



    e.preventDefault();

    return false;
});

$(document).on('keyup', '.liveSearch', function(e){
    var that = $(this);

    delay(function() {
        liveSearch(that);
    }, 700);

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

function printData(divId)
{
    var divToPrint=document.getElementById(divId);
    newWin= window.open("");
    newWin.document.write(divToPrint.outerHTML);
    newWin.print();
    newWin.close();
}
