$(document).ready(function(){
    $(document).foundation();

    $('.fdatepicker').fdatepicker({
        language: 'cz',
        format: 'd. m. yyyy'
    }).on('keydown', function(){
        return false;
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