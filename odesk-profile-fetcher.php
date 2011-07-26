<?php
/*
Plugin Name: Odesk Profile Fetcher
Plugin URI: http://reygcalantaol.com/odesk-profile-fetcher
Plugin Demo: http://reygcalantaol.com/php-programmer-asp-programmer-web-developer-about/
Description: This plugin uses Odesk API to display odesk profile in your website. If you find this plugin useful, please consider making a small donation to help this maintained and free to everyone. <a href="http://reygcalantaol.com/php-asp-programmer-donation">Click here to donate.</a>
Version: 0.10
Author: Rey Calanta-ol
Author URI: http://reygcalantaol.com
License: GPL2

Copyright 2011 Odesk Profile Fetcher  (email : reygcalantaol@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//add the admin options page

if (isset($_POST['profileKey'])) {
	update_option('odesk_profile_key', $_POST['profileKey'], ' ', 'yes');
}

add_action('admin_menu', 'odesk_admin_page');
add_action('wp_print_scripts', 'add_odesk_scripts');
add_action('wp_print_styles', 'add_odesk_style');

function odesk_admin_page() {
	add_options_page('Odesk Profile Fetcher', 'Odesk Profile Fetcher', 'manage_options', 'odesk-profile-fetcher', 'odesk_options_page');
}

//display the admin options page
function odesk_options_page() { ?>
    <div><h2>Odesk Profile Fetcher Options</h2>
    This plugin requires Odesk.com account. If you do not have one, please signup <a href="https://www.odesk.com/w/signup.php?" target="_blank" rel="nofollow">here</a>.<br />
    Please input your profile key, you can found it at your Odesk public profile page somewhere below your photo at the last part of the permalink.<br />Example: https://www.odesk.com/users/~~xxxxxxxxxxxxxxx, the last part including the tilde is your profile number.<br /><br />
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>&updated=true" method="post">
        <table>
        <tr><td>Odesk Profile Number:</td><td><input name="profileKey" type="text" value="<?php echo get_option('odesk_profile_key'); ?>" size="50"/></td></tr>
        <tr><td></td><td><input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></td></tr>
        </table>
    </form>
    </div>
<?php
}

add_shortcode('odesk_profile', 'OdeskProfile');

function OdeskProfile() {
	$profile = getOdeskProfile(get_option("odesk_profile_key"));
	$output = "";
	if (!$profile) {
		$output = "Invalid profile key!";
	}else{
		$output .= getProfileHeader($profile);
		$output .= "<div class=\"clear_both\"></div>"; //begin tabber
		$output .= "<div class=\"tabber\">"; //begin tabber
		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2> Overview </h2>"; //Title
	  	$output .= getOverview($profile); //Content
     	$output .= "</div>"; //End Tab

		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Resume</h2>"; //Title
	  	$output .= getSkills($profile); //Content
		$output .= getCertification($profile); //Content
		$output .= getEmployment($profile); //Content
		$output .= getOther($profile); //Content
		$output .= getEducation($profile); //Content		
     	$output .= "</div>"; //End Tab
		
		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Work History and Feedback (".$profile->profile->assignments_count.")</h2>"; //Title
		$output .= getWorkHistory($profile);
		$output .= getWorkHistoryFP($profile);		
		$output .= "</div>"; //End Tab

		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Test (".$profile->profile->tsexams_count.")</h2>"; //Title
	  	$output .= getTest($profile); //Content
     	$output .= "</div>"; //End Tab
		
		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Portfolio (".$profile->profile->dev_tot_feedback.")</h2>"; //Title
	  	$output .= getPortfolio($profile); //Content
     	$output .= "</div>"; //End Tab		
		
		$output .= "</div>";	//End tabber	
		$output .= "<div style=\"text-align:right; font-size:10px;\"><i>Odesk Profile Fetcher Plugin by <a href=\"http://reygcalantaol.com\">Rey G. Calanta-ol</a></i></div";	//Footer	
	}
	print_r($output);
	//print_r($profile->profile->assignments->hr->job);
}

function getOverview($profile) {
	$ready = ($profile->profile->dev_is_ready == 1) ? 'Yes' : 'No';
	$overview .= "<div class=\"odesk_overview\">";	
	$overview .= "<span>".$profile->profile->dev_blurb_short."</span><br /><br />";
	$overview .= "<div class=\"odesk_overview_list\"><ul>";
	$overview .= "<li><span class=\"caption\">Total Hours: </span><span class=\"value\">".$profile->profile->dev_total_hours."</span></li>";
	$overview .= "<li><span class=\"caption\">Total Contracts: </span><span class=\"value\">".$profile->profile->dev_billed_assignments."</span></li>";
	$overview .= "<li><span class=\"caption\">Location: </span><span class=\"value\">".$profile->profile->dev_location."</span></li>";
	$overview .= "<li><span class=\"caption\">English Skills: </span><span class=\"value\">".$profile->profile->dev_eng_skill."</span></li>";
	$overview .= "<li><span class=\"caption\">Member Since: </span><span class=\"value\">".$profile->profile->dev_member_since."</span></li>";
	$overview .= "<li><span class=\"caption\">Last Worked: </span><span class=\"value\">".$profile->profile->dev_last_worked."</span></li>";
	$overview .= "<li><span class=\"caption\">Odesk Ready: </span><span class=\"value\">".$ready."</span></li>";
	$overview .= "</ul></div>";
	$overview .= "<div class=\"clear_both\"></div>";
	$overview .= "</div>";
	
	return $overview;
}

function getTest($profile) {
	
	$work = "<div>";
	$work .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$work .= "<tr>";
	$work .= "<td colspan=5><span><strong>oDesk Tests Taken</strong></span></td>";
	$work .= "</tr>";	
	$work .= "<tr>";
	$work .= "<th width=45%>Name of Test</th>";
	$work .= "<th width=10%>Score</th>";
	$work .= "<th width=15%>Percentile</th>";
	$work .= "<th width=20%>Date Taken</th>";
	$work .= "<th width=10%>Duration</th>";
	$work .= "</tr>";
	$work .= "<tbody>";
	
	foreach ($profile->profile->tsexams->tsexam as $hr) {

	$work .= "<tr>";
	$work .= "<td>".$hr->ts_name."</td>";
	$work .= "<td>".$hr->ts_score."</td>";
	$work .= "<td>".$hr->ts_percentile."</td>";
	$work .= "<td>".$hr->ts_when."</td>";
	$work .= "<td>".$hr->ts_duration." min</td>";	
	$work .= "</tr>";
	
	}
	
	$work .= "</tbody>";
	$work .= "</table></div>";		
	
	return $work;
	
}


function getPortfolio($profile) {
	
	$portfolio = "<div>";
	$portfolio .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$portfolio .= "<tr>";
	$portfolio .= "<td colspan=2><span><strong>Portfolio</strong></span></td>";
	$portfolio .= "</tr>";	
	$portfolio .= "<tbody>";
	
	foreach ($profile->profile->portfolio_items->portfolio_item as $hr) {
	$date = (int)$hr->pi_completed;
	$portfolio .= "<tr>";
	$portfolio .= "<td valign=top><img src=\"".$hr->pi_thumbnail."\" /></td>";
	$portfolio .= "<td><ul>";
	$portfolio .= "<li><strong>Project Title:</strong> ".$hr->pi_title."</li>";
	$portfolio .= "<li><strong>Completed:</strong> ".date('M d, Y',$date)."</li>";
	$portfolio .= "<li><strong>Category:</strong> ".$hr->pi_category->pi_category_level1.">".$hr->pi_category->pi_category_level2."</li>";
	$portfolio .= "<li><strong>URL:</strong> ".$hr->pi_url."</li>";
	$portfolio .= "<li><strong>Description:</strong> ".$hr->pi_description."</li>";
	$portfolio .= "</ul></td>";
	$portfolio .= "</tr>";
	
	}
	
	$portfolio .= "</tbody>";
	$portfolio .= "</table></div>";		
	
	return $portfolio;
	
}


function getWorkHistory($profile) {
	
	$work = "<div>";
	$work .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$work .= "<tr>";
	$work .= "<td colspan=5><span><strong>Hourly Job History</strong></span></td>";
	$work .= "</tr>";	
	$work .= "<tr>";
	$work .= "<th width=13%>Emp ID</th>";
	$work .= "<th width=15%>From/To</th>";
	$work .= "<th width=20%>Job Title</th>";
	$work .= "<th width=15%>Paid</th>";
	$work .= "<th width=37%>Feedback</th>";
	$work .= "</tr>";
	$work .= "<tbody>";
	
	foreach ($profile->profile->assignments->hr->job as $hr) {
	if ($hr->as_client != 318878) {	
	$charge = number_format((double)$hr->as_total_charge,2);
	$feedback = ($hr->as_status == 'Closed') ? $hr->feedback->comment : '<i>Job in progress</i>';
	$work .= "<tr>";
	$work .= "<td>".$hr->as_client."</td>";
	$work .= "<td>".$hr->as_from."-".$hr->as_to."</td>";
	$work .= "<td>".$hr->as_opening_title."</td>";
	$work .= "<td>$".$charge. " (".$hr->as_total_hours." hrs @ ".$hr->as_rate."/hr)</td>";
	$work .= "<td>".$feedback."</td>";	
	$work .= "</tr>";
	}
	}
	
	$work .= "</tbody>";
	$work .= "</table>";		
	
	return $work;
	
}

function getWorkHistoryFP($profile) {
	
	$work = "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$work .= "<tr>";
	$work .= "<td colspan=5><span><strong>Fixed-Price Job History</strong></span></td>";
	$work .= "</tr>";	
	$work .= "<tr>";
	$work .= "<th width=13%>Emp ID</th>";
	$work .= "<th width=15%>From/To</th>";
	$work .= "<th width=20%>Job Title</th>";
	$work .= "<th width=15%>Paid</th>";
	$work .= "<th width=37%>Feedback</th>";
	$work .= "</tr>";
	$work .= "<tbody>";
	
	foreach ($profile->profile->assignments->fp->job as $hr) {
	$charge = number_format((double)$hr->as_total_charge,2);
	$feedback = ($hr->as_status == 'Closed') ? $hr->feedback->comment : '<i>Job in progress</i>';
	$work .= "<tr>";
	$work .= "<td>".$hr->as_client."</td>";
	$work .= "<td>".$hr->as_from."-".$hr->as_to."</td>";
	$work .= "<td>".$hr->as_opening_title."</td>";
	$work .= "<td>$".$charge."</td>";
	$work .= "<td>".$feedback."</td>";	
	$work .= "</tr>";
	
	}
	
	$work .= "</tbody>";
	$work .= "</table></div>";		
	
	return $work;
	
}

function getSkills($profile) {
	
	$skills = "<div>";
	$skills .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$skills .= "<tr>";
	$skills .= "<td colspan=5><span><strong>Skills</strong></span></td>";
	$skills .= "</tr>";	
	$skills .= "<tr>";
	$skills .= "<th width=10%>Skill</th>";
	$skills .= "<th width=15%>Experience</th>";
	$skills .= "<th width=10%>Level</th>";
	$skills .= "<th width=15%>Last Used</th>";
	$skills .= "<th width=50%>Description</th>";
	$skills .= "</tr>";
	$skills .= "<tbody>";
	
	foreach ($profile->profile->skills->skill as $skill) {
		
	$skills .= "<tr>";
	$skills .= "<td>".$skill->skl_name."</td>";
	$skills .= "<td>".$skill->skl_year_exp."</td>";
	$skills .= "<td>".$skill->skl_level."</td>";
	$skills .= "<td>".$skill->skl_last_used."</td>";
	$skills .= "<td>".$skill->skl_comment."</td>";	
	$skills .= "</tr>";
	
	}
	
	$skills .= "</tbody>";
	$skills .= "</table>";		
	
	return $skills;
	
}

function getCertification($profile) {
	
	$certificate .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$certificate .= "<tr>";
	$certificate .= "<td colspan=4><span><strong>Certification</strong></span></td>";
	$certificate .= "</tr>";	
	$certificate .= "<tr>";
	$certificate .= "<th width=10%>Date</th>";
	$certificate .= "<th width=25%>Name</th>";
	$certificate .= "<th width=25%>Organization</th>";
	$certificate .= "<th width=40%>Description</th>";
	$certificate .= "</tr>";
	$certificate .= "<tbody>";
	
	foreach ($profile->profile->certification->certificate as $c) {
		
	$certificate .= "<tr>";
	$certificate .= "<td>".$c->cer_earned."</td>";
	$certificate .= "<td>".$c->cer_name."</td>";
	$certificate .= "<td>".$c->cer_organisation."</td>";
	$certificate .= "<td>".$c->cer_comment."</td>";
	$certificate .= "</tr>";
	
	}
	
	$certificate .= "</tbody>";
	$certificate .= "</table>";		
	
	return $certificate;
	
}


function getEmployment($profile) {
	
	$employ .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$employ .= "<tr>";
	$employ .= "<td colspan=5><span><strong>Employment History</strong></span></td>";
	$employ .= "</tr>";	
	$employ .= "<tr>";
	$employ .= "<th width=10%>From</th>";
	$employ .= "<th width=10%>To</th>";
	$employ .= "<th width=20%>Company</th>";
	$employ .= "<th width=30%>Title/Role</th>";
	$employ .= "<th width=30%>Description</th>";
	$employ .= "</tr>";
	$employ .= "<tbody>";
	
	foreach ($profile->profile->experiences->experience as $c) {
		
	$employ .= "<tr>";
	$employ .= "<td>".$c->exp_from."</td>";
	$employ .= "<td>".$c->exp_to."</td>";
	$employ .= "<td>".$c->exp_company."</td>";
	$employ .= "<td>".$c->exp_title."</td>";
	$employ .= "<td>".$c->exp_comment."</td>";
	$employ .= "</tr>";
	
	}
	
	$employ .= "</tbody>";
	$employ .= "</table>";		
	
	return $employ;
	
}


function getOther($profile) {
	
	$other .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$other .= "<tr>";
	$other .= "<td colspan=2><span><strong>Other Experience</strong></span></td>";
	$other .= "</tr>";	
	$other .= "<tbody>";
	
	foreach ($profile->profile->oth_experiences->oth_experience as $c) {
		
	$other .= "<tr>";
	$other .= "<td>".$c->exp_subject."</td>";
	$other .= "<td>".$c->exp_description."</td>";
	$other .= "</tr>";
	
	}
	
	$other .= "</tbody>";
	$other .= "</table>";		
	
	return $other;	
}

function getEducation($profile) {
	
	$education .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	$education .= "<tr>";
	$education .= "<td colspan=6><span><strong>Education</strong></span></td>";
	$education .= "</tr>";
	$education .= "<tr>";
	$education .= "<th width=10%>From</th>";
	$education .= "<th width=10%>To</th>";
	$education .= "<th width=15%>School</th>";
	$education .= "<th width=15%>Degree</th>";
	$education .= "<th width=20%>Major</th>";
	$education .= "<th width=30%>Description</th>";
	$education .= "</tr>";	
	$education .= "<tbody>";
	
	foreach ($profile->profile->education->institution as $c) {
		
	$education .= "<tr>";
	$education .= "<td>".$c->ed_from."</td>";
	$education .= "<td>".$c->ed_to."</td>";
	$education .= "<td>".$c->ed_school."</td>";
	$education .= "<td>".$c->ed_degree."</td>";
	$education .= "<td>".$c->ed_area."</td>";
	$education .= "<td>".$c->ed_comment."</td>";	
	$education .= "</tr>";
	
	}
	
	$education .= "</tbody>";
	$education .= "</table></div>";		
	
	return $education;	
}

function getProfileHeader($profile) {
	$header = "<div class=\"odesk_header\">";
	$header .= "<div class=\"img\"><img src=\"".$profile->profile->dev_portrait."\" /><br />";
	$header .= "<a target=\"_blank\" href=\"https://www.odesk.com/users/".get_option("odesk_profile_key")."?wsrc=tile_2&wlnk=btn&_scr=hireme_l\">";
	$header .= "<img src=\"".getURL().'th_hire_me_button.jpg'."\" border=0 width=100 /></a></div>";
	$header .= "<div class=\"header_profilename\"><strong><a target=\"_blank\" href=\"https://www.odesk.com/users/".get_option("odesk_profile_key")."?wsrc=tile_2&wlnk=btn&_scr=hireme_l\">".$profile->profile->dev_short_name."</a></strong></h1>";
	$header .= "<div class=\"border_line\"></div>";
	$header .= "<span>".$profile->profile->profile_title_full."</span><br />";
	$header .= "<span>Current hourly rate: $<strong>".$profile->profile->dev_bill_rate."/hr</strong></span><br />";
	$header .= "<span>Member since ".$profile->profile->dev_member_since."</span>";
	$header .= "</div>";
	$header .= "</div>";
	
	return $header;
}

function getProfileHeaderWidget($profile) {
	$header = "<div class=\"odesk_header_widget\">";
	$header .= "<div class=\"img\"><img src=\"".$profile->profile->dev_portrait."\" /></div>";
	$header .= "<div class=\"header_profilename_widget\"><strong><a target=\"_blank\" href=\"https://www.odesk.com/users/".get_option("odesk_profile_key")."?wsrc=tile_2&wlnk=btn&_scr=hireme_l\">".$profile->profile->dev_short_name."</a></strong></h1><br />";
	$header .= "<span>".$profile->profile->dev_profile_title."</span><br />";	
	$header .= "<span>Hourly rate: $<strong>".$profile->profile->dev_bill_rate."/hr</strong></span><br />";
	$header .= "<span>Member: ".$profile->profile->dev_member_since."</span>";
	$header .= "</div>";
	$header .= "</div>";
	
	return $header;
}

function getOdeskProfile($key) {
	$url = "http://www.odesk.com/api/profiles/v1/providers/".$key."?wsrc=tile_2/profile.xml";
	//$url = getURL()."profile.xml";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$data = curl_exec($ch);
	
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
	curl_close($ch);
	
	if ($status >= 200 && $status < 300) {	
		$doc = new SimpleXmlElement($data, LIBXML_NOCDATA);
		//print_r($doc);
		return $doc;
	}else{
		return false;
	}

}

function add_odesk_scripts() {
    wp_enqueue_script('odesk_tabber', getURL().'tabber-minimized.js');
}
function add_odesk_style() {
    wp_enqueue_style('odesk_tabber', getURL().'tabber.css');
}
function getURL() {
	return WP_CONTENT_URL.'/plugins/'.basename(dirname(__FILE__)) . '/';
}


function getProfileWidget() {
	$profile = getOdeskProfile(get_option("odesk_profile_key"));
	print_r(getProfileHeaderWidget($profile));
}


/**
 * Odesk PRofile Widget, will be displayed on post page
 */
class OdeskProfileWidget extends WP_Widget {
	function OdeskProfileWidget() {
		parent::WP_Widget(false, $name = 'Odesk Profile Widget');
	}

	function widget($args, $instance) {
		//if ( is_single() && !is_page() ) { // display on post page only
			extract( $args );
			$title = apply_filters('widget_title', $instance['title']);
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			getProfileWidget();
			print_r($profileWidget);
			echo $after_widget;
		//}
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function form($instance) {
		$title = esc_attr($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		<?php
	}

} // class OdeskProfileWidget

add_action( 'widgets_init', create_function( '', 'return register_widget("OdeskProfileWidget");' ) );

?>