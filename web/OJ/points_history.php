<?php
/**
 * This file is created
 * by lixun516@qq.com
 * @2021.06.07
 **/
?>
<?php
////////////////////////////Common head
    $cache_time=10;
    $OJ_CACHE_SHARE=false;
    require_once('./include/cache_start.php');
    require_once('./include/db_info.inc.php');
    require_once('./include/setlang.php');
    require_once('./include/const.inc.php');
    require_once('./include/my_func.inc.php');
    if (!(isset($OJ_points_enable) && $OJ_points_enable)){
        $view_errors="<font color='red'>You don't have the privilege to view this page!</font>";
        require("template/".$OJ_TEMPLATE."/error.php");
        exit(0);
    }
    $user=trim($mysqli->real_escape_string($_GET['user']));
    if ($user!=$_SESSION['user_id'] && !IS_ADMIN($_SESSION['user_id'])){
        $view_errors="<font color='red'>You don't have the privilege to view this page!</font>";
        require("template/".$OJ_TEMPLATE."/error.php");
        exit(0);
    }
    if (isset($_SESSION['contest_id'])){ //不允许比赛用户查看普通用户信息
        $view_errors= "<font color='red'>$MSG_HELP_TeamAccount_forbid</font>";
        require("template/".$OJ_TEMPLATE."/error.php");
        exit(0);
    }
    // check user
    
    if (!is_valid_user_name($user)){
        $view_errors= "No such User!";
        require("template/".$OJ_TEMPLATE."/error.php");
        exit(0);
    }
    $sql="SELECT `nick`,`points` FROM `users` WHERE `user_id`='$user'";
    $result=$mysqli->query($sql);
    $row_cnt=$result->num_rows;
    if ($row_cnt==0){ 
      $view_errors= "No such User!";
      require("template/".$OJ_TEMPLATE."/error.php");
      exit(0);
    }
    $row=$result->fetch_object();
    $nick=$row->nick;
    $points=$row->points;
	$sql = "SELECT SUM(`pay_points`) a FROM `points_log` WHERE `user_id`='$user'";
	$result=$mysqli->query($sql);
    $InitialPoints = round($points - $result->fetch_object()->a, 2);
    $nowPoints = round($points, 2);
    if(isset($_GET['page'])) {
        $page = intval($_GET['page']);
    } else $page = 1;
    $page_cnt = 30;
    $sql="SELECT count(`index`) a FROM `points_log` WHERE `user_id`='$user'";
    $result = $mysqli->query($sql);
    $total = $result->fetch_object()->a;
    $view_total_page = ceil($total / $page_cnt); //计算页数
    $view_total_page = $view_total_page>0?$view_total_page:1;
    if ($page > $view_total_page) {
        $page = $view_total_page;
    } else if ($page < 1) $page = 1;
    $pstart = $rank = $page_cnt*$page-$page_cnt;
    $pend = $page_cnt;   
    $view_logs = array();
    $cnt = 0;
	$sql = "SELECT * FROM `points_log` WHERE `user_id`='$user' ORDER BY `pay_time` DESC";
    $sql .= " limit ".strval($pstart).",".strval($pend);
    $result=$mysqli->query($sql);
    while($row=$result->fetch_object()){
        $rank++;
        $view_logs[$cnt][0]= $rank;
        if($row->solution_id>0){
            $view_logs[$cnt][1]= $row->solution_id;
        } else $view_logs[$cnt][1] = "&nbsp";
        $view_logs[$cnt][2]= $row->pay_time;
        $view_logs[$cnt][3]= $row->item;
        if($row->operator==""){
            $view_logs[$cnt][4] = "System";
        } else $view_logs[$cnt][4]= $row->operator;
        if($row->pay_points>=0) {
            $view_logs[$cnt][5] = round($row->pay_points, 2);
            $view_logs[$cnt][6] = "&nbsp";
        } else {
            $view_logs[$cnt][5] = "&nbsp";
            $view_logs[$cnt][6] = round($row->pay_points, 2);
        }
        $view_logs[$cnt][7] = round($points, 2)." <span class='am-icon-apple'></span>";
        $points-=$row->pay_points;
        $cnt++;
    }
///////////////////////////MAIN
    
	
/////////////////////////Template
require("template/".$OJ_TEMPLATE."/points_history.php");
/////////////////////////Common foot
if(file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>
