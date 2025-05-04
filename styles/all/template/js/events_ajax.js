/**
	* Events calendar $extends the phpBB Software package.
	* @copyright (c) 2024, Steve, https://steven-clark.tech/
	* @license GNU General Public License, version 2 (GPL-2.0)
*/

(($) => { 'use strict';

$.extend(phpbb, {
	prevToggleTabs: (iD) => {
		if ($(iD + " .calendar-month:visible").prev().length != 0) {
			$(iD + " .calendar-month:visible").prev().show("slow").next().hide("slow");
		}
		else {
			$(iD + " .calendar-month:visible").hide("slow");
			$(iD + " .calendar-month:last").show("slow");
		}
		return this;
	},
	nextToggleTabs: (iD) => {
		if ($(iD + " .calendar-month:visible").next().length != 0) {
			$(iD + " .calendar-month:visible").next().show("slow").prev().hide("slow");
		}
		else {
			$(iD + " .calendar-month:visible").hide("slow");
			$(iD + " .calendar-month:first").show("slow");
		}
		return this;
	},
	toggle_column: (id, columnId, cClass) => {
		$('#calendar-column-' + id).slideToggle("slow");
		$('#calendar-toggle-column-' + columnId + ' i.icon').toggleClass('fa-toggle-off icon-red fa-toggle-off fa-rotate-180 icon-green');

		var column = $('#calendar-column-' + columnId);
		if ($(column).hasClass('column' + cClass)) {
			$(column).removeClass('column' + cClass);
		} else {
			$(column).addClass('column' + cClass);
		}
	}

});

phpbb.addAjaxCallback('phpbb_collapse_calendar', function(res) {
	if (res.success) {
		$('#collapsible-calendar').slideToggle('fast');
		$(this)
			.find('i')
			.toggleClass('fa-plus-square fa-minus-square')
			.end()
			.closest('.forabg').find('#collapse_calendar')
			.stop(true, true)
			.slideToggle('fast')
		;
	}
});

phpbb.addAjaxCallback('attend', (res) => {
	var tools = $('a[id^=attend_], a[id^=unattend_]');
	if (tools.is(':hidden')) { tools.show() }
	$(this).hide();
	phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
});

phpbb.addAjaxCallback('attended', (res) => {
	$(this).hide();
	phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
});

phpbb.addAjaxCallback('delete_event', (res) => {
	$('#event-' + res.EVENT_ID).fadeOut();
	phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
});

$("a#calendar-toggle-column-one").click((e) => {
    e.preventDefault();
	phpbb.toggle_column('two', 'one', '1');
});

$("a#calendar-toggle-column-two").click((e) => {
    e.preventDefault();
	phpbb.toggle_column('one' ,'two', '2');
});

$("#next-month").click((e) => {
	e.preventDefault();
	if ($("#months .calendar-month:visible").next().length != 0) {
		$("#months .calendar-month:visible").next().show("slow").prev().hide("slow");
	}
	else {
		$("#months .calendar-month:visible").hide("slow");
		$("#months .calendar-month:first").show("slow");
	}
});

$("#prev-month").click((e) => {
	e.preventDefault();
	if ($("#months .calendar-month:visible").prev().length != 0) {
		$("#months .calendar-month:visible").prev().show("slow").next().hide("slow");
	}
	else {
		$("#months .calendar-month:visible").hide("slow");
		$("#months .calendar-month:last").show("slow");
	}
});

var indexTabs = '#calendar-index-tabs';
$("#next-tab").click((e) => {
	e.preventDefault();
	phpbb.nextToggleTabs(indexTabs);
});

$("#prev-tab").click((e) => {
	e.preventDefault();
	phpbb.prevToggleTabs(indexTabs);
});

$("#error-popup").click((e) => {
	e.preventDefault();
	$('#phpbb_alert1').hide();
});

$(() => {
    $("div[id^=months-]").each((event) => {
        if (event != $('#months-' + calendarMonth) && event != 0) { $(this).hide() }
    });  	
	$('#months-' + calendarMonth).show();
});

})(jQuery);
