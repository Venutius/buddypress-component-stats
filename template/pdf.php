<?php		
	require_once('../../../../wp-load.php');					
	require_once("../lib/dompdf/dompdf_config.inc.php");                            													
	$css = "		
		<style>				
			h4 {
				font-family:Arial Black, Gadget, sans-serif;
				font-style:italic;
				font-weight:lighter;
				font-stretch:wider;
			}			
			.total {
				float:left; 
				width:100%;
				font-family:Arial Black, Gadget, sans-serif;
				font-style:italic;
				font-weight:lighter;
				font-stretch:wider;
			}
			.component{
				text-transform:capitalize;
				font-weight:bold;
				font-style:italic;
			}
			table.tablesorter {
				font-family:arial;
				background-color: #CDCDCD;
				margin:10px 0pt 15px;
				font-size: 8pt;
				width: 100%;
				text-align: left;
			}
			table.tablesorter thead tr th, table.tablesorter tfoot tr th {
				background-color: #e6EEEE;
				border: 1px solid #FFF;
				font-size: 8pt;
				padding: 4px;
			}
			table.tablesorter thead tr .header {
				background-image: url(images/bg.gif);
				background-repeat: no-repeat;
				background-position: center right;
				cursor: pointer;
			}
			table.tablesorter tbody td {
				color: #3D3D3D;
				padding: 4px;
				background-color: #FFF;
				vertical-align: top;
			}
			table.tablesorter tbody tr.odd td {
				background-color:#F0F0F6;
			}
			table.tablesorter thead tr .headerSortUp {
				background-image: url(images/asc.gif);
			}
			table.tablesorter thead tr .headerSortDown {
				background-image: url(images/desc.gif);
			}
			table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {
				background-color: #8dbdd8;				
			}
		</style>
	";				
			
	global $wpdb;	
	
	$type = $_GET['type'];
	$nameFile = "stats-".$type."-".$_GET["start_date"]."-".$_GET["final_date"].".pdf";			
	$site = get_option('blogname');
	$total = 0;		
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
	
	switch($type){
		case 'activity' :										
			$sql = "
				SELECT COUNT(type) as publications, $users_tablename.display_name, $users_tablename.user_email, $users_tablename.user_registered, MAX($activity_tablename.date_recorded) AS latest
				FROM $activity_tablename, $users_tablename 
				WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'activity' AND type = 'activity_update' AND date_recorded
				BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
				GROUP BY(user_id)
				ORDER BY (publications) DESC
			"; 
			       				        
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){							
				$html = $css."							
					<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>				 
					<table id='myTable' class='tablesorter'>
						<thead>
							<tr>
								<th>User Avatar</th>
								<th>Username</th>
								<th>Amount Publications</th>
								<th>e-mail</th>
								<th>Registered From</th>
								<th>Last Update</th>
							</tr>
						</thead>
					<tbody>
				";																																												
				
				foreach ( $response as $rs ) {																																																								
					$total+= $rs->publications;								
					$html.="
					<tr>
						<td class='avatar' align='center'>".get_avatar( $rs->user_email, 24 )."</td>
						<td>".normalize_stringsPDF($rs->display_name)."</td>
						<td align='center'>$rs->publications</td>
						<td>$rs->user_email</td>
						<td>".normalize_datesPDF($rs->user_registered)."</td>
						<td>".normalize_datesPDF($rs->latest)."</td>					
					</tr>";											
				}																																						
			}
		break;
		
		case 'groups' :																												
			$sql = "
				SELECT COUNT(type) as publications, $users_tablename.display_name, $users_tablename.user_email, $users_tablename.user_registered, MAX($activity_tablename.date_recorded) AS latest,
				(SELECT COUNT($groups_members_tablename.user_id) FROM $groups_members_tablename, $users_tablename u2 WHERE u2.ID = $groups_members_tablename.user_id and u2.ID = $users_tablename.ID ) as groups									
				FROM $activity_tablename, $users_tablename			
				WHERE wp_users.ID = $activity_tablename.user_id AND component = 'groups' AND type = 'activity_update' 
				AND date_recorded BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
				GROUP BY $activity_tablename.user_id
				ORDER BY (publications) DESC
			"; 					       			        			
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){					        																					
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>								
				<table id='myTableGrupos' class='tablesorter'>
					<thead>
						<tr>
							<th>User Avatar</th>
							<th>Username</th>
							<th>Amount Groups Involved</th>
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
						<td class='avatar' align='center'>".get_avatar( $rs->user_email, 24 )."</td>
						<td>".normalize_stringsPDF($rs->display_name)."</td>
						<td>$rs->groups</td>
						<td>$rs->publications</td>
						<td>$rs->user_email</td>
						<td>".normalize_datesPDF($rs->user_registered)."</td>
						<td>".normalize_datesPDF($rs->latest)."</td>					
					</tr>";															
				}								
			}
		break;		
		case 'forums' :
			$sql = "
				SELECT COUNT(type) as publications, $users_tablename.display_name, $users_tablename.user_email, $users_tablename.user_registered, MAX($activity_tablename.date_recorded) AS latest
				FROM $activity_tablename, $users_tablename			
				WHERE $users_tablename.ID = $activity_tablename.user_id AND component = 'groups' AND (type = 'bbp_topic_create' OR type = 'bbp_topic_reply')
				AND date_recorded BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
				GROUP BY $activity_tablename.user_id
				ORDER BY (publications) DESC
			"; 								   			
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){						
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>								
				<table id='myTable' class='tablesorter'>
					<thead>
						<tr>
							<th>User Avatar</th>
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
						<td class='avatar' align='center'>".get_avatar( $rs->user_email, 24 )."</td>
						<td>".normalize_stringsPDF($rs->display_name)."</td>					
						<td>$rs->publications</td>
						<td>$rs->user_email</td>
						<td>".normalize_datesPDF($rs->user_registered)."</td>
						<td>".normalize_datesPDF($rs->latest)."</td>										
					</tr>";															
				}																																																																																																			
			} 
		break;		
		case 'blogs':
			$sql = "SELECT blog_id, domain, path, registered, last_updated FROM $blogs_tablename WHERE last_updated BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'";				   				        	$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){													
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>
				<table id='myTable' class='tablesorter'>
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
						$rs_blog_options_tablename = $wpdb->prefix . $rs->blog_id . '_options';
						$rs_blog_posts_tablename = $wpdb->prefix . $rs->blog_id . '_posts';
						$rs_blog_comments_tablename = $wpdb->prefix . $rs->blog_id . '_comments';
						$subsql = 
						"
							SELECT COUNT($rs_blog_comments_tablename.comment_ID) as comments, $rs_blog_options_tablename.option_value as blogname, 
							(SELECT COUNT($rs_blog_posts_tablename.post_type) FROM $rs_blog_posts_tablename 
							WHERE $rs_blog_posts_tablename.post_type = 'post' AND $rs_blog_posts_tablename.post_status = 'publish' 
							AND $rs_blog_posts_tablename.post_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59') as articles
							FROM $rs_blog_options_tablename, $rs_blog_comments_tablename 
							WHERE $rs_blog_options_tablename.option_name = 'blogname' AND $rs_blog_comments_tablename.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
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
								<td>".normalize_dates($rs->registered)."</td>
								<td>".normalize_dates($rs->last_updated)."</td>
							</tr>";	
						}
					} else {						
						$subsql = 
						"
							SELECT COUNT($comments_tablename.comment_ID) as comments, $options_tablename.option_value as blogname, 
							(SELECT COUNT($posts_tablename.ID) FROM $posts_tablename WHERE $posts_tablename.post_type = 'post' AND $posts_tablename.post_status = 'publish' 
							AND $posts_tablename.post_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59') as articles
							FROM $options_tablename, $comments_tablename 
							WHERE $options_tablename.option_name = 'blogname' AND $comments_tablename.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
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
								<td>".normalize_dates($rs->registered)."</td>
								<td>".normalize_dates($rs->last_updated)."</td>
							</tr>";	
						}
					}																						 																											
				}																																																																																																																										
			}
		break;		
		case 'comments' :			
			$sql = "SELECT $users_tablename.ID, $users_tablename.display_name, $users_tablename.user_registered, $users_tablename.user_email FROM $users_tablename";											
			$response = $wpdb->get_results($sql);
			$records = sizeof($response);								        			
			if($response){						
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component between <strong>".$_GET["start_date"]."</strong> and <strong>".$_GET["final_date"]."</strong></h4>
				<table id='myTable' class='tablesorter'>
					<thead>
						<tr>
							<th>User Avatar</th>
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
					$sqlblogs = "SELECT blog_id FROM $blogs_tablename";
					$responseblogs = $wpdb->get_results($sqlblogs); 																	
					foreach($responseblogs as $rsblog){																																																																		
							if($rsblog->blog_id != 1) {								
								$rsb_blog_options_tablename = $wpdb->prefix . $rsblog->blog_id . '_options';
								$rsb_blog_posts_tablename = $wpdb->prefix . $rsblog->blog_id . '_posts';
								$rsb_blog_comments_tablename = $wpdb->prefix . $rsblog->blog_id . '_comments';
								$subsql = "
									SELECT COUNT(user_id) as comments
									FROM $rsb_blog_comments_tablename, $users_tablename 
									WHERE $rsb_blog_comments_tablename.user_id = $users_tablename.ID 
									AND $users_tablename.ID = ".$rs->ID."
									AND $rsb_blog_comments_tablename.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
									ORDER BY comments DESC
								";																																			
							} else {						
								$subsql = 
								"
									SELECT COUNT(user_id) as comments
									FROM $users_tablename, $comments_tablename
									WHERE $comments_tablename.user_id = $users_tablename.ID 
									AND $users_tablename.ID = ".$rs->ID."
									AND $comments_tablename.comment_date BETWEEN '".$_GET["start_date"]." 00:00:00' AND '".$_GET["final_date"]." 23:59:59'
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
							<td align='center'>".get_avatar($users[$i]['email'], 24 )."</td>
							<td>".normalize_stringsPDF($users[$i]['UserName'])."</td>												
							<td>".$users[$i]['Comments']."</td>
							<td>".normalize_datesPDF($users[$i]['RegisteredDate'])."</td>											
						</tr>
					";
					$total+=$users[$i]['Comments'];												
				}																																																										
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
				$html = $css."
				<h4>Results found on <span class='component'>$type</span> component</h4>								
				<table id='myTable' class='tablesorter'>
					<thead>
						<tr>
							<th>User Avatar</th>
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
							<td align='center'>".get_avatar( $rs->user_email, 24 )."</td>
							<td><a href=".$profile." target='_blank'>".normalize_stringsPDF($rs->display_name)."</a></td>																			
							<td>$rs->user_email</td>
							<td align='center'>$rs->meta_value</td>
							<td>".normalize_datesPDF($rs->user_registered)."</td>											
						</tr>
					";																					
				}																																																																																												
		}
		break;																			
	}
	
