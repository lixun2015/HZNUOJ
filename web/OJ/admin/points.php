<?php

/**
 * This file is created
 * by lixun516@qq.com
 * @2021.06.10
 **/
?>

<?php 
require_once("admin-header.php");
if (!HAS_PRI("inner_function")) {
	echo "Permission denied!";
	exit(1);
}
require_once("../include/my_func.inc.php");
//分页start
$page = 1;
$args = array();
if (isset($_GET['function'])) $args['function'] = intval($_GET['function']); else $args['function'] = 1;
if (isset($_GET['page'])) $page = intval($_GET['page']);
if (isset($_GET['defunct'])) $args['defunct'] = $_GET['defunct'];
if (isset($_GET['class'])) $args['class'] = urlencode($_GET['class']);
if (isset($_GET['user'])) $args['user'] = trim($mysqli->real_escape_string($_GET['user']));
if (isset($_GET['type'])) $args['type'] = intval($_GET['type']);
if (isset($_GET['sort_method'])) $args['sort_method'] = $_GET['sort_method'];
else $args['sort_method'] = "";
if (isset($_GET['keyword'])) {
    $_GET['keyword'] = trim($_GET['keyword']);
    $args['keyword'] = urlencode($_GET['keyword']);
}
$args['page'] = $page;
function generate_url($data, $link)
{
    global $args;
    if ($link == "") $link = "points.php?";
    else $link .= "?";
    foreach ($args as $key => $value) {
        if (isset($data["$key"])) {
            $value = htmlentities($data["$key"]);
            $link .= "&$key=$value";
        } else if ($value) {
            $link .= "&$key=" . htmlentities($value);
        }
    }
    return $link;
}
switch ($args['function']) {
	case 1: default://查看用户积分
		$sql_filter = " WHERE 1 ";
		if (isset($_GET['keyword']) && $_GET['keyword'] != "") {
			$keyword = $mysqli->real_escape_string($_GET['keyword']);
			$keyword = "'%$keyword%'";
			$sql_filter .= " AND (";
			$sql_filter .= " (user_id LIKE $keyword ) OR (nick LIKE $keyword ) ";
			if (isset($OJ_NEED_CLASSMODE) && $OJ_NEED_CLASSMODE) {
				$sql_filter .= " OR (`real_name` LIKE $keyword ) ";
				$sql_filter .= " OR (`class` LIKE $keyword ) ";
			}
			$sql_filter .= ") ";
		}
		if (isset($OJ_NEED_CLASSMODE) && $OJ_NEED_CLASSMODE && isset($_GET['class']) && $_GET['class'] != "all" && $_GET['class'] != "") {
			if($_GET['class']<>"empty"){
				$sql_filter .= " AND `class`='{$mysqli->real_escape_string($_GET['class'])}' ";
			} else $sql_filter .= " AND (ISNULL(`class`) OR `class`='') ";
		}
		if (isset($_GET['defunct']) && $_GET['defunct'] != "all") {
			if ($_GET['defunct'] == "N") {
				$sql_filter .= " AND `defunct`= 'N' ";
			} else $sql_filter .= " AND `defunct`= 'Y' ";
		}
		$sql = "SELECT COUNT('user_id') a FROM `users` " . $sql_filter;
		$result = $mysqli->query($sql);
		$total = $result->fetch_object()->a;
		if (isset($_GET['keyword']) && $_GET['keyword'] != "") $page_cnt = $total; else $page_cnt = 50;
		$view_total_page = ceil($total / $page_cnt); //计算页数
		$view_total_page = $view_total_page>0?$view_total_page:1;
		if ($page > $view_total_page) $args['page'] = $page = $view_total_page;
		if ($page < 1) $page = 1;
		$left_bound = $page_cnt * $page - $page_cnt;
		$u_id = $left_bound;
		switch ($args['sort_method']) {
			case 'AccTime_DESC':
				$acctime_icon = "am-icon-sort-amount-desc";
				$points_icon = "am-icon-sort";
				$InitPoints_icon = "am-icon-sort";
				$sql_filter .= " ORDER BY `accesstime` DESC,user_id ";
				$accTime = 'AccTime_ASC';
				$points = 'points_DESC';
				$InitPoints = 'InitPoints_DESC';
				break;
			case 'AccTime_ASC':
				$acctime_icon = "am-icon-sort-amount-asc";
				$points_icon = "am-icon-sort";
				$InitPoints_icon = "am-icon-sort";
				$sql_filter .= " ORDER BY `accesstime`,user_id ";
				$accTime = 'AccTime_DESC';
				$points = 'points_DESC';
				$InitPoints = 'InitPoints_DESC';
				break;
			case 'points_DESC':
            default:
				$acctime_icon = "am-icon-sort";
				$points_icon = "am-icon-sort-amount-desc";
				$InitPoints_icon = 'am-icon-sort';
				$sql_filter .= " ORDER BY `points` DESC,user_id ";
				$accTime = 'AccTime_DESC';
				$points = 'points_ASC';
				$InitPoints = 'InitPoints_DESC';
				break;
			case 'points_ASC':
				$acctime_icon = "am-icon-sort";
				$points_icon = "am-icon-sort-amount-asc";
				$InitPoints_icon = 'am-icon-sort';
				$sql_filter .= " ORDER BY `points`,user_id ";
				$accTime = 'AccTime_DESC';
				$points = 'points_DESC';
				$InitPoints = 'InitPoints_DESC';
				break;
			case 'InitPoints_DESC':
				$acctime_icon = "am-icon-sort";
				$points_icon = "am-icon-sort";
				$InitPoints_icon = 'am-icon-sort-amount-desc';
				$sql_filter .= " ORDER BY `points0` DESC,user_id ";
				$accTime = 'AccTime_DESC';
				$points = 'points_DESC';
				$InitPoints = 'InitPoints_ASC';
				break;
			case 'InitPoints_ASC':
				$acctime_icon = "am-icon-sort";
				$points_icon = "am-icon-sort";
				$InitPoints_icon = 'am-icon-sort-amount-asc';
				$sql_filter .= " ORDER BY `points0`,user_id ";
				$accTime = 'AccTime_DESC';
				$points = 'points_DESC';
				$InitPoints = 'InitPoints_DESC';
				break;
		}
		$sql_filter .= " LIMIT $left_bound, $page_cnt";
		$view_items = array();
		$cnt = 0;
		$sql = "SELECT `user_id`,`nick`,`defunct`,`accesstime`,`class`,`real_name`,`points`,(`points`-(SELECT SUM(`pay_points`) a FROM `points_log` WHERE `user_id`=users.`user_id`)) points0 FROM `users` ";
        $sql .= $sql_filter;
		$result = $mysqli->query($sql);
		while ($row = $result->fetch_object()) {
			$view_items[$cnt][0] = ++$u_id;
			$view_items[$cnt][1] = "<a href='points.php?function=2&user=" . $row->user_id . "'>" . $row->user_id . "</a>";
			$view_items[$cnt][2] = mb_strlen($row->nick, 'utf-8')<=6 ? "<div style='width:80px;white-space: nowrap;'>$row->nick</div>" : "<div title='$row->nick' style='width:80px;white-space: nowrap;text-overflow:ellipsis; overflow:hidden;'>". mb_substr($row->nick,0,6,'utf-8')."...</div>";
			$view_items[$cnt][3] = round($row->points,2);
            $view_items[$cnt][4] = round($row->points0,2);
			if (isset($OJ_NEED_CLASSMODE) && $OJ_NEED_CLASSMODE) {
				$view_items[$cnt][5] = $row->real_name;
				$view_items[$cnt][6] = $row->class;
			}
			if ($row->defunct == "N") {
				$view_items[$cnt][7] = "<span class='btn btn-primary' disabled>$MSG_Available</span>";
			} else {
				$view_items[$cnt][7] = "<span class='btn btn-danger' disabled>$MSG_Reserved</span>";
			}
			$view_items[$cnt][8] = $row->accesstime;
			$cnt++;
		}
		break;
	case 2://查看积分日志
		require_once("../include/set_get_key.php");
		if(!isset($OJ_points_submit)) $OJ_points_submit=1;
        if(!isset($OJ_points_AC)) $OJ_points_AC=1;
        if(!isset($OJ_points_firstAC)) $OJ_points_firstAC=1;
        if(!isset($OJ_points_Wrong)) $OJ_points_Wrong=0;
		$sql_filter = " WHERE 1 ";
		if (isset($_GET['user']) && $_GET['user'] != ""){
			$sql_filter .= " AND p.`user_id`='{$args['user']}'";
		}
		if (isset($_GET['keyword']) && $_GET['keyword'] != "") {
			$keyword = $mysqli->real_escape_string($_GET['keyword']);
			$keyword = "'%$keyword%'";
			$sql_filter .= " AND (";
			$sql_filter .= " (p.`user_id` LIKE $keyword ) OR (`item` LIKE $keyword ) ";
			$sql_filter .= " OR (`solution_id` LIKE $keyword ) ";
			$sql_filter .= ") ";
		}
		if (isset($_GET['type']) && $args['type']>=0){
			$sql_filter .= " AND `pay_type`=".$args['type'];
		}
		$sql = "SELECT COUNT('index') a FROM `points_log` as p" . $sql_filter;
		$result = $mysqli->query($sql);
		$total = $result->fetch_object()->a;
		if (isset($_GET['keyword']) && $_GET['keyword'] != "") $page_cnt = $total; else $page_cnt = 50;
		$view_total_page = ceil($total / $page_cnt); //计算页数
		$view_total_page = $view_total_page>0?$view_total_page:1;
		if ($page > $view_total_page) $args['page'] = $page = $view_total_page;
		if ($page < 1) $page = 1;
		$left_bound = $page_cnt * $page - $page_cnt;
		$p_id = $left_bound;
		switch ($args['sort_method']) {
			case 'subtime_DESC': default:
				$subtime_icon = "am-icon-sort-amount-desc";
				$runid_icon = "am-icon-sort";
				$userid_icon = "am-icon-sort";
				$sql_filter .= " ORDER BY `pay_time` DESC,`user_id` ";
				$subtime = 'subtime_ASC';
				$runid = 'runid_DESC';
				$userid = 'userid_DESC';
				break;
			case 'subtime_ASC':
				$subtime_icon = "am-icon-sort-amount-asc";
				$runid_icon = "am-icon-sort";
				$userid_icon = "am-icon-sort";
				$sql_filter .= " ORDER BY `pay_time`,`user_id` ";
				$subtime = 'subtime_DESC';
				$runid = 'runid_DESC';
				$userid = 'userid_DESC';
				break;
			case 'runid_DESC':
				$subtime_icon = "am-icon-sort";
				$runid_icon = "am-icon-sort-amount-desc";
				$userid_icon = 'am-icon-sort';
				$sql_filter .= " ORDER BY `solution_id` DESC";
				$subtime = 'subtime_DESC';
				$runid = 'runid_ASC';
				$userid = 'userid_DESC';
				break;
			case 'runid_ASC':
				$subtime_icon = "am-icon-sort";
				$runid_icon = "am-icon-sort-amount-asc";
				$userid_icon = 'am-icon-sort';
				$sql_filter .= " ORDER BY `solution_id`";
				$subtime = 'subtime_DESC';
				$runid = 'runid_DESC';
				$userid = 'userid_DESC';
				break;
            case 'userid_DESC':
                $subtime_icon = "am-icon-sort";
                $runid_icon = "am-icon-sort";
                $userid_icon = 'am-icon-sort-amount-desc';
                $sql_filter .= " ORDER BY p.`user_id` DESC, `pay_time` DESC";
                $subtime = 'subtime_DESC';
                $runid = 'runid_DESC';
                $userid = 'userid_ASC';
                break;
            case 'userid_ASC':
                $subtime_icon = "am-icon-sort";
                $runid_icon = "am-icon-sort";
                $userid_icon = 'am-icon-sort-amount-asc';
                $sql_filter .= " ORDER BY p.`user_id`, `pay_time` DESC ";
                $subtime = 'subtime_DESC';
                $runid = 'runid_DESC';
                $userid = 'userid_DESC';
                break;
		}
		$sql_filter .= " LIMIT $left_bound, $page_cnt";
		$view_logs = array();
		$cnt = 0;
		$sql = "SELECT p.*,u.`points` FROM `points_log` as p";
		$sql .= " LEFT JOIN `users` as u ON p.`user_id`= u.`user_id`";
		$sql .= $sql_filter;
		$result=$mysqli->query($sql);
		while ($row = $result->fetch_object()) {
			if($row->pay_type==3){
				$view_logs[$cnt][0] = "<input type='checkbox' name='index[]' value='$row->index' />&nbsp;" . ++$p_id;
			} else $view_logs[$cnt][0] = "&nbsp;&nbsp;" . ++$p_id;
			
			if($row->solution_id>0){
				$view_logs[$cnt][1]= $row->solution_id;
			} else $view_logs[$cnt][1] = "&nbsp";
			$view_logs[$cnt][2]= "<a href='points.php?function=2&user=" . $row->user_id . "'>" . $row->user_id . "</a>";
			$view_logs[$cnt][3]= str_replace('href="','href="../',$row->item);
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
			$view_logs[$cnt][7] = $row->pay_time;
			if($row->pay_type==3){
				if(is_null($row->points)){
					$view_logs[$cnt][8] = "<a class='btn btn-success' href='points.php?function=3&getkey=". $_SESSION['getkey'] ."&undo=$row->index' title='账号不存在，点击【{$MSG_DEL}】按钮直接删除日志记录'>$MSG_DEL</a>";
				} else {
					$view_logs[$cnt][8] = "<a class='btn btn-primary' href='#' onclick=\"javascript:if(confirm(' $MSG_Undo ?')) {location.href='points.php?function=3&getkey=". $_SESSION['getkey'] ."&undo=$row->index';} \" title='点击【{$MSG_Undo}】按钮，{$MSG_Undo}本次积分操作，恢复原来积分' >$MSG_Undo</a>";
				}
			} else {
				$view_logs[$cnt][8] = "&nbsp;";
			}
			$cnt++;
		}
		break;
	case 3://积分操作
		
		function undo($index){
			global $mysqli;
			$sql = "SELECT `pay_points`,`pay_type`,u.`user_id` FROM `points_log` p LEFT JOIN `users` u ON p.`user_id`=u.`user_id` WHERE `index`=$index";
			$result = $mysqli->query($sql);
			$res = 0;
			if($row = $result->fetch_object()){
				if($row->pay_type==3){ //人工处理的积分才能删除或者撤销
					if(is_null($row->user_id)){ //账号不存在，直接删除日志
						$sql="DELETE FROM `points_log` WHERE `index`=$index";
						$mysqli->query($sql);
						$res = $mysqli->affected_rows;
					} else { //账号存在，执行撤销
						$sql="UPDATE `users` SET `points`=`points`-$row->pay_points WHERE `user_id`='$row->user_id'";
						$mysqli->query($sql);
						$res = $mysqli->affected_rows;
						if ($res>0) {
							$sql="DELETE FROM `points_log` WHERE `index`=$index;";
							$mysqli->query($sql);
						} 
					}
				}
			}
			if($res < 0 ) $res = 0;
			return $res;
		}
		function update_points($uid, $points, $msg){
			global $mysqli;
			global $OJ_points_enable;//是否开启积分功能，true开启，false关闭
			$res = 0;
			//if (isset($OJ_points_enable) && $OJ_points_enable){
				$sql="SELECT user_id FROM `users` WHERE user_id= '$uid'";
				$result=$mysqli->query($sql);
				if ($row=$result->fetch_object()){//不存在的普通账号不更新积分（可能是比赛账号，也可能是删除的账号）
					$operator = $mysqli->real_escape_string($_SESSION['user_id']);
					if($points != 0){//结算积分
						$sql="UPDATE `users` SET `points`=`points`+$points WHERE `user_id`='$uid'";
						$mysqli->query($sql);
						$res = $mysqli->affected_rows;
						if ($res>0) {
							$sql="INSERT INTO `points_log`(`item`,`operator`,`user_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$msg', '$operator', '$uid', 3, $points, NOW())";
							$mysqli->query($sql);
						}	
					}
				}
			//}
			if($res < 0 ) $res = 0;
			return $res;
		}
		$res = 0;
		$undo = array();
		if (isset($_GET['undo'])&& trim($_GET['undo'])!=""){
			require_once("../include/check_get_key.php");
			$undo[0] = intval($_GET['undo']);
			$res += undo($undo[0]);
		} else if(isset($_POST['index'])){
			require_once("../include/check_get_key.php");
			foreach($_POST['index'] as $i){
				$res += undo(intval($i));
			}
		}		
		if ($res > 0){
			echo "<script language='javascript'>\n";
			echo "alert('$MSG_Undo."/".$MSG_DEL.$res 条$MSG_PointsHistory');\n";
			echo "history.go(-1);\n</script>";
			exit(0);
		} else if(isset($_POST['class']) || isset($_POST['uids'])){
			require_once("../include/check_post_key.php");
			$points=intval($_POST['points']);
			$msg=$mysqli->real_escape_string(trim($_POST['msg']));
			if($msg=="" || $points==0){
				echo 0;
				exit(0);
			}
			$res = 0;
			if(trim($_POST['uids'])!=""){
				$uids = str_replace(" ","",$_POST['uids']);
				$uids = str_replace("，",",",$uids);
				$uids = array_unique(explode(",",$uids));
				foreach($uids as $uid){
					$res += update_points($uid, $points, $msg);
				}
			} else {
				$sql="SELECT `user_id` FROM `users` WHERE `class` IN ('".implode("','",$_POST['class'])."') ";
				$result=$mysqli->query($sql);
				while ($row=$result->fetch_object()){
					$res += update_points($row->user_id, $points, $msg);
				}
			}
			echo "完成 $res 人次".$MSG_points.$MSG_Operations;
		}
		break;
}

