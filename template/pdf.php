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
	
	switch($type){
		case 'activity' :										
			$sql = "
				SELECT COUNT(type) as publications, wp_users.display_name, wp_users.user_email, wp_users.user_registered, MAX(wp_bp_activity.date_recorded) AS lastest
				FROM wp_bp_activity, wp_users 
				WHERE wp_users.ID = wp_bp_activity.user_id AND component = 'activity' AND type = 'activity_update' AND date_recorded
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
						<td>".normalize_datesPDF($rs->lastest)."</td>					
					</tr>";											
				}																																						
			}
		break;
		
		case 'groups' :																												
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
						<td>".normalize_datesPDF($rs->lastest)."</td>					
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
						<td>".normalize_datesPDF($rs->lastest)."</td>										
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
								<td>".normalize_dates($rs->registered)."</td>
								<td>".normalize_dates($rs->last_updated)."</td>
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
								<td>".normalize_dates($rs->registered)."</td>
								<td>".normalize_dates($rs->last_updated)."</td>
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