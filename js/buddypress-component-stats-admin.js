
function detailed_publications(id,startDate,finalDate,comp) {				
	jQuery("#detailed_preload").show();
	jQuery("#detailed_results").hide();
	var data = {
		action: 'component_detailed_stats',
		user_id:id,
		security : ajax_object.check_nonce,
		start_date: startDate,
		final_date: finalDate,
		component:comp			
	};
	
	jQuery.post(ajaxurl, data, function(response) {																								
		jQuery("#detailed_preload").hide();
		jQuery("#detailed_results").show();
		jQuery("#detailed_results").html(response);
	});
}

jQuery(document).ready(function(){		
	jQuery("#preload").hide();	
	jQuery("#detailed_preload").hide();
	jQuery("#detailed_results").hide();		
	jQuery("#tabs").tabs();								
	jQuery("#datepicker_start").datepicker({maxDate: "+0D", dateFormat: "yy-mm-dd"});
	jQuery("#datepicker_final").datepicker({maxDate: "+0D", dateFormat: "yy-mm-dd"});												
	jQuery('select[name="component"]').bind('change', function(){
		if(jQuery(this).val() == 'friendship' ){
			jQuery('#datepicker_start').hide();	
			jQuery('#datepicker_final').hide();
			jQuery('#start').hide();	
			jQuery('#final').hide();										
		}
		else {
			jQuery('#datepicker_start').show();
			jQuery('#datepicker_final').show();	
			jQuery('#start').show();	
			jQuery('#final').show();				
		}
	});										
});
													
/* validate form to get appropiate dates for the query */
function ValidateForm() {		 
	 start = jQuery("#datepicker_start").datepicker("getDate");
	 final = jQuery("#datepicker_final").datepicker("getDate");		 				 
			 
	 var comp = jQuery('select[name="component"]').val();		 		 
	 if (comp != 'friendship'){
		 if(jQuery("#datepicker_start").val().length == 0 || jQuery("#datepicker_start").val() == '' ) {  						
			jQuery("#error").slideUp('slow', function(){
				jQuery("#error-msg").html('Select a date for the field <strong>Start Date</strong>');
				jQuery("#error-msg").fadeIn("slow");
				jQuery("#error").slideDown('slow');	
			});									
			return false;	    
		} else if (jQuery("#datepicker_final").val().length == 0 || jQuery("#datepicker_final").val() == '' ) {  						
			jQuery("#error").slideUp('slow', function(){
				jQuery("#error-msg").html('Select a date for the field <strong>Final Date</strong>');
				jQuery("#error-msg").fadeIn("slow");			
				jQuery("#error").slideDown('slow');	
			});			
			return false;	    
		} else if (final < start){
			jQuery("#error").slideUp('slow', function(){
				jQuery("#error-msg").html('The Final Date cannot be a date less that the Star Date');
				jQuery("#error-msg").fadeIn("slow");			
				jQuery("#error").slideDown('slow');	
			});			
			return false;
		}
	} else {
		
	}
	
	jQuery("#green").hide();		
	jQuery("#detailed_results").hide();
	jQuery("#results").html('');
	jQuery("#detailed_results").html('');
	jQuery("#preload").show();
	jQuery("#error").slideUp('slow');
	
	var data = {
		action: 'results_query',
		component: jQuery("#component").val(),
		security : ajax_object.check_nonce,
		start_date: jQuery("#datepicker_start").val(),
		final_date: jQuery("#datepicker_final").val()
	};
			
	jQuery.post(ajaxurl, data, function(response) {															
		jQuery("#preload").hide();
		jQuery("#results").html(response);
		paginateResults();
		jQuery("#green").show();																													
	});	
	
	return false;									
}
/* Fucntion to paginate results using javascript */
function paginateResults() {

	paginationInfo = document.getElementById( 'bpcs-pagination-data' );
	dataA = paginationInfo.dataset.a;
	dataB = paginationInfo.dataset.b;
	dataRecords = paginationInfo.dataset.records;

	jQuery("#myTable").tablesorter({         
		sortList: [[dataA, dataB]] 
	});			
	jQuery('#green').smartpaginator({ 				
		totalrecords: dataRecords,
		recordsperpage: 25, 
		datacontainer: 'myTable', 
		initval:0,
		length: 10,
		dataelement: 'tr',
		next: 'Next', 
		prev: 'Prev', 
		first: 'First', 
		last: 'Last',
		display: 'single',
		theme: 'green'
	});
	
	jQuery('#btnexport').click(function(){
		jQuery('#formats').toggle();
	});
}