?>
<title><?php echo $html_title . $MSG_points ?></title>
<h1><?php echo $MSG_USER . $MSG_points ?></h1>
<h4>
<?php
 if (isset($OJ_points_enable) && $OJ_points_enable){
	echo "当前积分功能已开启";
 } else {
	echo "当前积分功能未开启";
 }
?>
</h4>
<div class="am-avg-md-1" style="margin-top: 20px; margin-bottom: 20px;">
    <ul class="am-nav am-nav-tabs">
    <?php 
    switch ($args['function']) {
        case 1: default:
    ?>
        <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
        <li class="am-active"><a href="points.php?function=1"><?php echo $MSG_points.$MSG_LIST ?></a></li>
        <li><a href="points.php?function=2"><?php echo $MSG_PointsHistory ?></a></li>
        <li><a href="points.php?function=3"><?php echo $MSG_points.$MSG_Operations ?></a></li>
    <?php 
        break;
        case 2:
    ?>
         <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
        <li><a href="points.php?function=1"><?php echo $MSG_points.$MSG_LIST ?></a></li>
        <li class="am-active"><a href="points.php?function=2"><?php echo $MSG_PointsHistory ?></a></li>
        <li><a href="points.php?function=3"><?php echo $MSG_points.$MSG_Operations ?></a></li>
    <?php 
        break;
		case 3:
    ?>
         <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
        <li><a href="points.php?function=1"><?php echo $MSG_points.$MSG_LIST ?></a></li>
        <li><a href="points.php?function=2"><?php echo $MSG_PointsHistory ?></a></li>
        <li class="am-active"><a href="points.php?function=3"><?php echo $MSG_points.$MSG_Operations ?></a></li>
    <?php 
        break;
    }
    ?>
    </ul>
