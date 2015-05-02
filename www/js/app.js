$(document).ready(function(){
	$.nette.init();

	init();

	var elem = document.querySelector('input.switcher');
	if (elem !== null) {
		var switchery = new Switchery(elem, {color: '#008CBA'});
	}

	$(".chat-text").niceScroll();
	$("#chat").niceScroll();

	autosize($('.autosize'));

	$(document).ajaxComplete(init);

})



function init(){
	$(document).foundation();

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

	$(".rateit").rateit();


	$('.tooltip-limit').tooltipster({
		content: $('<span><strong>žádný</strong> - není žádný limit pro odevzdání<br/><strong>volný</strong> - studenti mohou odevzdávat i po limitu, v závěrečném zhodnocení budou tito studenti označeni<br /><strong>striktní</strong> - studenti nebudou moci odevzdat po uplynutí limitu</span>')
	});


}


$(document).on('click', '.myAjax', function(e){

    var request = $.nette.ajax({}, this, e).success(function(){
		var element = $(e.target);
		if (element.data('foundation-refresh')) {
			$(document).foundation();
		}
	});

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

function drawChart(id, data, options) {

	var options = {
		title: 'Úpěšnost odpovědí v %'
	};

	var chart = new google.visualization.PieChart(document.getElementById(id));

	chart.draw(google.visualization.arrayToDataTable(data), options);
}
