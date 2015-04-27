$(document).ready(function(){
    $(document).foundation();
    $.nette.init();


    $('.fdatepicker').fdatepicker({
        language: 'cz',
        format: 'd. m. yyyy'
    }).on('keydown', function(){
        return false;
    })

	$('.fdatetimepicker').fdatetimepicker({
		language: 'cs',
		format: 'd. m. yyyy h:ii',
		closeButton:false
	}).on('keydown', function(){
		return false;
	})

    $(".chat-text").niceScroll();
    $("#chat").niceScroll();

    autosize($('.autosize'));

    var elem = document.querySelector('input.switcher');
    if (elem !== null) {
        var init = new Switchery(elem, {color: '#008CBA'});
    }

})



$(document).on('click', '.myAjax', function(e){

    var request = $.nette.ajax({}, this, e);

	if (request.readyState) {
		if ($(this).data('reveal-id')) {
			$('#' + $(this).data('reveal-id')).foundation('reveal','open');
		}

		if ($(this).hasClass('button')) {
			$(this).addClass('disabled');
			var isSubmit = $(this).is('input[type=submit]') || $(this).is('button[type=submit]');

			if (isSubmit) {
				$(this).val("Počkejte...");
			} else {
				$(this).text("Počkejte...");
			}
		}
	}

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


//function loadView(elementId, url)
//{
//    var element = $("#" + elementId);
//    if (url) {
//        element.load(url);
//    } else {
//        url = element.data('url');
//        element.load(url)
//    }
//}

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

function stillOnline(url) {
    $.get(url);
}

function showOrHide(id) {
    var element = $("#" + id);
    if (element.is(":visible")) {
        element.hide();
    } else {
        element.show();
    }
}

(function($){
	$.fn.fdatetimepicker.dates['cs'] = {
		days: ["Neděle", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"],
		daysShort: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So", "Ne"],
		daysMin: ["N", "P", "Ú", "St", "Č", "P", "So", "N"],
		months: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
		monthsShort: ["Led", "Úno", "Bře", "Dub", "Kvě", "Čer", "Čnc", "Srp", "Zář", "Říj", "Lis", "Pro"],
		today: "Dnes"
	};
}(jQuery));