</div>
<!-- 查找 start -->
<div class='am-g' style="margin-left: 5px;">
    <form id="searchform" class="am-form am-form-inline">
		<input type="hidden" name="function" value="<?php echo $args['function'] ?>">
	<?php 
    switch ($args['function']) {
        case 1: default:
    ?>
            <div class='am-form-group'>
            <select class="selectpicker show-tick" id='defunct' name='defunct' data-width="auto" onchange='javascript:document.getElementById("searchform").submit();'>
                <option value='all' <?php if (isset($_GET['defunct']) && ($_GET['defunct'] == "" || $_GET['defunct'] == "all")) echo "selected"; ?>> <?php echo $MSG_ALL.$MSG_STATUS ?></option>
                <option value='N' <?php if (isset($_GET['defunct']) && $_GET['defunct'] == "N" ) echo "selected"; ?>> <?php echo $MSG_Available?></option>
                <option value='Y' <?php if (isset($_GET['defunct']) && $_GET['defunct'] == "Y" ) echo "selected"; ?>> <?php echo $MSG_Reserved ?></option>
            </select>
        </div>
        <?php if (isset($OJ_NEED_CLASSMODE) && $OJ_NEED_CLASSMODE) {?>
            <div class='am-form-group'>
                <select class="selectpicker show-tick" data-live-search="true" id='class' name='class' data-width="auto" onchange='javascript:document.getElementById("searchform").submit();'>
                    <option value='all' <?php if (isset($_GET['class']) && ($_GET['class'] == "" || $_GET['class'] == "all")) echo "selected"; ?>> <?php echo $MSG_ALL.$MSG_Class ?></option>
                    <option value='其它' <?php if (isset($_GET['class']) && $_GET['class'] == "其它") echo "selected"; ?>>其它</option>
                    <option value='empty' <?php if (isset($_GET['class']) && $_GET['class'] == "empty") echo "selected"; ?>>无归属班级</option>                    
                    <?php
                    $sql = "SELECT DISTINCT `class` FROM `users` WHERE NOT ISNULL(`class`) AND `class`<>'' AND `class`<>'其它' ORDER BY `class`";
                    $result = $mysqli->query($sql);
                    $prefix = $result->fetch_all();
                    $result->free();
                    foreach ($prefix as $row) {
                        echo "<option value='" . $row[0] . "' ";
                        if (isset($_GET['class']) && $_GET['class'] == $row[0])  echo "selected";
                        echo ">" . $row[0] . "</option>";
                    }
                    ?>
                </select>
            </div>
        <?php } 
			break;
        case 2:
		?>
		     <div class='am-form-group'>
                <select class="selectpicker show-tick" data-live-search="true" id='type' name='type' data-width="auto" onchange='javascript:document.getElementById("searchform").submit();'>
                    <option value='-1'> <?php echo $MSG_ALL.$MSG_Type ?></option>
                    <option value='0' <?php if (isset($_GET['type']) && $_GET['type'] == "0") echo "selected"; ?>>代码提交</option>
                    <option value='1' <?php if (isset($_GET['type']) && $_GET['type'] == "1") echo "selected"; ?>>加分</option>
					<option value='2' <?php if (isset($_GET['type']) && $_GET['type'] == "2") echo "selected"; ?>>罚分</option>		
					<option value='3' <?php if (isset($_GET['type']) && $_GET['type'] == "3") echo "selected"; ?>>人工处理</option>
                </select>
            </div>
		   <div class="am-form-group">
				<input type="text" class="am-form-field" placeholder=" &nbsp;<?php echo $MSG_USER_ID ?>" name="user" value="<?php echo $args['user']?>">
		   </div>
		<?php
			break;
		case 3:
			break;
	}
	if($args['function']<3){
    ?>
        <div class="am-form-group am-form-icon">
            <i class="am-icon-search"></i>
            <input class="am-form-field" name="keyword" type="text" placeholder="<?php echo $MSG_KEYWORDS ?>" value="<?php echo $_GET['keyword'] ?>" />
        </div>
        <input class="btn btn-default" type="submit" value="<?php echo $MSG_SEARCH ?>">
	<?php } ?>
    </form>
