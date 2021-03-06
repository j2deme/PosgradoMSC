$(function(){
	$('.datepicker').datepicker({
		'language': 'es',
		'autoclose':true
//		'startDate': 'd'
	});
	$("a[rel=popover]")
    .popover()
    .click(function(e) {
        e.preventDefault();
	});
	$('.carousel').carousel();
	$('.timepicker').timepicker({
        minuteStep: 5,
        disableFocus: true
    });
    $(".chzn-select").chosen();
//    $(".chzn-select").select2();
    $('[rel=tooltip]').tooltip();

	var fullDate = new Date();
	//convert month to 2 digits
	var twoDigitMonth = ((fullDate.getMonth().length+1) === 1)? (fullDate.getMonth()+1) : '0' + (fullDate.getMonth()+1);
	var currentDate = fullDate.getFullYear() + "-" + twoDigitMonth + "-" + fullDate.getDate();

	$('#start-date')
	.datepicker()
    .on('changeDate', function(ev){
        var startDate = process($('#start-date').val());
		var endDate = process($('#end-date').val());
        if (ev.date.valueOf() > endDate.valueOf()){
            $('#alert').show().find('strong').text('La fecha de inicio debe ser anterior a la fecha de fin.');
            $('#save').attr("disabled", "disabled");
            $(this).blur();
        } else {
            $('#alert').hide();
            $('#save').removeAttr("disabled");
            /*startDate = new Date(ev.date);
            $('#date-start-display').text($('#date-start').data('date'));*/
        }
        $('#start-date').datepicker('hide');
    });
	$('#end-date')
    .datepicker()
    .on('changeDate', function(ev){
        var startDate = process($('#start-date').val());
		var endDate = process($('#end-date').val());
        if (ev.date.valueOf() < startDate.valueOf()){
            $('#alert').show().find('strong').text('La fecha de fin debe ser posterior a la fecha de inicio.');
            $('#save').attr("disabled", "disabled");
            $(this).blur();
        } else {
            $('#alert').hide();
            $('#save').removeAttr("disabled");
            /*endDate = new Date(ev.date);
            $('#date-end-display').text($('#date-end').data('date'));*/
        }
        $('#end-date').datepicker('hide');
    });
});

function process(date){
   var parts = date.split("/");
   return new Date(parts[2], parts[1] - 1, parts[0]);
}

$.fn.hasAttr = function(name) {
   var attr = $(this).attr('name');
    if (typeof attr !== 'undefined' && attr !== false) {
        return false;
    } else {
        return true;
    }
};
