<?php		
	header('Content-type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=stats-".$_GET['type']."-".$_GET["start_date"]."-".$_GET["final_date"].".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
		
	require_once('../../../../wp-load.php');	
		
	$type = $_GET['type'];	
	global $wpdb;
	switch($type){
		case 'activity':								
			$sql = "
				SELECT COUNT(type) as publications, wp_users.display_name, wp_users.user_email, wp_users.user_registered, MAX(wp_bp_activity.date_recorded) AS lastest
				FROM wp_bp_activity, wp_users
				WHERE wp_users.ID = wp_bp_activity.user_id AND component = 'activity' AND type = 'activity_update' AND date_recorded BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
				GROUP BY(user_id)
				ORDER BY (publications) DESC
			";        				        
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        
			
			if($response){			
				$html = "				
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>								
				<table border='1'>
					<thead>
						<tr>
							<th>Username</th>
							<th align='center'>Amount Publications</th>
							<th>email</th>
							<th>Registered From</th>
							<th>Last Update</th>
						</tr>
					</thead>
				<tbody>";																																												
				foreach ( $response as $rs ) {																																																								
					$total += $rs->publications;																									
					$html.="
					<tr>
						<td>".normalize_stringsXLS($rs->display_name)."</td>
						<td  align='center'>$rs->publications</td>
						<td>$rs->user_email</td>
						<td>".normalize_datesXLS($rs->user_registered)."</td>
						<td>".normalize_datesXLS($rs->lastest)."</td>					
					</tr>";											
				}																																		
			}
		break;
		
		case 'groups':
			$sql = "
				SELECT COUNT(type) as publications, wp_users.display_name, wp_users.user_email, wp_users.user_registered, MAX(wp_bp_activity.date_recorded) AS lastest,
				(SELECT COUNT(wp_bp_groups_members.user_id) FROM wp_bp_groups_members, wp_users u2 WHERE u2.ID = wp_bp_groups_members.user_id and u2.ID = wp_users.ID ) as groups									
				FROM wp_bp_activity, wp_users			
				WHERE wp_users.ID = wp_bp_activity.user_id AND component = 'groups' AND type = 'activity_update' 
				AND date_recorded BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
				GROUP BY wp_bp_activity.user_id
				ORDER BY (publications) DESC
			"; 					       			        
			
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        
			
			if($response){					        																					
				$html = "
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>								
				<table border='1'>
					<thead>
						<tr>					
							<th>Username</th>
							<th>Amount Groups Involved</th>
							<th>Amount Publications on Groups</th>
							<th>email</th>
							<th>Registered From</th>
							<th>Last update</th>
						</tr>
					</thead>
				<tbody>";																																												
				
				foreach ( $response as $rs ) {																																																								
					$total += $rs->publications;										
					$html.="
					<tr>											
						<td>".normalize_stringsXLS($rs->display_name)."</td>
						<td align='center'>$rs->groups</td>
						<td align='center'>$rs->publications</td>
						<td>$rs->user_email</td>
						<td>".normalize_datesXLS($rs->user_registered)."</td>
						<td>".normalize_datesXLS($rs->lastest)."</td>					
					</tr>";															
				}								
			}
		break;
		
		case 'forums' :
			$sql = "
				SELECT COUNT(type) as publications, wp_users.display_name, wp_users.user_email, wp_users.user_registered, MAX(wp_bp_activity.date_recorded) AS lastest									
				FROM wp_bp_activity, wp_users			
				WHERE wp_users.ID = wp_bp_activity.user_id AND component = 'groups' AND (type = 'new_forum_topic' OR type = 'new_forum_post')
				AND date_recorded BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
				GROUP BY wp_bp_activity.user_id
				ORDER BY (publications) DESC
			"; 								   			
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){						
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>								
				    <table border='1'>
					<thead>
						<tr>							
							<th>Username</th>				
							<th>Amount Publications</th>
							<th>e-mail</th>
							<th>Registered From</th>
							<th>Last Update</th>			
						</tr>
					</thead>
				<tbody>";																																																
				foreach ( $response as $rs ) {																																																								
					$total += $rs->publications;										
					$html.="					
					<tr>											
						<td>".normalize_stringsXLS($rs->display_name)."</td>					
						<td align='center'>$rs->publications</td>
						<td>$rs->user_email</td>
						<td>".normalize_datesXLS($rs->user_registered)."</td>
						<td>".normalize_datesXLS($rs->lastest)."</td>										
					</tr>";															
				}																																																																																																			
			} 
		break;
		
		case 'blogs':
			$sql = "SELECT blog_id, domain, path, registered, last_updated FROM wp_blogs WHERE last_updated BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'";				   				        	$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){													
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>
				<table border='1'>
					<thead>
						<tr>
							<th>Blog Name</th>						
							<th>Blog URL</th>
							<th>Amount Articles published</th>
							<th>Amount Comments</th>
							<th>Date Created</th>
							<th>Last Update</th>																
						</tr>
					</thead>
				<tbody>";																																																															
				foreach ($response as $rs) {																																																																			
					$url = 'http://'.$rs->domain.$rs->path;						
					if($rs->blog_id != 1) {								
						$subsql = 
						"
							SELECT COUNT(wp_".$rs->blog_id."_comments.comment_ID) as comments, wp_".$rs->blog_id."_options.option_value as blogname, 
							(SELECT COUNT(wp_".$rs->blog_id."_posts.post_type) FROM wp_".$rs->blog_id."_posts 
							WHERE wp_".$rs->blog_id."_posts.post_type = 'post' AND wp_".$rs->blog_id."_posts.post_status = 'publish' 
							AND wp_".$rs->blog_id."_posts.post_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59') as articles
							FROM wp_".$rs->blog_id."_options, wp_".$rs->blog_id."_comments 
							WHERE wp_".$rs->blog_id."_options.option_name = 'blogname' AND wp_".$rs->blog_id."_comments.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
							ORDER BY articles DESC								 
						";
																														
						$responseblogs = $wpdb->get_results($subsql);																																		
						foreach($responseblogs as $rsb) {							
							$total += $rsb->articles;								
							$html.="
							<tr>												
								<td>$rsb->blogname</td>									
								<td><a href='".$url."' target='_blank'>$url</a></td>
								<td align='center'>$rsb->articles</td>													
								<td align='center'>$rsb->comments</td>
								<td>".normalize_datesXLS($rs->registered)."</td>
								<td>".normalize_datesXLS($rs->last_updated)."</td>
							</tr>";	
						}
					} else {						
						$subsql = 
						"
							SELECT COUNT(wp_comments.comment_ID) as comments, wp_options.option_value as blogname, 
							(SELECT COUNT(wp_posts.ID) FROM wp_posts WHERE wp_posts.post_type = 'post' AND wp_posts.post_status = 'publish' 
							AND wp_posts.post_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59') as articles
							FROM wp_options, wp_comments 
							WHERE wp_options.option_name = 'blogname' AND wp_comments.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
							ORDER BY articles DESC	
						";
														
						$responseblogs = $wpdb->get_results($subsql);																
						foreach($responseblogs as $rsb) {							
							$total += $rsb->articles;																
							$html.="
							<tr>												
								<td>$rsb->blogname</td>
								<td><a href='".$url."' target='_blank'>$url</a></td>
								<td align='center'>$rsb->articles</td>
								<td align='center'>$rsb->comments</td>
								<td>".normalize_datesXLS($rs->registered)."</td>
								<td>".normalize_datesXLS($rs->last_updated)."</td>
							</tr>";	
						}
					}																						 																											
				}																																																																																																																										
			}
		break;
		
		case 'comments' :			
			$sql = "SELECT wp_users.ID, wp_users.display_name, wp_users.user_registered, wp_users.user_email FROM wp_users";											
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){						
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>
				<table border='1'>
					<thead>
						<tr>							
							<th>Username</th>												
							<th>Amount Comments on Blogs</th>
							<th>Registered From</th>																							
						</tr>
					</thead>
				<tbody>";																																																																							
				$users = array();
				$pos=0;																			
				foreach ($response as $rs) {																																																																																															
					$users[$pos]['UserName'] = $rs->display_name;	
					$users[$pos]['RegisteredDate'] = $rs->user_registered;
					$users[$pos]['email'] = $rs->user_email;					
					$sqlblogs = "SELECT blog_id FROM wp_blogs";
					$responseblogs = $wpdb->get_results($sqlblogs); 																	
					foreach($responseblogs as $rsblog){																																																																		
							if($rsblog->blog_id != 1) {								
								$subsql = "
									SELECT COUNT(user_id) as comments
									FROM wp_".$rsblog->blog_id."_comments, wp_users 
									WHERE wp_".$rsblog->blog_id."_comments.user_id = wp_users.ID 
									AND wp_users.ID = ".$rs->ID."
									AND wp_".$rsblog->blog_id."_comments.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
									ORDER BY comments DESC
								";																																			
							} else {						
								$subsql = 
								"
									SELECT COUNT(user_id) as comments
									FROM wp_users, wp_comments
									WHERE wp_comments.user_id = wp_users.ID 
									AND wp_users.ID = ".$rs->ID."
									AND wp_comments.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
									ORDER BY comments DESC
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
							<td>".normalize_stringsXLS($users[$i]['UserName'])."</td>												
							<td align='center'>".$users[$i]['Comments']."</td>
							<td>".normalize_stringsXLS($users[$i]['RegisteredDate'])."</td>											
						</tr>
					";
					$total+=$users[$i]['Comments'];												
				}																																																										
			}			
		break;		
		case 'friendship':		
			$sql = "
				SELECT wp_users.display_name, wp_users.user_registered, wp_usermeta.meta_value, wp_users.user_email
				FROM wp_users, wp_usermeta
				WHERE wp_users.ID = wp_usermeta.user_id
				AND wp_usermeta.meta_key = 'total_friend_count'
				ORDER BY wp_usermeta.meta_value DESC					
			";											
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){							
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component</h4>								
				<table border='1'>
					<thead>
						<tr>							
							<th>Username</th>
							<th>email</th>												
							<th>Amount of Friends</th>
							<th>Registered From</th>																							
						</tr>
					</thead>
				<tbody>";																																																																																												
				foreach ($response as $rs) {																																																																																																																																				
					$profile = get_bloginfo('home')."/members/".$rs->display_name."/";
					$html.="
						<tr>																														
							<td><a href=".$profile." target='_blank'>".normalize_stringsXLS($rs->display_name)."</a></td>																			
							<td>$rs->user_email</td>
							<td align='center'>$rs->meta_value</td>
							<td>".normalize_datesXLS($rs->user_registered)."</td>											
						</tr>
					";																					
				}																																																																																												
		}
		break;										
	}				
		
	if($type != "friendship"){
        $html.="				
    		<table border='1'>				
    			<tr>										
    				<td>Total publications on $type: $total</td>				
    			</tr>
    		</table>
    		</tbody>
    		</table>
    	";
    } else {
        $html.= "
                </tbody>
  		    </table>
        ";  
    }
				
	echo $html;
	
	function normalize_datesXLS($dates) {		
		$data = explode(" ", $dates);
		$date = explode("-", $data[0]);
		$hour = explode(":", $data[1]);						
		$time = mktime($hour[0], $hour[1], $hour[2], $date[1], $date[2], $date[0]);											
		$returndate = ucfirst(strftime("%A", $time)).", ".ucfirst(strftime("%B",$time))."-".strftime("%d de %Y %I:%M", $time)."-".date("a");																				
		$returndate = str_replace(array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'),array('&aacute;','&eacute;','&iacute;','&oacute;','&uacute;','&ntilde;','&Aacute;','&Eacute;','&Iacute;','&Oacute;','&Uacute;', 	 	 		'&Ntilde;'),$returndate);		
		return $returndate;
	}
		
	function normalize_stringsXLS($str) {																							
		$str = str_replace(array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'),array('&aacute;','&eacute;','&iacute;','&oacute;','&uacute;','&ntilde;','&Aacute;','&Eacute;','&Iacute;','&Oacute;','&Uacute;','&Ntilde;'), 	 	$str);		
	 return $str;
	}
?>