</div>
<!-- 查找 end -->
<?php if($args['function']<3){ ?>
<!-- 页标签 start -->
<div class="am-g" style="margin-left: 5px;">
    <ul class="pagination text-center" style="margin-top: 10px;margin-bottom: 0px;">
        <?php $link = generate_url(Array("page"=>"1"), "")?>
        <li><a href="<?php echo $link ?>">Top</a></li>
        <?php $link = generate_url(array("page" => max($page - 1, 1)), "") ?>
        <li><a href="<?php echo $link ?>">&laquo; Prev</a></li>
        <?php
        $page_size=10;
        $page_start=max(ceil($page/$page_size-1)*$page_size+1,1);
        $page_end=min(ceil($page/$page_size-1)*$page_size+$page_size,$view_total_page);
        for ($i=$page_start;$i<$page;$i++){
            $link=generate_url(Array("page"=>"$i"), "");
            echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        $link=generate_url(Array("page"=>"$page"), "");
        echo "<li class='active'><a href=\"$link\">{$page}</a></li>";
        for ($i=$page+1;$i<=$page_end;$i++){
            $link=generate_url(Array("page"=>"$i"), "");
            echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        if ($i <= $view_total_page){
            $link=generate_url(Array("page"=>"$i"), "");
            echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        ?>
        <?php $link = generate_url(array("page" => min($page + 1, intval($view_total_page))), "") ?>
        <li><a href="<?php echo $link ?>">Next &raquo;</a></li>
    </ul>
</div>
<!-- 页标签 end -->
<?php 
}
switch ($args['function']) {
        case 1: default:
?>
<style type="text/css" media="screen">
    #points,#InitPoints,#acctime:hover {
        cursor: pointer;
    }
</style>
<div class="am-g am-scrollable-horizontal" style="max-width: 900px;margin-left: 5px;">
    <!-- 罗列用户积分情况 start -->
    <table class="table table-hover table-bordered table-condensed table-striped" style="white-space: nowrap;">
        <thead>
            <tr>
                <th width="10px"><?php echo $MSG_ID ?></th>
                <th><?php echo $MSG_USER_ID ?></th>
                <th><?php echo $MSG_NICK ?></th>
                <th id="points"><?php echo $MSG_points ?>&nbsp;<span class="<?php echo $points_icon ?>"></span></th>
                <th id="InitPoints"><?php echo $MSG_InitialPoints ?>&nbsp;<span class="<?php echo $InitPoints_icon ?>"></span></th>
                <?php if (isset($OJ_NEED_CLASSMODE) && $OJ_NEED_CLASSMODE) { ?>
                    <th><?php echo $MSG_REAL_NAME ?></th>
                    <th><?php echo $MSG_Class ?></th>
                <?php } ?>
                <th><?php echo $MSG_STATUS ?></th>
                <th id="acctime"><?php echo $MSG_AccessTime ?>&nbsp;<span class="<?php echo $acctime_icon ?>"></span></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($view_items as $row) {
                echo "<tr>\n";
                foreach ($row as $table_cell) {
                    echo "<td style='vertical-align:middle;'>";
                    echo $table_cell;
                    echo "</td>\n";
                }
                echo "</tr>\n";
            }
            ?>
        </tbody>
    </table>
    <!-- 罗列用户积分情况 end -->
</div>
<?php 
        break;
    case 2:
?>
<style type="text/css" media="screen">
    #runid,#userid,#subtime:hover {
        cursor: pointer;
    }
</style>
<div class="am-g am-scrollable-horizontal" style="max-width: 900px;margin-left: 5px;">
    <!-- 罗列积分日志 start -->
    <form action="points.php?function=3&getkey=<?php echo $_SESSION['getkey'] ?>" method="post">
        <table class="table table-hover table-bordered table-condensed table-striped" style="white-space: nowrap;">
            <thead>
				<tr>
					<td colspan="9">
					 <input type="submit" name="delete" class="btn btn-success" value="<?php echo $MSG_Undo."/".$MSG_DEL ?>" onclick='javascript:if(confirm("<?php echo $MSG_Undo."/".$MSG_DEL ?>?")) $("form").attr("action","points.php?function=3&getkey=<?php echo $_SESSION['getkey'] ?>");'>
					</td>
				</tr>
                <tr>
                    <th width="10px"><input type='checkbox' style='vertical-align:2px;' onchange='$("input[type=checkbox]").prop("checked", this.checked)'>&nbsp;<?php echo $MSG_ID ?></th>
					<th id="runid"><?php echo $MSG_RUNID ?>&nbsp;<span class="<?php echo $runid_icon ?>"></span></th>
					<th id="userid"><?php echo $MSG_USER_ID ?>&nbsp;<span class="<?php echo $userid_icon ?>"></span></th>
					<th><?php echo $MSG_Logs ?></th>
					<th><?php echo $MSG_Operator ?></th>
					<th><?php echo $MSG_Income ?></th>
					<th><?php echo $MSG_Expenditure ?></th>
					<th id="subtime"><?php echo $MSG_SUBMIT_TIME ?>&nbsp;<span class="<?php echo $subtime_icon ?>"></span></th>
                    <th><?php echo $MSG_Operations ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($view_logs as $row) {
                    echo "<tr>\n";
                    foreach ($row as $table_cell) {
                        echo "<td style='vertical-align:middle;'>";
                        echo $table_cell;
                        echo "</td>\n";
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
    </form>
    <!-- 罗列积分日志 end -->
</div>
<?php
	
        break;
	case 3:
?>
<div class="am-g am-scrollable-horizontal" style="max-width: 500px;margin-left: 5px;">
<form class="am-form am-form-horizontal" action="points.php?function=3" method="post">
 <input type='hidden' name='cid' value='<?php echo $cid ?>'>
 <?php require_once('../include/set_post_key.php'); ?>
	<div class="am-form-group" style="white-space: nowrap;">
      <label class="am-u-sm-2 am-u-sm-offset-2 am-form-label"><?php echo $MSG_USER_ID ?>:</label>
      <div class="am-u-sm-8">
        <input type="text" maxlength="200" style="width:220px;" name="uids" placeholder="user1,user2……"/>若账号不填，按所选班级批量操作
      </div>
    </div>
	<div class="am-form-group" style="white-space: nowrap;">
      <label class="am-u-sm-2 am-u-sm-offset-2 am-form-label"><?php echo $MSG_Class ?>:</label>
      <div class="am-u-sm-8">
        <select class="selectpicker show-tick" multiple data-live-search="true" name="class[]">
		<option value=''></option>
        <?php 
		  require_once('../include/classList.inc.php');
		  $classList = get_classlist(true, "");
          foreach ($classList as $c){
              if($c[0]) echo "<optgroup label='$c[0]级'>\n"; else echo "<optgroup label='无入学年份'>\n";
              foreach ($c[1] as $cl){
                echo "<option value='$cl'>$cl</option>\n";
              }
              echo "</optgroup>\n";
          }
        ?>
        </select>
      </div>
    </div>
	<div class="am-form-group" style="white-space: nowrap;">
      <label class="am-u-sm-2 am-u-sm-offset-2 am-form-label"><?php echo $MSG_points ?>:</label>
      <div class="am-u-sm-8">
        <input type="number" style="width:220px;" maxlength="20" name="points" placeholder="正数-加积分，负数-扣积分" min="-1000" max="1000" required />
      </div>
    </div>
	<div class="am-form-group" style="white-space: nowrap;">
      <label class="am-u-sm-2 am-u-sm-offset-2 am-form-label"><?php echo $MSG_Logs ?>:</label>
      <div class="am-u-sm-8">
        <input type="text" maxlength="60" style="width:220px;" name="msg" placeholder="操作事由" required />
      </div>
    </div>
	<div class="am-form-group">
      <div class="am-u-sm-8 am-u-sm-offset-4">
        <input type="submit" value="<?php echo $MSG_SUBMIT ?>" name="save" class="am-btn am-btn-success">
      </div>
    </div>
</form>
<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>
<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>
</div>
<?php
		break;
    }
 if($args['function']<3){ 
?>

<!-- 页标签 start -->
<div class="am-g" style="margin-left: 5px;">
    <ul class="pagination text-center" style="margin-top: 1px;margin-bottom: 0px;">
        <?php $link = generate_url(Array("page"=>"1"), "")?>
        <li><a href="<?php echo $link ?>">Top</a></li>
        <?php $link = generate_url(array("page" => max($page - 1, 1)), "") ?>
        <li><a href="<?php echo $link ?>">&laquo; Prev</a></li>
        <?php
        //分页
        for ($i=$page_start;$i<$page;$i++){
            $link=generate_url(Array("page"=>"$i"), "");
            echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        $link=generate_url(Array("page"=>"$page"), "");
        echo "<li class='active'><a href=\"$link\">{$page}</a></li>";
        for ($i=$page+1;$i<=$page_end;$i++){
            $link=generate_url(Array("page"=>"$i"), "");
            echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        if ($i <= $view_total_page){
            $link=generate_url(Array("page"=>"$i"), "");
            echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        ?>
        <?php $link = generate_url(array("page" => min($page + 1, intval($view_total_page))), "") ?>
        <li><a href="<?php echo $link ?>">Next &raquo;</a></li>
    </ul>
</div>
<!-- 页标签 end -->

<?php
 }
require_once("admin-footer.php");
switch ($args['function']) {
        case 1:
?>
<!-- sort by acctime、points BEGIN -->
<script>
    <?php $args['sort_method'] = $accTime; ?>
    $("#acctime").click(function() {
        var link = "<?php echo generate_url(array("page" => "1"), "") ?>";
        window.location.href = link;
    });
    <?php $args['sort_method'] = $points; ?>
    $("#points").click(function() {
        var link = "<?php echo generate_url(array("page" => "1"), "") ?>";
        window.location.href = link;
    });
	<?php $args['sort_method'] = $InitPoints; ?>
    $("#InitPoints").click(function() {
        var link = "<?php echo generate_url(array("page" => "1"), "") ?>";
        window.location.href = link;
    });
</script>
<!-- sort by acctime、points  END -->
<?php 
	break;
case 2:
?>
<!-- sort by BEGIN -->
<script>
    <?php $args['sort_method'] = $runid; ?>
    $("#runid").click(function() {
        var link = "<?php echo generate_url(array("page" => "1"), "") ?>";
        window.location.href = link;
    });
    <?php $args['sort_method'] = $userid; ?>
    $("#userid").click(function() {
        var link = "<?php echo generate_url(array("page" => "1"), "") ?>";
        window.location.href = link;
    });
	<?php $args['sort_method'] = $subtime; ?>
    $("#subtime").click(function() {
        var link = "<?php echo generate_url(array("page" => "1"), "") ?>";
        window.location.href = link;
    });
</script>
<!-- sort END -->
<?php 
	break;
}
?>