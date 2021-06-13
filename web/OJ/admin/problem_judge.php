<?php require_once("../include/db_info.inc.php");
if (!HAS_PRI("rejudge")){
	echo "0";
	exit(1);
}
function update_points($sid, $judge_result, $type){
	global $mysqli;
	global $OJ_points_enable;//是否开启积分功能，true开启，false关闭
	global $OJ_points_submit;//提交一次代码扣xx个积分
	/*--以下三个参数只用于http分布式判题和管理员web端手动判题，本地判题机判题的相关参数设置在judge.conf中--*/
	global $OJ_points_AC;//提交的代码正确奖励xx个积分
	global $OJ_points_firstAC;//提交的代码第一次AC题目奖励xx个积分
	global $OJ_points_Wrong;//提交的代码错误扣除xx积分
	if (isset($OJ_points_enable) && $OJ_points_enable && $judge_result >=4){
		if(!isset($OJ_points_AC)) $OJ_points_AC=1;
		if(!isset($OJ_points_firstAC)) $OJ_points_firstAC=1;
		if(!isset($OJ_points_Wrong)) $OJ_points_Wrong=0;
		$sql="SELECT `lastresult`,`problem_id`,`user_id`,`contest_id`,`num` FROM `solution` WHERE `solution_id`=$sid";
		$result=$mysqli->query($sql);
		if($row=$result->fetch_object()){
			$points_pay=0;
			$lastresult=$row->lastresult;
			$problem_id=$row->problem_id;
			$user_id=$row->user_id;
			if($row->contest_id){
				require_once("../include/const.inc.php");
				$contest_id=$row->contest_id;
				$cpid=PID($row->num);
			} else $contest_id = 0;
			$sql="SELECT user_id FROM `users` WHERE user_id= '$user_id'";
			$result=$mysqli->query($sql);
			if ($row=$result->fetch_object()){//不存在的普通账号不更新积分（可能是比赛账号，也可能是删除的账号）
				$sql="SELECT count(`user_id`) as ac FROM `solution` WHERE `problem_id`=$problem_id AND `result`=4 AND `user_id`= '$user_id'";
				$result=$mysqli->query($sql);
				if($row=$result->fetch_object()){
					$AC_already=$row->ac;
				} else $AC_already=0;
				//计算积分 pay_type  0 提交代码扣积分，1 加奖励积分， 2 扣惩罚扣分， 3 其他人工处理加减积分
				$operator = $mysqli->real_escape_string($_SESSION['user_id']);
				if ($lastresult==0){//新提交代码
					if($judge_result!=4){ //新交代码是错的
						$points_pay = -$OJ_points_Wrong;
						//插入积分日志
						if($points_pay!=0){
							if($type=="manual"){
								$msg="人工判题，代码错误";
							} else {
								$msg="代码错误";
							}
							if($contest_id > 0){
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-{$contest_id}_{$cpid}</a>";
							} else {
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-$problem_id</a>";
							}
							if($type=="manual"){
								$sql="INSERT INTO `points_log`(`item`,`operator`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog', '$operator', '$user_id', $sid, 2, $points_pay, NOW())";
							} else {
								$sql="INSERT INTO `points_log`(`item`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog', '$user_id', $sid, 2, $points_pay, NOW())";
							}
							$mysqli->query($sql);
						}
					} else { //新交代码是对的
						if($AC_already==0){
							$points_pay = $OJ_points_firstAC;
						} else {
							$points_pay = $OJ_points_AC;
						}
						//插入积分日志
						if($points_pay!=0){
							if($type=="manual"){
								$msg="人工判题，代码正确";
							} else {
								$msg="代码正确";
							}
							if($contest_id > 0){
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-{$contest_id}_{$cpid}</a>";
							} else {
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-$problem_id</a>";
							}
							if($type=="manual"){
								$sql="INSERT INTO `points_log`(`item`,`operator`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog', '$operator', '$user_id', $sid, 1, $points_pay, NOW())";
							} else {
								$sql="INSERT INTO `points_log`(`item`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog', '$user_id', $sid, 1, $points_pay, NOW())";
							}
							$mysqli->query($sql);
						}
					}
				} else if($lastresult!=0) {//老代码重判
					if($lastresult==4 && $judge_result!=4){
						$points_pay = -$OJ_points_Wrong;//扣惩罚积分
						//插入积分日志
						if($points_pay!=0){
							if($type=="manual"){
								$msg="人工重判，代码错误";
							} else {
								$msg="重判，代码错误";
							}
							if($contest_id > 0){
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-{$contest_id}_{$cpid}</a>";
							} else {
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-$problem_id</a>";
							}
							if($type=="manual"){
								$sql="INSERT INTO `points_log`(`item`,`operator`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog','$operator', '$user_id', $sid, 2, $points_pay, NOW())";
							} else {
								$sql="INSERT INTO `points_log`(`item`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog', '$user_id', $sid, 2, $points_pay, NOW())";
							}
							$mysqli->query($sql);
						}
						//扣回加分
						$sql="SELECT pay_points,`index` FROM `points_log` WHERE `solution_id`=$sid AND `pay_type`=1 AND `pay_points`>0";
						$result=$mysqli->query($sql);
						if($row=$result->fetch_object()){
							$points_pay -= $row->pay_points;
							$sql="DELETE FROM `points_log` WHERE `index`=$row->index";
							$mysqli->query($sql);
						}
					} else if($lastresult!=4 && $judge_result==4){
						if($AC_already==0){//加奖励积分
							$points_pay = $OJ_points_firstAC;
						} else {
							$points_pay = $OJ_points_AC;
						}
						//插入积分日志
						if($points_pay!=0){
							if($type=="manual"){
								$msg="人工重判，代码正确";
							} else {
								$msg="重判，代码正确";
							}
							if($contest_id > 0){
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-{$contest_id}_{$cpid}</a>";
							} else {
								$plog="<a href=\"showsource.php?id=$sid\" target=\"_blank\">$msg-$problem_id</a>";
							}
							if($type=="manual"){
								$sql="INSERT INTO `points_log`(`item`,`operator`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog', '$operator', '$user_id', $sid, 1, $points_pay, NOW())";
							} else {
								$sql="INSERT INTO `points_log`(`item`,`user_id`,`solution_id`,`pay_type`,`pay_points`,`pay_time` ) VALUES('$plog', '$user_id', $sid, 1, $points_pay, NOW())";
							}
							$mysqli->query($sql);
						}
						//加回扣分
						$sql="SELECT pay_points,`index` FROM `points_log` WHERE `solution_id`=$sid AND `pay_type`=2 AND `pay_points`<0";
						$result=$mysqli->query($sql);
						if($row=$result->fetch_object()){
							$points_pay -= $row->pay_points;
							$sql="DELETE FROM `points_log` WHERE `index`=$row->index";
							$mysqli->query($sql);
						}
					}
				}
				if($points_pay != 0){//结算积分
					$sql="UPDATE `users` SET `points`=`points`+$points_pay WHERE `user_id`='$user_id'";
					$mysqli->query($sql);
				}
			}
		}
	}
}
if(isset($_POST['manual'])){

        $sid=intval($_POST['sid']);
        $result=intval($_POST['result']);
		update_points($sid, $result, "manual");
        if($result>=0){
		  if($result==4){
          	$sql="UPDATE solution SET result=$result, lastresult=$result WHERE solution_id=$sid LIMIT 1";
		  } else if($result>4){
			$sql="UPDATE solution SET result=$result, lastresult=$result, `pass_rate`=0 WHERE solution_id=$sid LIMIT 1";
		  } else {
			$sql="UPDATE solution SET result=$result, `pass_rate`=0 WHERE solution_id=$sid LIMIT 1";
		  }
          $mysqli->query($sql);
        }
        if(isset($_POST['explain'])){
             $sql="DELETE FROM runtimeinfo WHERE solution_id=$sid ";
             $mysqli->query($sql);
             $reinfo=$mysqli->real_escape_string($_POST['explain']);
             if (get_magic_quotes_gpc ()) {
                 $reinfo= stripslashes ( $reinfo);
             }
             $sql="INSERT INTO runtimeinfo VALUES($sid,'$reinfo')";
             $mysqli->query($sql);
        }
        echo "<script>history.go(-1);</script>";
}

