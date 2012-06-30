<?php
/*
Plugin Name: Odesk Profile Fetcher
Plugin URI: http://reygcalantaol.com/odesk-profile-fetcher
Description: This plugin uses Odesk API to display odesk profile in your website.
Version: 0.11
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
*/


if (isset($_POST['profileKey'])) {
	update_option('odesk_profile_key', $_POST['profileKey'], ' ', 'yes');
	update_option('odesk_profile_link', $_POST['footer_'], ' ', 'yes');
	update_option('odesk_profile_proxies', $_POST['txtProxy'], ' ', 'yes');
	update_option('odesk_profile_redirect', $_POST['txtRedirect'], ' ', 'yes');
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
    <form action="<?php echo $_SERVER['file:///D|/repository/odesk-profile-fetcher/trunk/REQUEST_URI']; ?>&amp;updated=true" method="post">
        <table>
        <tr>
          <td>Odesk Profile ID:</td><td><input name="profileKey" type="text" value="<?php echo get_option('odesk_profile_key'); ?>" style="width:350px;"/></td></tr>
        <tr>
          <td valign="top"><p>Proxies:</p></td>
          <td><span style="font-size:11px;">
            <textarea name="txtProxy" id="txtProxy" rows="4" style="width:350px;"><?php echo get_option("odesk_profile_proxies"); ?></textarea>
          <br />
          Separated by comma e.g 190.121.135.178:8080</span></td>
        </tr>
        <tr>
          <td valign="top">Recirect URL:</td>
          <td><input name="txtRedirect" type="text" id="txtRedirect" style="width:350px;" value="<?php echo get_option('odesk_profile_redirect'); ?>"/><br />
          <span style="font-size:11px;">Url to show if odesk fails to load.</span></td>
        </tr>
        <tr><td></td><td><input name="footer_" type="checkbox" value="1" <?php if (get_option("odesk_profile_link") != '') { echo "checked=\"checked\"";} ?>/> Display developer link at the footer?</td></tr>
       <tr><td></td><td><input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></td></tr>
       
        </table>
    </form>
    </div>
<?php
}

add_shortcode('odesk_profile', 'odesk_profile_generator');

function odesk_profile_generator() {
	$proxy = "";
	if (trim(get_option("odesk_profile_proxies")) != '') {
		$proxies = explode(",",get_option("odesk_profile_proxies"));
		$rand = rand(0,count($proxies)-1);
		$proxy = trim($proxies[$rand]);
	}
	$url = "http://www.odesk.com/api/profiles/v1/providers/".trim(get_option("odesk_profile_key")).".xml";
	$profile = odesk_curl_data($url, $proxy);
	$output = "";
	
	if (!$profile) {
		$output = "Invalid profile key!";
		if (trim(get_option("odesk_profile_redirect")) != "") {
			$output .= "<script type='text/javascript'>location.href='".get_option("odesk_profile_redirect")."'</script>";
		}
		//return $output;
	}else{
		$output .= getProfileHeader($profile->profile);
		$output .= "<div class=\"clear_both\"></div>"; //begin tabber
		$output .= "<div class=\"tabber\">"; //begin tabber
		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2> Overview </h2>"; //Title
		$output .= getOverview($profile->profile); //Content
     	$output .= "</div>"; //End Tab
		
		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Resume</h2>"; //Title
	  	$output .= getSkills($profile->profile->skills->skill); //Content
		$output .= getCertification($profile->profile->certification->certificate); //Content
		$output .= getEmployment($profile->profile->experiences->experience); //Content
		$output .= getOther($profile->profile->experiences->oth_experience); //Content
		$output .= getEducation($profile->profile->education->institution); //Content		
     	$output .= "</div>"; //End Tab
		
		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Work History and Feedback (".$profile->profile->assignments_count.")</h2>"; //Title
		$output .= getWorkHistory($profile->profile->assignments->hr->job);
		$output .= getWorkHistoryFP($profile->profile->assignments->fp->job);		
		$output .= "</div>"; //End Tab

		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Tests (".$profile->profile->tsexams_count.")</h2>"; //Title
	  	$output .= getTest($profile->profile->tsexams->tsexam); //Content
     	$output .= "</div>"; //End Tab
		
		$output .= "<div class=\"tabbertab\">"; //First tab
	  	$output .= "<h2>Portfolio (".$profile->profile->dev_portfolio_items_count.")</h2>"; //Title
	  	$output .= getPortfolio($profile->profile->portfolio_items->portfolio_item); //Content
     	$output .= "</div>"; //End Tab		
		
		$output .= "</div>";	//End tabber	
		$output .= "<div style=\"text-align:right; font-size:10px;\">";
		if (get_option("odesk_profile_link") != '') {
		$output .= "<i><a href=\"http://reygcalantaol.com/odesk-profile-fetcher\">Odesk Profile Fetcher Plugin</a> by <a href=\"http://reygcalantaol.com\">Rey G. Calanta-ol</a></i>";
		}else{
		$output .= "<i>Odesk  Profile  Fetcher  Plugin   by  Rey G. Calanta-ol</i>";
		}
		
		$output .= "</div";	//Footer			
	}
	return $output;
}


