$filter_date_form = '';
$filter_date_to = '';
jQuery( document ).ready( function( $ ){
	if( $( 'input[name="wcfm-date-range"]' ).length > 0 ) {
		/* Date Range Picker
		------------------------------------------ */
		$.dateRangePickerLanguages = { 'wcfm_drp_lang': wcfm_drp_lang };
		$( 'input[name="wcfm-date-range"]' ).dateRangePicker({
			language:'wcfm_drp_lang',
			setValue: function(s) {
				this.value = s;
			},
			startOfWeek: wcfm_drp_options.startOfWeek,
			showShortcuts: true,
			showTopbar: true,
			singleMonth: false,
			shortcuts : {
				'prev-days': [3,7,14],
				'prev': ['week','month'],
				'next-days': null,
				'next': null,
			},
			customShortcuts: [
				{
					name: wcfm_drp_lang.this_week,
					dates : function()
					{
						var start = moment().day(0).toDate();
						var end = moment().day(6).toDate();
						return [start,end];
					}
				},
				{
					name: wcfm_drp_lang.this_month,
					dates : function()
					{
						var start = moment().startOf('month').toDate();
						var end = moment().endOf('month').toDate();
						return [start,end];
					}
				}
			]
		});
		$( 'body' ).on( 'datepicker-change', 'input[name="wcfm-date-range"]', function( e, obj ) {
	
			/* Get date from + to */
			var date1 = new Date( obj.date1 );
			var date2 = new Date( obj.date2 );
	
			/* Format it in YYYY-MM-DD for consistency */
			var date_from = date1.getFullYear() + '-' + ("0" + (date1.getMonth() + 1)).slice(-2) + '-' + ("0" + date1.getDate()).slice(-2);
			var date_to = date2.getFullYear() + '-' + ("0" + (date2.getMonth() + 1)).slice(-2) + '-' +("0" + date2.getDate()).slice(-2);
	
			/* Add it in hidden input */
			$( 'input[name="wcfm-date_from"]' ).val( date_from );
			$( 'input[name="wcfm-date_to"]' ).val( date_to );
			
			$filter_date_form = date_from;
			$filter_date_to = date_to;
	
			/* Update chart */
			$( document.body ).trigger( 'wcfm-date-range-refreshed' );
		});
	}
});