if(isset($_POST['update_solution'])){
	//require_once("../include/check_post_key.php");
	$sid=intval($_POST['sid']);
	$result=intval($_POST['result']);
	$time=intval($_POST['time']);
	$memory=intval($_POST['memory']);
	$sim=intval($_POST['sim']);
	$simid=intval($_POST['simid']);
	$pass_rate=floatval($_POST['pass_rate']);
	update_points($sid, $result, "update_solution");
	$judger=$mysqli->real_escape_string($_SESSION['user_id']);
	if ( $result >= 4){
		$sql="UPDATE `solution` SET `result`=$result,`time`=$time,`memory`=$memory,`judgetime`=NOW(),`pass_rate`=$pass_rate,`judger`='$judger',`lastresult`=$result WHERE `solution_id`=$sid LIMIT 1";
		$mysqli->query($sql);
	} else {
		$sql="UPDATE `solution` SET `result`=$result,`time`=$time,`memory`=$memory,`judgetime`=NOW(),`pass_rate`=$pass_rate,`judger`='$judger' WHERE solution_id=$sid LIMIT 1";
		$mysqli->query($sql);
	}	
    if ($sim) {
		$sql="insert into sim(s_id,sim_s_id,sim) values($sid,$simid,$sim) on duplicate key update  sim_s_id=$simid,sim=$sim";
		$mysqli->query($sql);
	}
	
}else if(isset($_POST['checkout'])){
	
	$sid=intval($_POST['sid']);
	$result=intval($_POST['result']);
	$sql="UPDATE solution SET result=$result,time=0,memory=0,judgetime=NOW() WHERE solution_id=$sid and (result<2 or (result<4 and NOW()-judgetime>60)) LIMIT 1";
	$mysqli->query($sql);
	if($mysqli->affected_rows>0)
		echo "1";
	else
		echo "0";
}else if(isset($_POST['getpending'])){
	$max_running=intval($_POST['max_running']);
	$oj_lang_set=$mysqli->real_escape_string($_POST['oj_lang_set']);
	$sql="SELECT solution_id FROM solution WHERE language in ($oj_lang_set) and (result<2 or (result<4 and NOW()-judgetime>60)) ORDER BY result ASC,solution_id ASC limit $max_running";
	$result=$mysqli->query($sql);
	while ($row=$result->fetch_object()){
		echo $row->solution_id."\n";
	}
	$result->free();
	
}else if(isset($_POST['getsolutioninfo'])){
	
	$sid=intval($_POST['sid']);
	$sql="select problem_id, user_id, language from solution WHERE solution_id=$sid ";
	$result=$mysqli->query($sql);
	if ($row=$result->fetch_object()){
		echo $row->problem_id."\n";
		echo $row->user_id."\n";
		echo $row->language."\n";
		
	}
	$result->free();
	
}else if(isset($_POST['getsolution'])){
	
	$sid=intval($_POST['sid']);
	$sql="SELECT source FROM source_code WHERE solution_id=$sid ";
	$result=$mysqli->query($sql);
	if ($row=$result->fetch_object()){
		echo $row->source."\n";
	}
	$result->free();
	
}else if(isset($_POST['getcustominput'])){
	
	$sid=intval($_POST['sid']);
	$sql="SELECT input_text FROM custominput WHERE solution_id=$sid ";
	$result=$mysqli->query($sql);
	if ($row=$result->fetch_object()){
		echo $row->input_text."\n";
	}
	$result->free();
	
}else if(isset($_POST['getprobleminfo'])){
	
	$pid=intval($_POST['pid']);
	$sql="SELECT time_limit,memory_limit,spj FROM problem where problem_id=$pid ";
	$result=$mysqli->query($sql);
	if ($row=$result->fetch_object()){
		echo $row->time_limit."\n";
		echo $row->memory_limit."\n";
		echo $row->spj."\n";
		
	}
	$result->free();
	
}else if(isset($_POST['addceinfo'])){
	
	$sid=intval($_POST['sid']);
	$sql="DELETE FROM compileinfo WHERE solution_id=$sid ";
	$mysqli->query($sql);
	$ceinfo=$mysqli->real_escape_string($_POST['ceinfo']);
	$sql="INSERT INTO compileinfo VALUES($sid,'$ceinfo')";
	$mysqli->query($sql);
	
}else if(isset($_POST['addreinfo'])){
	
	$sid=intval($_POST['sid']);
	$sql="DELETE FROM runtimeinfo WHERE solution_id=$sid ";
	$mysqli->query($sql);
	$reinfo=$mysqli->real_escape_string($_POST['reinfo']);
	$sql="INSERT INTO runtimeinfo VALUES($sid,'$reinfo')";
	$mysqli->query($sql);
	
}else if(isset($_POST['updateuser'])){
	
  	$user_id=$mysqli->real_escape_string($_POST['user_id']);
	$sql="UPDATE `users` SET `solved`=(SELECT count(DISTINCT `problem_id`) FROM `solution` WHERE `user_id`='$user_id' AND `result`=4) WHERE `user_id`='$user_id'";
	$mysqli->query($sql);
  //  echo $sql;
	
	$sql="UPDATE `users` SET `submit`=(SELECT count(*) FROM `solution` WHERE `user_id`='$user_id') WHERE `user_id`='$user_id'";
	$mysqli->query($sql);
  //	echo $sql;
	
}else if(isset($_POST['updateproblem'])){
	
	$pid=intval($_POST['pid']);
	$sql="UPDATE `problem` SET `accepted`=(SELECT count(1) FROM `solution` WHERE `problem_id`=$pid AND `result`=4) WHERE `problem_id`=$pid";
	//echo $sql;
	$mysqli->query($sql);
	
	$sql="UPDATE `problem` SET `submit`=(SELECT count(1) FROM `solution` WHERE `problem_id`=$pid) WHERE `problem_id`=$pid";
	//echo $sql;
	$mysqli->query($sql);
	$cid=intval($_POST['cid']);
	if($cid>0){
		$sql="UPDATE `contest_problem` SET `c_accepted`=(SELECT count(1) FROM `solution` WHERE `problem_id`=$pid and contest_id=$cid AND `result`=4) WHERE `problem_id`=$pid and contest_id=$cid";
		$mysqli->query($sql);
		$sql="UPDATE `contest_problem` SET `c_submit`=(SELECT count(1) FROM `solution` WHERE `problem_id`=$pid and contest_id=$cid) WHERE `problem_id`=$pid and contest_id=$cid";
		$mysqli->query($sql);
	}

  //动态计算题目分值 start
  // get user numbers
  $sql = "SELECT count(*) as num FROM users WHERE solved>10";
  $result = $mysqli->query($sql) or die($mysqli->error);
  $row = $result->fetch_object();
  $user_cnt = $row->num?$row->num:1;

  // get AC user numbers
  $sql = "SELECT count(DISTINCT user_id) AS num FROM solution WHERE result=4 AND problem_id='$pid'";
  $result = $mysqli->query($sql) or die($mysqli->error);
  $row = $result->fetch_object();
  $solved_user = $row->num;

  // get submit user numbers
  $sql = "SELECT count(DISTINCT user_id) AS num FROM solution WHERE problem_id='$pid'";
  $result = $mysqli->query($sql) or die($mysqli->error);
  $row = $result->fetch_object();
  $submit_user = $row->num;
  $result->free();
  // calculate scores
  $scores = 100.0 * (1 - ($solved_user + $submit_user / 2.0) / $user_cnt);
  if ($scores < 10) $scores = 10;
  $sql = "UPDATE problem SET solved_user=".$solved_user.", submit_user=".$submit_user.",score=" . $scores . " WHERE problem_id='$pid'";
  $mysqli->query($sql);
  //动态计算题目分值 end
	
}else if(isset($_POST['checklogin'])){
	echo "1";
}else if(isset($_POST['gettestdatalist'])){


	$pid=intval($_POST['pid']);
      
  	if($OJ_SAE){
          //echo $OJ_DATA."$pid";
         
           $store = new SaeStorage();
           $ret = $store->getList("data", "$pid" ,100,0);
            foreach($ret as $file) {
              if(!strstr($file,"sae-dir-tag")){
                     $file=pathinfo($file);
                     $file=$file['basename'];
                    		 echo $file."\n";   
              }
                    
            }


        } else{
        
            $dir=opendir($OJ_DATA."/$pid");
            while (($file = readdir($dir)) != "")
            {
              if(!is_dir($file)){
               	    $file=pathinfo($file);
                    $file=$file['basename'];
                    echo "$file\n";
              }
            }
            closedir($dir);
        }
        
	
}else if(isset($_POST['gettestdata'])){
	$file=$_POST['filename'];
        if($OJ_SAE){ 
		$store = new SaeStorage();
                if($store->fileExists("data",$file)){
                       
                		echo $store->read("data",$file);
                }
                
        }else{
          	echo file_get_contents($OJ_DATA.'/'.$file);
        }
           
}
else if(isset($_POST['gettestdatadate'])){
	$file=$_POST['filename'];
        
		
    echo filemtime($OJ_DATA.'/'.$file);
        
           
}else{
?>
<form action='problem_judge.php' method=post>
<input type="text" name="sid" value="">
<select length="2" name="result">
	<option value="0">等待 </option>
	<option value="1">等待重判 </option>
	<option value="2">编译中 </option>
	<option value="3">运行并评判 </option>
	<option value="4">正确 </option>
	<option value="5">格式错误 </option>
	<option value="6">答案错误 </option>
	<option value="7">时间超限 </option>
	<option value="8">内存超限 </option>
	<option value="9">输出超限 </option>
</select>
<input name="manual" type="hidden">
<input type="submit" name="manual" value="确定"></form>
<form action='problem_judge.php' method=post>
	<b>HTTP Judge:</b><br />
	sid:<input type=text size=10 name="sid" value=1244><br />
	pid:<input type=text size=10 name="pid" value=1000><br />
	result:<input type=text size=10 name="result" value=4><br />
	time:<input type=text size=10 name="time" value=500><br />
	memory:<input type=text size=10 name="memory" value=1024><br />
	sim:<input type=text size=10 name="sim" value=100><br />
	simid:<input type=text size=10 name="simid" value=0><br />
  	gettestdata:<input type=text size=10 name="filename" value="1000/test.in"><br />
	
        <input type='hidden' name='gettestdatalist' value='do'>
	<input type=submit value='Judge'>
</form>
<?php 
}
?>
