
	$( document ).ready(function() {
		dataAttr = {
			"data-slider-min": "0",
			"data-slider-max": "10",
			"data-slider-step": "1",
			};
		
		$("input[id^='Numeric']").attr(	
			dataAttr 
		
		);
		$("input[id^='Numeric']").each(function ( index){
			$(this).attr(	
				"data-slider-value", $(this).val()
			)
		});
		$("input[id^='Numeric']").slider({
		    tooltip: 'auto'
		});
	})

