<?php
require_once '.config.php';

set_time_limit(60000);

function get_call($url, $method='GET', $params=''){
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL 						=> $url,
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_ENCODING				=> "",
		CURLOPT_MAXREDIRS 			=> 10,
		CURLOPT_TIMEOUT					=> 30,
		CURLOPT_HTTP_VERSION		=> CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST 	=> $method,
		CURLOPT_POSTFIELDS			=> $params,
		CURLOPT_HTTPHEADER			=> array(
			"authorization: Basic ".AUTH,
			"content-type: application/json"
		),
	));
	$response	= json_decode(curl_exec($curl), TRUE);
	$err			= curl_error($curl);
	curl_close($curl);
	if ($err!=''){
		return $err;
	} else {
		return $response;
	}
}
function get_status($name, $project_id){
	include '.config.php';
	if (in_array($project_id, $legacy)){
		$return	=	'Legacy';
	} else {
		$name = strtolower($name);
		if (strpos($name, 'mvp') !== false){
			$return	=	'MVP';
		} elseif (strpos($name, 'design') !== false){
			$return	=	'Design Sprint';
		} else {
			$return = 'Sprints';
		}
	}
	return $return;
}

$project_columns = '';
$project_dots = '';
$total_u_dots	=	0;
$total_p_dots	=	array();
$total_d_dots	=	0;
$users				= array();
$proyects 		= array();
$s_design			=	0;
$s_mvp				=	0;
$s_sprint			=	0;
$s_legacy			=	0;
$s_noinfo			=	0;
$datatable		=	'';
$j_projects		=	'';
$j_assignee		=	'';
$f_ontime			=	'';
$f_atrisk			=	'';
$f_late				=	'';
$f_hold				=	'';
$f_noinfo			=	'';

//Get projects
$response = get_call(API_URL."project?expand=lead");
foreach ($response as $proyect) {
	if (in_array($proyect[projectCategory][name], $categories)) {
		$proyects[$proyect[id]] = $proyect[lead][displayName];
		$project_columns				.="data.addColumn('string', '".$proyect[name]."');";
		$j_projects							.=$proyect[id].',';
	}
}
$j_projects									=	substr($j_projects, 0, -1);

//Get teammates
$all_asignees = get_call(API_URL.'user/assignable/search?project=DADG&startAt=6');
foreach ($all_asignees as $asignee) {
	foreach ($proyects as $id => $lead) {
		$users[$asignee[key]][$id]	=	0;
	}
	$j_assignee						.=$asignee[key].',';
}
$j_assignee							=	substr($j_assignee, 0, -1);

//Get issues
$all_issues	=	get_call(API_URL.'search', 'POST', "{\"jql\": \"assignee in ($j_assignee) AND project in ($j_projects) AND duedate > startOfMonth() AND duedate < endOfMonth()\"}");
foreach ($all_issues[issues] as $issue) {
	$asignee_key	=	$issue[fields][assignee][key];
	$project_id		=	$issue[fields][project][id];
	if ($issue[fields][customfield_10016] > 0){
		$points			=	$issue[fields][customfield_10016];
	} else {
		$points			=	0;
	}
	$users[$asignee_key][$project_id]	=	$points + $users[$asignee_key][$project_id];
}

//Assign points
foreach ($users as $user_key => $user_data) {
	$total_points		=	0;
	$total_dots			=	0;
	$project_dots		.="['$user_key', '{{dots}}', '{{total}}',";
	foreach ($proyects as $id => $lead) {
		$project_dots	.=" '$user_data[$id]',";
		$total_p_dots[$id]	=	$total_p_dots[$id] + $user_data[$id];
		$total_points	=	$total_points	+	$user_data[$id];
		if ($user_data[$id] > 0){
			$total_dots++;
		}
	}
	$project_dots		=	str_replace('{{dots}}', $total_dots, $project_dots);
	$project_dots		=	str_replace('{{total}}', $total_points, $project_dots);
	$project_dots		=	substr($project_dots, 0, -1);
	$project_dots		.='],';
	$total_u_dots		=	$total_u_dots	+	$total_points;
	$total_d_dots		=	$total_d_dots	+	$total_dots;
}
$project_dots			.="['Todos', '$total_d_dots', '$total_u_dots', ";
foreach ($total_p_dots as $id => $total) {
	$project_dots		.=" '$total',";
}
$project_dots		=	substr($project_dots, 0, -1);
$project_dots			.=']';

//Get boards
$all_boards = get_call(AGILE_URL."board");
foreach ($all_boards[values] as $board) {
	$project_id		= $board[location][projectId];
	$project_name	= explode('(', $board[location][name]);
	$project_name = $project_name[0];
	$project_lead	=	$proyects[$project_id];
	//Get Sprint
	$all_sprints = get_call(AGILE_URL."board/".$board[id]."/sprint");
	$future_sprint	=	'';
	$active_sprint	=	'';
	foreach ($all_sprints[values] as $sprint) {
		if ($sprint[state] == 'future'){
			$future_sprint	= get_status($sprint[name], $project_id);
			$future_date		=	$sprint[endDate];
		} elseif ($sprint[state] == 'closed'){
			$closed_date		=	$sprint[endDate];
		} elseif ($sprint[state] == 'active'){
			$active_sprint	= get_status($sprint[name], $project_id);
			$active_date		=	$sprint[endDate];
		}
	}
	$sprint_status		=	'';
	if ($active_sprint == ''){
		$sprint_status	=	$future_sprint;
		$deadline				=	$future_date;
	} else {
		$sprint_status	=	$active_sprint;
		$deadline				= $active_date;
	}
	if ($sprint_status	==	''){
		$sprint_status	=	'No info';
		$s_noinfo++;
	}
	switch ($sprint_status){
		case 'Design Sprint':
			$s_design++;
			break;
		case 'MVP':
			$s_mvp++;
			break;
		case 'Sprints':
			$s_sprint++;
			break;
		case 'Legacy':
			$s_legacy++;
			break;
	}
	if (in_array($project_id, $on_hold)){
		$deadline				=	'HOLD';
		$f_hold++;
	} elseif ($deadline == '') {
		$deadline				=	'No info';
		if ($closed_date != '') {
			$deadline				=	'Closed';
			$f_hold++;
		} else {
			$f_noinfo++;
		}
	} else {
		$deadline				=	substr($deadline, 0, 10);
		if ($deadline >= date(Y-m-d)){
			$f_ontime++;
		} else {
			$f_late++;
		}
	}

	$boards[$board[id]] = array();
	$boards[$board[id]][project_id] = $project_id;
	$boards[$board[id]][project_name] = $project_name;
	$boards[$board[id]][project_lead] = $project_lead;
	$boards[$board[id]][sprint_status] = $sprint_status;
	$boards[$board[id]][deadline] = $deadline;

	$datatable	.=	"['$project_name', '$sprint_status', '$deadline', '$project_lead'],";
}
$datatable		=	substr($datatable, 0, -1);
?>