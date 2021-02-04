var timeFormat = 'MM/DD/YY';
function newDate(days) {
	return moment().add(days, 'd').toDate();
}

function newDateString(days) {
	return moment().add(days, 'd').format(timeFormat);
}

function newTimestamp(days) {
	return moment().add(days, 'd').unix();
}

window.chartColors = {
	red: 'rgb(232, 62, 140)',
	orange: 'rgb(248, 203, 0)',
	yellow: 'rgb(255, 193, 7)',
	green: 'rgb(23, 162, 184)',
	blue: 'rgb(99, 194, 222)',
	purple: 'rgb(153, 102, 255)',
	grey: 'rgb(201, 203, 207)',
	refund: 'rgb(232, 62, 140)',
	withdrawal: 'rgb(77, 189, 116)',
	tax: 'rgb(115, 129, 143)',
	shipping: 'rgb(111, 66, 193)'
};

var color = Chart.helpers.color;