function odesk_curl_data($url, $proxy) {
	$referer = "http://www.odesk.com";
	$agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if (trim($proxy) != '') {
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
	}
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
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

function getOverview($p) {
	//print_r($p);
	$ready = ($p->dev_is_ready == 1) ? 'Yes' : 'No';
	$overview .= "<div class=\"odesk_overview\">";	
	$overview .= "<span>".$p->dev_blurb."</span><br /><br />";
	$overview .= "<div class=\"odesk_overview_list\"><ul>";
	$overview .= "<li><span class=\"caption\">Total Hours: </span><span class=\"value\">".$p->dev_total_hours."</span></li>";
	$overview .= "<li><span class=\"caption\">Total Contracts: </span><span class=\"value\">".$p->dev_billed_assignments."</span></li>";
	$overview .= "<li><span class=\"caption\">Location: </span><span class=\"value\">".$p->dev_location."</span></li>";
	$overview .= "<li><span class=\"caption\">English Skills: </span><span class=\"value\">".$p->dev_eng_skill."</span></li>";
	$overview .= "<li><span class=\"caption\">Member Since: </span><span class=\"value\">".$p->dev_member_since."</span></li>";
	$overview .= "<li><span class=\"caption\">Last Worked: </span><span class=\"value\">".$p->dev_last_worked."</span></li>";
	$overview .= "<li><span class=\"caption\">Odesk Ready: </span><span class=\"value\">".$ready."</span></li>";
	$overview .= "</ul></div>";
	$overview .= "<div class=\"clear_both\"></div>";
	$overview .= "</div>";
	
	return $overview;
}

function getTest($profile) {
	
	$work = "<div>";
	$work .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
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
	
	foreach ($profile as $hr) {

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


function getPortfolio($folio) {
	
	$portfolio = "<div>";
	$portfolio .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
	$portfolio .= "<tr>";
	$portfolio .= "<td colspan=2><span><strong>Portfolio</strong></span></td>";
	$portfolio .= "</tr>";	
	$portfolio .= "<tbody>";
	
	foreach ($folio as $hr) {
		$date = (int)$hr->pi_completed;
		$portfolio .= "<tr>";
		$portfolio .= "<td valign=top><img src=\"".$hr->pi_thumbnail."\" /></td>";
		$portfolio .= "<td><ul>";
		$portfolio .= "<li><strong>Project Title:</strong> ".$hr->pi_title."</li>";
		$portfolio .= "<li><strong>Completed:</strong> ".date('M d, Y',$date)."</li>";
		$portfolio .= "<li><strong>Category:</strong> ".$hr->pi_category->pi_category_level1.">".$hr->pi_category->pi_category_level2."</li>";
		$portfolio .= "<li><strong>URL:</strong> <a href=\"".$hr->pi_url."\" rel=\"nofollow\" target=\"_blank\">".$hr->pi_url."</a></li>";
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
	
	foreach ($profile as $hr) {
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
	
	foreach ($profile as $hr) {
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

function getSkills($s) {
	
	$skills = "<div>";
	$skills .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
	$skills .= "<tr>";
	$skills .= "<td colspan=3><span><strong>Skills</strong></span></td>";
	$skills .= "</tr>";	
	$skills .= "<tr>";
	$skills .= "<th width=15%>Skill</th>";
	$skills .= "<th width=15%>Level</th>";
	$skills .= "<th width=70%>Description</th>";
	$skills .= "</tr>";
	$skills .= "<tbody>";
	
	foreach ($s as $skill) {
		
	$skills .= "<tr>";
	$skills .= "<td>".$skill->skl_name."</td>";
	$skills .= "<td>".$skill->skl_level."</td>";
	$skills .= "<td>".$skill->skl_description."</td>";	
	$skills .= "</tr>";
	
	}
	
	$skills .= "</tbody>";
	$skills .= "</table>";		
	
	return $skills;
	
}

function getCertification($cer) {
	
	$certificate .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
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
	
	foreach ($cer as $c) {
		
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
	
	$employ .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
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
	
	foreach ($profile as $c) {
		
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


function getOther($o) {
	
	$other .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
	$other .= "<tr>";
	$other .= "<td colspan=2><span><strong>Other Experience</strong></span></td>";
	$other .= "</tr>";	
	$other .= "<tbody>";
	
	foreach ($o as $c) {
		
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
	
	$education .= "<table class=\"odesk_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
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
	
	foreach ($profile as $c) {
		
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
	$header .= "<div class=\"img\"><img src=\"".$profile->dev_portrait."\" /><br />";
	$header .= "<a target=\"_blank\" href=\"https://www.odesk.com/users/".get_option("odesk_profile_key")."?wsrc=tile_2&wlnk=btn&_scr=hireme_l\">";
	$header .= "<img src=\"".getURL().'th_hire_me_button.jpg'."\" border=0 width=100 /></a></div>";
	$header .= "<div class=\"header_profilename\"><strong><a target=\"_blank\" href=\"https://www.odesk.com/users/".get_option("odesk_profile_key")."?wsrc=tile_2&wlnk=btn&_scr=hireme_l\">".$profile->dev_short_name."</a></strong></h1>";
	$header .= "<div class=\"border_line\"></div>";
	$header .= "<span>".$profile->profile_title_full."</span><br />";
	$header .= "<span>Current hourly rate: $<strong>".$profile->dev_bill_rate."/hr</strong></span><br />";
	$header .= "<span>Member since ".$profile->dev_member_since."</span>";
	$header .= "</div>";
	$header .= "</div>";
	
	return $header;
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

?>