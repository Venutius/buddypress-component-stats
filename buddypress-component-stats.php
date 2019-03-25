<?php
/*
  Plugin Name: Buddypress Component Stats
  Description: This plugin allows to obtain statistics about the users who interact in the social network and classifies the statistics of the main components of buddypress (Forums, Groups, Blogs, Comments, Activity,   		  Friends) showing results on the most active in each of these components.
  Version: 2.0.0
  Author: manichooo
*/
?>
<?php         		                       	                     						
	/* Actions que vinculan las funciones de instalacion y desintalacion desde el panel de administracion de plugins de wordpress */						
	add_action('activate_buddypress-component-stats/buddypress-component-stats.php','stats_install');
	add_action('deactivate_buddypress-component-stats/buddypress-component-stats.php', 'stats_desinstall');		
	
	/* call to function that include the js and css files for the plugin*/
	add_action('admin_init', 'my_admin_init');
	
	/* Add a menu item to Wordpress menu panel */
	add_action('admin_menu', 'buddypress_component_stats_add_menu');						
	
	/* Call to function that obtain the stats information from the front end view plugin */
	add_action('wp_ajax_results_query', 'get_component_stats');
	
	/* Call to enqueue admin scripts */
	add_action( 'admin_enqueue_scripts', 'bpcs_admin_enqueue_scripts' );
		
	/* Function that compiles stats about the users interaction by components (Activity Stream, Groups, Forums, Comments, Blogs, Friendship) on the social network  */				
	function get_component_stats() {
		global $wpdb;
		$html = '';
		$total = 0;
		$start_date = sanitize_text_field( $_POST['start_date'] );
		$final_date = sanitize_text_field( $_POST['final_date'] );
		$component = sanitize_text_field( $_POST['component'] );
		$user_avatar = sanitize_text_field( __( 'User Avatar', 'buddypress-component-stats' ) );
		$user = sanitize_text_field( __( 'User', 'buddypress-component-stats' ) );
		$number_of_publications = sanitize_text_field( __( 'Number of Publications', 'buddypress-component-stats' ) );
		$email = sanitize_text_field( __( 'e-mail', 'buddypress-component-stats' ) );
		$registered_from = sanitize_text_field( __( 'Registered from', 'buddypress-component-stats' ) );
		$last_update = sanitize_text_field( __( 'Last Update', 'buddypress-component-stats' ) );
		$no_records = sanitize_text_field( __( 'Number of Records Found', 'buddypress-component-stats' ) );
		$username = sanitize_text_field( __( 'Username', 'buddypress-component-stats' ) );				
		$no_of_forum_posts = sanitize_text_field( __( 'Number of Forum Posts', 'buddypress-component-stats' ) );
		$results_found = sanitize_text_field( __( 'Results found on ', 'buddypress-component-stats' ) );
		$component_bet = sanitize_text_field( __( 'component between', 'buddypress-component-stats' ) );
		$and = sanitize_text_field( __( 'and', 'buddypress-component-stats' ) );
		
		if($component != 'friendship'){
			$html.= "<br/>
				<h4>$results_found<span class='component'>$component</span> $component_bet <strong><b>$start_date</b> $and <b>$final_date</b></strong></h4></br>									
				<table id='myTable' class='tablesorter'>
					<thead>
			";
		} else {
			$html.= "<br/>													
				<h4>$results_found<span class='component'>$component</span> component</h4></br>
				<table id='myTable' class='tablesorter'>
					<thead>
			";
		}
								
		$activity_tablename = $wpdb->prefix . 'bp_activity';
		$blogs_tablename = $wpdb->prefix . 'blogs';
		$comments_tablename = $wpdb->prefix . 'comments';
		$groups_tablename = $wpdb->prefix . 'bp_groups';
		$groups_members_tablename = $wpdb->prefix . 'bp_groups_members';
		$posts_tablename = $wpdb->prefix . 'posts';
		$options_tablename = $wpdb->prefix . 'options';
		$users_tablename = $wpdb->prefix . 'users';
		$usermeta_tablename = $wpdb->prefix . 'usermeta';
		$users_tablename = $wpdb->prefix . 'users';
		switch($component){
			case 'activity':
				$sql = "
					SELECT COUNT(type) as publications, $users_tablename.display_name, $users_tablename.ID, $users_tablename.user_email, $users_tablename.user_registered, MAX($activity_tablename.date_recorded) AS latest 
					FROM $activity_tablename, $users_tablename
					WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'activity' AND type = 'activity_update' AND date_recorded BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59' 
					GROUP BY(user_id) 
					ORDER BY (publications) DESC
				";								
				        					        
				$response = $wpdb->get_results($sql);
				$records = sizeof($response);								        
				
				if($response){					
					$query = true;
					paginateResults(2,1,$records);								
					$html.= "						
						<tr>
							<th>" . $user_avatar . "</th>
							<th>" . $user  . "</th>
							<th>" . $number_of_publications  . "/th>
							<th>" . $email  . "/th>
							<th>" . $registered_from . "</th>
							<th>" . $last_update . "</th>
						</tr>
						</thead>
						<tbody>
					";																																																						                    
					foreach ( $response as $rs ) {																																																													
						$total += $rs->publications;																													
						$html.="																									
						<tr>					
							<td class='avatar' align='center'>".get_avatar( $rs->user_email, 24 )."</td>
							<td>$rs->display_name</td>														
							<td align='center'><a href='#' onclick=\"javascript:detailed_publications('".$rs->ID."','".$start_date."','".$final_date."', 'activity')\">$rs->publications</a></td>
							<td>$rs->user_email</td>
							<td>".normalize_dates($rs->user_registered)."</td>
							<td>".normalize_dates($rs->latest)."</td>					
						</tr>";											
					}																																																																																																									
				} else {
					echo "<h3><strong>" . $no_records . "</strong></h3>";											
				}																							
			break;
			
			case 'groups':												
				$sql = "
					SELECT COUNT(type) as publications, $users_tablename.display_name, $users_tablename.ID, $users_tablename.user_email, $users_tablename.user_registered, MAX($activity_tablename.date_recorded) AS latest,					
					(SELECT COUNT($groups_members_tablename.user_id) FROM $groups_members_tablename, $users_tablename u2 WHERE u2.ID = $groups_members_tablename.user_id and u2.ID = $users_tablename.ID ) as groups							           														
					FROM $activity_tablename, $users_tablename			
					WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'groups' AND type = 'activity_update' 
					AND date_recorded BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
					GROUP BY $activity_tablename.user_id
					ORDER BY (publications) DESC
				"; 								   					        
				$response = $wpdb->get_results($sql);
				$records = sizeof($response);								        
				
				if($response){	
					
					$query = true;
					paginateResults(4,1,$records);
					
					$html.= "
						<tr>
							<th>" . $user_avatar . "</th>
							<th>" . $username . "</th>
							<th>Number of Groups Involved</th>
							<th>Invloved Groups Name</th>
							<th>" . $number_of_publications  . "/th>
							<th>" . $email  . "/th>
							<th>" . $registered_from . "</th>
							<th>" . $last_update . "</th>
						</tr>
						</thead>
						<tbody>
					";																																												
					
					foreach ( $response as $rs ) {																																																								
						$total += $rs->publications;																																								
						$subsql = "
							SELECT DISTINCT($groups_tablename.name) FROM $groups_members_tablename, $groups_tablename WHERE $groups_members_tablename.user_id = '".$rs->ID."' AND $groups_members_tablename.group_id = $groups_tablename.user_id
						";
						$subresponse = $wpdb->get_results($subsql);													        				
						if( $subresponse ) {
							$groupsname = '';
							foreach ( $subresponse as $rsg ){
								$groupsname.= $rsg->name."<br />";
							}
						}
																		
						$html.="
						<tr>					
							<td class='avatar' align='center'>".get_avatar( $rs->user_email, 24 )."</td>
							<td>$rs->display_name</td>							
							<td align='center'>$rs->groups</td>
							<td align='center'>$groupsname</td>
							<td align='center'><a href='#' onclick=\"javascript:detailed_publications('".$rs->ID."','".$start_date."','".$final_date."','groups')\">$rs->publications</a></td>
							<td>$rs->user_email</td>
							<td>".normalize_dates($rs->user_registered)."</td>
							<td>".normalize_dates($rs->latest)."</td>
						</tr>";															
					}																																																																																					
				} else {
					echo "<h3><strong>" . $no_records . "</strong></h3>";
				}														
			break;
			
			case 'forums': 
				$sql = "
					SELECT COUNT(type) as publications, $activity_tablename.content, $users_tablename.display_name, $users_tablename.ID, $users_tablename.user_email, $users_tablename.user_registered, MAX($activity_tablename.date_recorded) AS latest	
					FROM $activity_tablename, $users_tablename			
					WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'groups' AND (type = 'bbp_topic_create' OR type = 'bbp_topic_reply') 
					AND date_recorded BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
					GROUP BY $activity_tablename.user_id
					ORDER BY (publications) DESC
				"; 								   					        
				$response = $wpdb->get_results($sql);
				$records = sizeof($response);								        
				
				if($response){			
					
					$query = true;
					paginateResults(2,1,$records);
					
					$html.="
						<tr>
							<th>" . $user_avatar . "</th>
							<th>" . $username . "</th>				
							<th>" . $no_of_forum_posts . "</th>
							<th>" . $email  . "/th>
							<th>" . $registered_from . "</th>
							<th>" . $last_update . "</th>				
						</tr>
						</thead>
						<tbody>
					";																																												
					
					foreach ( $response as $rs ) {																																																								
						$total += $rs->publications;						
						$html.="						
							<tr>					
								<td class='avatar' align='center'>".get_avatar( $rs->user_email, 24 )."</td>
								<td>$rs->display_name</td>					
								<td align='center'><a href='#' onclick=\"javascript:detailed_publications('".$rs->ID."','".$start_date."','".$final_date."', 'forums')\">$rs->publications</a></td>
								<td>$rs->user_email</td>
								<td>".normalize_dates($rs->user_registered)."</td>
								<td>".normalize_dates($rs->latest)."</td>										
							</tr>
						";															
					}																																																																																																						
				} else {
					echo "<h3><strong>" . $no_records . "</strong></h3>";
				}
								
			break;
			
			case 'blogs': 
				$sql = "SELECT blog_id, domain, path, registered, last_updated FROM $blogs_tablename WHERE last_updated BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'";					   					        
				$response = $wpdb->get_results($sql);
				$records = sizeof($response);								        
				
				if($response){								
					$query=true;
					paginateResults(2,1,$records);
					
					$html.= "
					<tr>
						<th>Blogname</th>						
						<th>Blog URL</th>
						<th>Number of Articles Published</th>
						<th>Amount Comments</th>
						<th>Date Created</th>
						<th>" . $last_update . "</th>																	
					</tr>
					</thead>
					<tbody>";																																																						
										
					foreach ($response as $rs) {																																																																			
							$url = 'http://'.$rs->domain.$rs->path;						
							if($rs->blog_id != 1) {	
								$rs_blog_options_tablename = $wpdb->prefix . $rs->blog_id . '_options';
								$rs_blog_posts_tablename = $wpdb->prefix . $rs->blog_id . '_posts';
								$rs_blog_comments_tablename = $wpdb->prefix . $rs->blog_id . '_comments';
								
								$subsql = 
								"
									SELECT COUNT($rs_blog_comments_tablename.comment_ID) as comments, $rs_blog_options_tablename.option_value as blogname, 
									(SELECT COUNT($rs_blog_posts_tablename.post_type) FROM $rs_blog_posts_tablename 
									WHERE $rs_blog_posts_tablename.post_type = 'post' AND $rs_blog_posts_tablename.post_status = 'publish' 
									AND $rs_blog_posts_tablename.post_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59') as articles
									FROM $rs_blog_options_tablename, $rs_blog_comments_tablename 
									WHERE $rs_blog_options_tablename.option_name = 'blogname' AND $rs_blog_comments_tablename.comment_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
									ORDER BY articles DESC								 
								";							
								
								$responseblogs = $wpdb->get_results($subsql);																								
								foreach($responseblogs as $rsb) {							
									$total += $rsb->articles;								
									$html.="
									<tr>												
										<td>$rsb->blogname</td>									
										<td><a href='".$url."' target='_blank'>$url</a></td>
										<td align='center'><a href='#' onclick=\"javascript:detailed_publications('".$rs->blog_id."','".$start_date."','".$final_date."', 'blogs')\">$rsb->articles</a></td>													
										<td align='center'>$rsb->comments</td>
										<td>".normalize_dates($rs->registered)."</td>
										<td>".normalize_dates($rs->last_updated)."</td>
									</tr>";	
								}
							} else {						
								$subsql = 
								"
									SELECT COUNT($comments_tablename.comment_ID) as comments, $options_tablename.option_value as blogname, 
									(SELECT COUNT(wp_posts.ID) FROM $posts_tablename WHERE $posts_tablename.post_type = 'post' AND wp_posts.post_status = 'publish' 
									AND $posts_tablename.post_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59') as articles
									FROM $options_tablename, $comments_tablename 
									WHERE wp_options.option_name = 'blogname' AND wp_comments.comment_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
									ORDER BY articles DESC	
								";
																
								$responseblogs = $wpdb->get_results($subsql);																
								foreach($responseblogs as $rsb) {							
									$total += $rsb->articles;																
									$html.="
									<tr>												
										<td>$rsb->blogname</td>
										<td><a href='".$url."' target='_blank'>$url</a></td>
										<td align='center'><a href='#' onclick=\"javascript:detailed_publications('".$rs->blog_id."','".$start_date."','".$final_date."', 'blogs')\">$rsb->articles</a></td>
										<td align='center'>$rsb->comments</td>
										<td>".normalize_dates($rs->registered)."</td>
										<td>".normalize_dates($rs->last_updated)."</td>
									</tr>";	
								}
							}																						 																											
						}																																																																																																																												
				} else {
					echo "<h3><strong>" . $no_records . "</strong></h3>";
				}				 
			break;
						
			case 'comments': 
				$sql = "SELECT $users_tablename.ID, $users_tablename.display_name, $users_tablename.user_registered, $users_tablename.user_email FROM $users_tablename";					   					        
				$response = $wpdb->get_results($sql);
				$records = sizeof($response);								        
				
				if($response){			
					
					$query = true;
					paginateResults(2,1,$records);
					
					$html.= "
						<tr>
							<th>" . $user_avatar . "</th>
							<th>" . $username . "</th>												
							<th>Number of Comments on Blogs</th>
							<th>" . $registered_from . "</th>																							
						</tr>
						</thead>
						<tbody>
					";																																																																					
					
					$users = array();
					$pos=0;															
					
					foreach ($response as $rs) {																																																																																										
						
						$users[$pos]['UserName'] = $rs->display_name;	
						$users[$pos]['RegisteredDate'] = $rs->user_registered;
						$users[$pos]['email'] = $rs->user_email;
						
						$sqlblogs = "SELECT blog_id FROM $blogs_tablename";
						$responseblogs = $wpdb->get_results($sqlblogs); 												
						
						foreach($responseblogs as $rsblog){																																																																		
								if($rsblog->blog_id != 1) {
									$rsb_blog_options_tablename = $wpdb->prefix . $rsblog->blog_id . '_options';
									$rsb_blog_posts_tablename = $wpdb->prefix . $rsblog->blog_id . '_posts';
									$rsb_blog_comments_tablename = $wpdb->prefix . $rsblog->blog_id . '_comments';
									$rsb_blog_comments_tablename = $wpdb->prefix . $rsblog->blog_id . '_comments';
									$subsql = "
										SELECT COUNT(user_id) as comments
										FROM $rsb_blog_comments_tablename, $users_tablename 
										WHERE $rsb_blog_comments_tablename.user_id = $users_tablename.ID 
										AND $users_tablename.ID = ".$rs->ID."
										AND $rsb_blog_comments_tablename.comment_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
									";
																																				
								} else {						
									$subsql = "
										SELECT COUNT(user_id) as comments 
										FROM $users_tablename, $comments_tablename
										WHERE $comments_tablename.user_id = $users_tablename.ID 
										AND $posts_tablename.ID = ".$rs->ID."
										AND wp_comments.comment_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
									";																																				
								}
								
								$responseco = $wpdb->get_results($subsql);																																
								foreach ($responseco as $rscom){									
									$users[$pos]['Comments'] += $rscom->comments;
								}
						}
						$pos++;																																																						
					}																																								
														
					for($i=0; $i<sizeof($users); $i++){																																																
						$html.="
							<tr>																							
								<td class='avatar' align='center'>".get_avatar( $users[$i]['email'], 24 )."</td>
								<td>".$users[$i]['UserName']."</td>												
								<td align='center'>".$users[$i]['Comments']."</td>
								<td>".normalize_dates($users[$i]['RegisteredDate'])."</td>											
							</tr>
						";
						$total+=$users[$i]['Comments'];												
					}																																																																																																														
				} else {
					echo "<h3><strong>" . $no_records . "</strong></h3>";
				}				
			break;	
			
			case 'friendship':				
				$sql = "
					SELECT $users_tablename.display_name, $users_tablename.user_registered, $usermeta_tablename.meta_value, $users_tablename.user_email
					FROM $users_tablename, $usermeta_tablename
					WHERE $users_tablename.ID = $usermeta_tablename.user_id
					AND $usermeta_tablename.meta_key = 'total_friend_count'
					ORDER BY $usermeta_tablename.meta_value DESC					
				";
					   					        
				$response = $wpdb->get_results($sql);
				$records = sizeof($response);								        
				
				if($response){							
					$query = true;
					paginateResults(3,1,$records);					
					$html.= "
					<tr>
						<th> $user_avatar </th>
						<th> $username </th>												
						<th> $email  </th>
						<th>Number of Friends</th>
						<th> $registered_from </th>																							
					</tr>
					</thead>
					<tbody>";																																																																																								
					
					foreach ($response as $rs) {	
						$profile = get_bloginfo('home')."/members/".$rs->display_name."/";																																																																																																																							
						$html.="
							<tr>																							
								<td class='avatar' align='center'>".get_avatar( $rs->user_email, 24 )."</td>
								<td><a href=".$profile." target='_blank'>$rs->display_name</a></td>
								<td>$rs->user_email</td>
								<td align='center'>$rs->meta_value</td>
								<td>".normalize_dates($rs->user_registered)."</td>											
							</tr>
						";																					
					}																																																																																																																																
				} else {
					echo "<h3><strong>" . $no_records . "</strong></h3>";								
				}				 
			break;						
		}
				
		if($component != 'friendship'){
			$html.="
					<table>
					<tr>
						<td><i style='float:left; width:100%;'>Total publications on <span class='component'>$component </span> component: $total</i></td>
					</tr>
					</table>			
				</tbody>
				</table>					
			";
		} else {
			$html.="								
				</tbody>
				</table>					
			";		
		}
		
		echo $html;														
		if(isset($query)){									
			$paramspdf = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__))."/template/pdf.php?type=$component&start_date=$start_date&final_date=$final_date";
			$paramsxls = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__))."/template/xls.php?type=$component&start_date=$start_date&final_date=$final_date";											
			echo "<div id='export'>
				<div id='btnexport'>
					<div id='openexport'>
						<a href='#' id='clicExportar' onclick='javascript:return false;'>Export</a>
					</div>					
				</div>
				<div id='formats'>
					<div id='pdf'>																
						<a href='".$paramspdf."' id='hrefpdf' target='_blank'>PDF</a>
					</div>					
					<div id='xls'>
						<a href='".$paramsxls."' id='hrefpdf' target='_blank'>XLS</a>
					</div>
				</div>
			</div>";
		}
		die();																 																			
	}
		
	/* call action to display detailde results */
	add_action('wp_ajax_component_detailed_stats', 'get_component_detailed_stats');

	function get_component_detailed_stats() {
		global $wpdb;		
		$user_id = sanitize_text_field( $_POST['user_id'] );
		$start_date = sanitize_text_field( $_POST['start_date'] );
		$final_date = sanitize_text_field( $_POST['final_date'] );		
		$component = sanitize_text_field( $_POST['component'] );												
		$activity_tablename = $wpdb->prefix . 'bp_activity';
		$blogs_tablename = $wpdb->prefix . 'blogs';
		$comments_tablename = $wpdb->prefix . 'comments';
		$groups_tablename = $wpdb->prefix . 'bp_groups';
		$groups_members_tablename = $wpdb->prefix . 'bp_groups_members';
		$posts_tablename = $wpdb->prefix . 'posts';
		$options_tablename = $wpdb->prefix . 'options';
		$users_tablename = $wpdb->prefix . 'users';
		$usermeta_tablename = $wpdb->prefix . 'usermeta';
		$users_tablename = $wpdb->prefix . 'users';
		$html = '';
		$start_date = sanitize_text_field( $_POST['start_date'] );
		$final_date = sanitize_text_field( $_POST['final_date'] );
		$component = sanitize_text_field( $_POST['component'] );
		$user_avatar = sanitize_text_field( __( 'User Avatar', 'buddypress-component-stats' ) );
		$user = sanitize_text_field( __( 'User', 'buddypress-component-stats' ) );
		$number_of_publications = sanitize_text_field( __( 'Number of Publications', 'buddypress-component-stats' ) );
		$email = sanitize_text_field( __( 'e-mail', 'buddypress-component-stats' ) );
		$registered_from = sanitize_text_field( __( 'Registered from', 'buddypress-component-stats' ) );
		$last_update = sanitize_text_field( __( 'Last Update', 'buddypress-component-stats' ) );
		$no_records = sanitize_text_field( __( 'Number of Records Found', 'buddypress-component-stats' ) );
		$username = sanitize_text_field( __( 'Username', 'buddypress-component-stats' ) );				
		$no_of_forum_posts = sanitize_text_field( __( 'Number of Forum Posts', 'buddypress-component-stats' ) );
		$results_found = sanitize_text_field( __( 'Results found on ', 'buddypress-component-stats' ) );
		$comonent_bet = sanitize_text_field( __( 'component between', 'buddypress-component-stats' ) );
		$and = sanitize_text_field( __( 'and', 'buddypress-component-stats' ) );
		$and = sanitize_text_field( __( 'and', 'buddypress-component-stats' ) );
		
		switch($component){		
		case 'activity':						
			$sql = "
				SELECT $activity_tablename.content, $users_tablename.display_name, $activity_tablename.date_recorded
				FROM $activity_tablename, $users_tablename
				WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'activity' AND type = 'activity_update' AND date_recorded BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
				AND $users_tablename.ID = '".$user_id."'
				ORDER BY $activity_tablename.date_recorded DESC
			";
			
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);
			
			if($response){				
				$html = "<br/>													
					<table width='100%' id='tabladetalle'>
					<thead>
					<tr>								
						<th width='70%'>Content</th>
						<th width='30%'>Date</th>				
					</tr>
					</thead>
					<tbody>
				";
				
				foreach ( $response as $rs ) {																																																																																																																																				
					$html.="																									
					<tr>
						<td width='70%'>$rs->content</td>
						<td width='30%' align='right'>".normalize_dates($rs->date_recorded)."</td>
					</tr>";
				}
				
				$html.="							
					</tbody>
					</table>
					</br><h4>Records found for the user <strong><b>".$rs->display_name."</b></strong> 
					between <strong><b>$start_date</b> $and <b>$final_date</b></strong> on<strong><b> <span class='component'>$component</span> </b></strong>component</h4>					
				";
			}			
		break;
		
		case 'groups':
			$sql = "
				SELECT $activity_tablename.content, $users_tablename.display_name, $activity_tablename.date_recorded, $groups_members_tablename.user_title
				FROM $activity_tablename, $users_tablename, $groups_members_tablename
				WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'groups' AND type = 'activity_update' AND date_recorded BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
				AND $users_tablename.ID = '".$user_id."' AND $groups_tablename.id = $activity_tablename.item_id	
				ORDER BY $activity_tablename.date_recorded DESC
			"; 				      					        
			
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);
			if($response){
				
				$html = "<br/>													
				<table width='100%' id='tabladetalle'>
				<thead>
				<tr>								
					<th width='40%'>Content</th>
					<th width='30%'>Group Name</th>
					<th width='30%'>Date</th>				
				</tr>
				</thead>
				<tbody>";
				
				foreach ( $response as $rs ) {																																																																																		
					$html.="																									
					<tr>										
						<td width='40%'>$rs->content</td>
						<td width='30%'>$rs->name</td>
						<td width='30%' align='right'>".normalize_dates($rs->date_recorded)."</td>									
					</tr>";											
				}
				
				$html.="							
					</tbody>
					</table>
					</br><h4>Records found for the user <strong><b>".$rs->display_name."</b></strong> 
					between <strong><b>$start_date</b> $and <b>$final_date</b></strong> on<strong><b> <span class='component'>$component</span> </b></strong>component</h4>					
				";
			}	
		break;
		
		case 'forums':
			$sql = "
				SELECT $activity_tablename.content, $users_tablename.display_name, $activity_tablename.date_recorded, $activity_tablename.item_id			
				FROM $activity_tablename, $users_tablename 
				WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'groups' AND (type = 'bbp_topic_create' OR type = 'bbp_topic_reply') AND date_recorded 
				BETWEEN '$start_date' 00:00:00' AND '$final_date' 23:59:59'
				AND $users_tablename.ID = '".$user_id."'			
				ORDER BY $activity_tablename.date_recorded DESC
			"; 				      					        
			
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);
			if($response){
								
				$html = "<br/>													
					<table width='100%' id='tabladetalle'>
					<thead>
						<tr>								
							<th width='40%'>Content</th>
							<th width='20%'>Published on Forum</th>
							<th width='20%'>Forum Group</th>
							<th width='20%'>Date</th>				
						</tr>
					</thead>
					<tbody>
				";
				
				foreach ( $response as $rs ) {
					$subsql = "
						SELECT $activity_tablename.content, $groups_tablename.name 				
						FROM $activity_tablename, $groups_tablename
						WHERE $activity_tablename.component = 'groups' AND $activity_tablename.type = 'bbp_topic_create' AND $groups_tablename.id = $activity_tablename.item_id
						AND $activity_tablename.item_id	= '".$rs->item_id."'
					"; 
					
					$subresponse = $wpdb->get_results($subsql);
					if($subresponse){
						foreach ( $subresponse as $rsf ) {
							$forum = $rsf->content;
							$group = $rsf->name;
						}
					}																									
					
					$html.="																									
						<tr>										
							<td width='40%'>$rs->content</td>
							<td width='20%'>$forum</td>
							<td width='20%'>$group</td>
							<td width='20%' align='right'>".normalize_dates($rs->date_recorded)."</td>									
						</tr>
					";											
				}
				$html.="							
					</tbody>
					</table>
					</br><h4>Records found for the user <strong><b>".$rs->display_name."</b></strong> 
					between <strong><b>$start_date</b> $and <b>$final_date</b></strong> on<strong><b> <span class='component'>$component</span> </b></strong>component</h4>					
				";
			}				
		break;
		
		case 'blogs':
			$id_blog = sanitize_text_field( $_POST['user_id'] );																			
			$html = "<br/>													
			<table width='100%' id='tabladetalle'>
			<thead>
			<tr>								
				<th width='20%'>Article Title</th>
				<th width='20%'>Publication Date</th>
				<th width='60%'>
					Comments
					<table width='100%'>
						<tr>
							<td width='20%'>Comment Author</td> <td width='50%'>Content</td> <td width='30%'>Date</td>
						</tr>
					</table>
				</th>											
			</tr>
			</thead>
			<tbody>";													
			
			if($id_blog!=1) {
				$id_posts_tablename = $wpdb->prefix . $id_blog . '_posts';
				$id_options_tablename = $wpdb->prefix . $id_blog . '_options';
				$id_comments_tablename = $wpdb->prefix . $id_blog . '_comments';
				 $sql="
					SELECT $id_posts_tablename.post_title, $id_posts_tablename.post_date, $id_posts_tablename.ID
					FROM $id_posts_tablename
					WHERE $id_posts_tablename.post_status = 'publish' AND $id_posts_tablename.post_type = 'post' AND $id_posts_tablename.post_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
				";					
				$response = $wpdb->get_results($sql);						
				if($response) {																									
					foreach($response as $rs){													
						
						$subco="
							SELECT $id_comments_tablename.comment_author, $id_comments_tablename.comment_date, $id_comments_tablename.comment_content
							FROM $id_posts_tablename, $id_comments_tablename
							WHERE $id_comments_tablename.comment_post_ID = $id_posts_tablename.ID
							AND $id_comments_tablename.comment_post_ID = '".$rs->ID."'
							AND $id_comments_tablename.comment_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'																					 
						";																									
						
						$html.="																									
							<tr>										
								<td valign='top' width='20%'>$rs->post_title</td>
								<td valign='top' width='20%'>".normalize_dates($rs->post_date)."</td>
								<td valign='top' width='60%'>
						";													
						$subresponse = $wpdb->get_results($subco);													
						if ($subresponse) {
							foreach($subresponse as $rsco){																								
								
								$html.="																																		
									<table width='100%'>
										<tr>
											<td valign='top' align='left' width='20%'>$rsco->comment_author</td> 
											<td valign='top' width='50%' align='left'>$rsco->comment_content</td> 
											<td valign='top' width='30%' align='right'>".normalize_dates($rsco->comment_date)."</td>
										</tr>
									</table>
								";	
							}
						} else {
							$html.="																																		
								<table width='100%'>
									<tr>
										<td colspan='3'>Without Comments</td> 										
									</tr>
								</table>
							";
						}													
						$html.="
								</td>												
							</tr>
						";
					}
				}
				
				$slqblog = "SELECT $id_options_tablename.option_value FROM $id_options_tablename WHERE $id_options_tablename.option_name = 'blogname'";
				$responseblog = $wpdb->get_results($slqblog);
				$final_datelog = $responseblog[0]->option_value;					
					
			} else {
				$sql="
					SELECT $posts_tablename.post_title, $posts_tablename.post_date, $posts_tablename.ID
					FROM $posts_tablename
					WHERE $posts_tablename.post_status = 'publish' AND $posts_tablename.post_type = 'post' AND wp_posts.post_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'
				";					
				$response = $wpdb->get_results($sql);						
				if($response) {																									
					foreach($response as $rs){													
						$subco="
							SELECT $comments_tablename.comment_author, $comments_tablename.comment_date, $comments_tablename.comment_content
							FROM $posts_tablename, $comments_tablename
							WHERE $comments_tablename.comment_post_ID = $posts_tablename.ID AND $comments_tablename.comment_post_ID = '".$rs->ID."' AND $comments_tablename.comment_date BETWEEN '".$start_date." 00:00:00' AND '".$final_date." 23:59:59'								 
						";																			
						
						$html.="																									
							<tr>										
								<td valign='top' width='20%'>$rs->post_title</td>
								<td valign='top' width='20%'>".normalize_dates($rs->post_date)."</td>
								<td valign='top' width='60%'>
						";													
						$subresponse = $wpdb->get_results($subco);													
						if ($subresponse) {
							foreach($subresponse as $rsco){																
								
								$html.="																																		
									<table width='100%'>
										<tr>
											<td valign='top' align='left' width='20%'>$rsco->comment_author</td> 
											<td valign='top' width='50%' align='left'>$rsco->comment_content</td> 
											<td valign='top' width='30%' align='right'>".normalize_dates($rsco->comment_date)."</td>
										</tr>
									</table>
								";	
							}
						} else {
							$html.="																																		
									<table width='100%'>
										<tr>
											<td colspan='3'>Without Comments</td> 											
										</tr>
									</table>
								";
						}													
						$html.="
								</td>																	
							</tr>
						";														
					}
				}
				$slqblog = "SELECT $options_tablename.option_value FROM $options_tablename WHERE $options_tablename.option_name = 'blogname'";
				$responseblog = $wpdb->get_results($slqblog);
				$final_datelog = $responseblog[0]->option_value;								
			}																					      					        												
		$html.="							
			</tbody>
			</table>
			<br/><h4>Records found for the articles of the blog <span class='component'>$final_datelog</span> between <strong><b>$start_date</b> $and <b>$final_date</b></strong></h4>					
		";							
		break;				
		}
				
		echo $html;
		die();
	}
		
	/* Fucntion to paginate results using javascript */
	function paginateResults($a,$b,$records) {
	?>
		<script type="text/javascript" language="javascript">
			jQuery("#myTable").tablesorter({         
				sortList: [[<?php echo $a; ?>, <?php echo $b; ?>]] 
			});			
			jQuery('#green').smartpaginator({ 				
				totalrecords: <?php echo $records; ?>,
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
		</script>
    <?php
	}
		
	/* Function to normalize dates to format Day of week, Month-00 of year hour-am/pm */				
	function normalize_dates($dates){								
		$timezone = get_option('timezone_string');
		date_default_timezone_set($timezone);
		$data = explode(" ", $dates);
		$date = explode("-", $data[0]);
		$hour = explode(":", $data[1]);						
		$time = mktime($hour[0], $hour[1], $hour[2], $date[1], $date[2], $date[0]);												
		$returndate = ucfirst(strftime("%A", $time)).", ".ucfirst(strftime("%B",$time))."-".strftime("%d of %Y %I:%M", $time)."-".date("a");																				
		$returndate = utf8_encode($returndate);		
		return $returndate;
	}
																																																																													
	/* enqueue scripts and styles */		
	function my_admin_init() {
		/* $pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)); */
		$pluginfolder = WP_PLUGIN_URL . '/' .dirname(plugin_basename(__FILE__));
		wp_enqueue_style('jquery.ui.theme', $pluginfolder . '/template/css/jquery-ui.css');
		wp_enqueue_style('table-sorter', $pluginfolder . '/template/css/table-sorter.css');
		wp_enqueue_style('custom-layout', $pluginfolder . '/template/css/custom-layout.css');
		wp_enqueue_style('pagination-styles', $pluginfolder . '/template/css/smartpaginator.css');		
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs', $pluginfolder . '/template/js/ui.tabs.min.js');	
		wp_enqueue_script('jquery-ui-datepicker', $pluginfolder . '/template/js/ui.datepicker.min.js');		
		wp_enqueue_script('jquery-paginator', $pluginfolder . '/template/js/smartpaginator.js');
		wp_enqueue_script('jquery-table-sorter', $pluginfolder . '/template/js/jquery.tablesorter.js');												
	}
	
	/* enqueue admin scripts */
	function bpcs_admin_enqueue_scripts() {
		$pluginfolder = WP_PLUGIN_URL . '/' .dirname(plugin_basename(__FILE__));
		wp_enqueue_script('buddypress-component-stats-admin', $pluginfolder . '/js/buddypress-component-stats-admin.js');												
	}

	/* function que es llamada al dar click en el menu insertado en la barra de opciones de administracion de wordpress */
	function stats_panel(){	 
	  include('template/stats_panel.php');
	} 
			
	/* Crear un vinculo para el plugin en la pestaña Herramientas de wordpress */
	function buddypress_component_stats_add_menu(){   
	  add_menu_page('BuddyPress Component Stats', 'BuddyPress Component Stats', 'manage_options', 'buddypress-component-stats', 'stats_panel', plugin_dir_url( __FILE__ ).'/template/images/stats.png');	  
	}
		
	/* funcion que es llamda al desinstalar el plugin */
	function stats_install(){	   
	}
	
	/* funcion que es llamda al instalar el plugin */
	function stasts_desinstall(){	   
	}																			
?>