if($type != 'friendship'){
	$html .= "
			</tbody>
		</table>
		<table>
			<tr>
				<td>
					<i class='total'>Total publications on <span class='component'> $type </span>component: $total</i>
				</td>
			</tr>
		</table>									
	";	
}
				
$dompdf = new DOMPDF();
$dompdf->set_paper('A4', 'landscape');
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream($nameFile);						
exit;	
		
function normalize_datesPDF($dates) {		
	$data = explode(" ", $dates);
	$date = explode("-", $data[0]);
	$hour = explode(":", $data[1]);						
	$time = mktime($hour[0], $hour[1], $hour[2], $date[1], $date[2], $date[0]);											
	$returndate = ucfirst(strftime("%A", $time)).", ".ucfirst(strftime("%B",$time))."-".strftime("%d de %Y %I:%M", $time)."-".date("a");																				
	$returndate = str_replace(array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'),array('&aacute;','&eacute;','&iacute;','&oacute;','&uacute;','&ntilde;','&Aacute;','&Eacute;','&Iacute;','&Oacute;','&Uacute;','&Ntilde;'),$returndate);		
	return $returndate;
}
	
function normalize_stringsPDF($str) {																							
 $str = str_replace(array('á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'),array('&aacute;','&eacute;','&iacute;','&oacute;','&uacute;','&ntilde;','&Aacute;','&Eacute;','&Iacute;','&Oacute;','&Uacute;','&Ntilde;'),$str);		
 return $str;
}
																												
?>