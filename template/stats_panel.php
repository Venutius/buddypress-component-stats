<html>
<head>
</head>
<body>
	<div id="contenedor-plugin">
    	<h2><?php echo sanitize_text_field( __( 'BuddyPress Component Stats', 'buddypress-component-stats' ) ); ?></h2>
        
        <h4><?php echo sanitize_text_field( __( 'In this page you can obtain statistics about the users who interact in the social network and classifies the statistics of the main components of buddypress (Forums, Groups, Blogs, Comments, Activity, Friendship) showing results on the most active in each of these components.', 'buddypress-component-stats' ) ); ?></h4>
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1"><?php echo sanitize_text_field( __( 'Stats Form Panel', 'buddypress-component-stats' ) ); ?></a></li>
            </ul>
            <div id="tabs-1">                				
                <fieldset>
                    <legend>Stats</legend>                                
                        <form>
                            <label id="titulo_inicial"><h3><?php echo sanitize_text_field( __( 'Select the component and a date range among which to search stats information', 'buddypress-component-stats' ) ); ?></h3></label><br>
                            <label><?php echo sanitize_text_field( __( 'Component: ', 'buddypress-component-stats' ) ); ?></label>                            
                            <select id="component" name="component">
                                <option value="activity"><?php echo sanitize_text_field( __( 'Activity', 'buddypress-component-stats' ) ); ?></option>
                                <option value="groups"><?php echo sanitize_text_field( __( 'Groups', 'buddypress-component-stats' ) ); ?></option>
                                <option value="forums"><?php echo sanitize_text_field( __( 'Forums', 'buddypress-component-stats' ) ); ?></option>
                                <option value="blogs"><?php echo sanitize_text_field( __( 'Blogs', 'buddypress-component-stats' ) ); ?></option>
                                <option value="comments"><?php echo sanitize_text_field( __( 'Comments', 'buddypress-component-stats' ) ); ?></option>
                                <option value="friendship"><?php echo sanitize_text_field( __( 'Friendship', 'buddypress-component-stats' ) ); ?></option>                               
                            </select>                            
                            <label id="start"><strong><?php echo sanitize_text_field( __( 'Start Date:', 'buddypress-component-stats' ) ); ?></strong> </label><input type="text" id="datepicker_start" name="datepicker_start">
                            <label id="final"><strong><?php echo sanitize_text_field( __( 'Final Date:', 'buddypress-component-stats' ) ); ?></strong></label><input type="text" id="datepicker_final" name="datepicker_final">                               
                            <input type="submit" id="consultar" name="consultar" value="Go" onClick="return ValidateForm();" />                               
                            <div id="error">
                                <div id="error-msg">                            
                                </div>
                            </div>
                        </form>
                </fieldset>
                <div id="preload"><?php echo sanitize_text_field( __( 'Searching for Results ...', 'buddypress-component-stats' ) ); ?></div>                
                <div id="results"></div>
                <div id="green" style="margin: auto;"></div>
            </div>                                    
		</div>
        <div id="detailed_preload"><?php echo sanitize_text_field( __( 'Searching for detailed results ...', 'buddypress-component-stats' ) ); ?></div>
        <div id="detailed_results"></div>                        
    </div>                
</body>
</html>