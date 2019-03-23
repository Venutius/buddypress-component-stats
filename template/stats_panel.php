<html>
<head>
<script type="text/javascript">						
	function detailed_publications(id,startDate,finalDate,comp) {				
		jQuery("#detailed_preload").show();
		jQuery("#detailed_results").hide();
		var data = {
			action: 'component_detailed_stats',
			user_id:id,
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
			start_date: jQuery("#datepicker_start").val(),
			final_date: jQuery("#datepicker_final").val()
		};
				
		jQuery.post(ajaxurl, data, function(response) {															
			jQuery("#preload").hide();
			jQuery("#results").html(response);																																				
			jQuery("#green").show();																													
		});	
		
		return false;									
	}										      								
</script>
</head>
<body>
	<div id="contenedor-plugin">
    	<h2>BuddyPress Component Stats</h2>
        
        <h4>In this page you can obtain statistics about the users who interact in the social network and classifies the statistics of the main components of buddypress (Forums, Groups, Blogs, Comments, Activity,    	            Friendship) showing results on the most active in each of these components.</h4>
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Stats Form Panel</a></li>                
            </ul>
            <div id="tabs-1">                				
                <fieldset>
                    <legend>Stats</legend>                                
                        <form>
                            <label id="titulo_inicial"><h3>Select the component and a date range among which to search stats information</h3></label><br>
                            <label>Component: </label>                            
                            <select id="component" name="component">
                                <option value="activity">Activity</option>
                                <option value="groups">Groups</option>
                                <option value="forums">Forums</option>
                                <option value="blogs">Blogs</option>
                                <option value="comments">Comments</option>
                                <option value="friendship">Friendship</option>                               
                            </select>                            
                            <label id="start"><strong>Start Date:</strong> </label><input type="text" id="datepicker_start" name="datepicker_start">
                            <label id="final"><strong>Final Date:</strong></label><input type="text" id="datepicker_final" name="datepicker_final">                               
                            <input type="submit" id="consultar" name="consultar" value="Go" onClick="return ValidateForm();" />                               
                            <div id="error">
                                <div id="error-msg">                            
                                </div>
                            </div>
                        </form>
                </fieldset>
                <div id="preload">Searching Results ...</div>                
                <div id="results"></div>
                <div id="green" style="margin: auto;"></div>
            </div>                                    
		</div>
        <div id="detailed_preload">Searching detailed results ...</div>
        <div id="detailed_results"></div>                        
    </div>                
</body>
</html>