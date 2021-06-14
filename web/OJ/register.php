<?php
  /**
   * This file is modified
   * by yybird
   * @2016.05.12
  **/
?>

<?php
  if(isset($_GET['code'])){
	  require_once("./include/db_info.inc.php");
    $code=$mysqli->real_escape_string(trim($_GET['code']));
    $sql="SELECT `user_id`, `activateCode` FROM `users` WHERE `activateCode`='$code' AND `defunct`='Y' AND `activateTimelimit`>=NOW()";
    $result = $mysqli->query($sql);
    $msg ="";
    if($row = $result->fetch_object()){
			if($row->activateCode==$code){
        $sql="UPDATE `users` SET `defunct`='N', `activateTimelimit`=NOW() WHERE `activateCode`='$code' AND `user_id`='{$row->user_id}'";
        $mysqli->query($sql);
        if ($mysqli->affected_rows>0) {
          $msg ="succ";
        }
      }
    }
    echo "<script language='javascript'>\n";
    if($msg=="succ"){
      echo "alert('激活成功，点击确定登录账号。');\n";
    } else {
      echo "alert('抱歉，此账户激活链接已经失效。可能你的账户已经被激活或者超过激活期限了？');\n";
    }
    $_SESSION['gotoIndex'] = true;
    echo "window.location.href='loginpage.php';";
    echo "</script>";
    exit(0);
  }
  require_once("include/check_post_key.php");
  require_once("./include/db_info.inc.php");
  require_once('./include/setlang.php');
  require_once("./include/my_func.inc.php");
	if (isset($OJ_REGISTER) && !$OJ_REGISTER) {
		echo "<script language='javascript'>\n";
		echo "alert('System do not allow register');\n history.go(-1);\n</script>";
		exit(0);
	}  
  $class="其它";
  $stu_id="";
  $real_name="";
  //验证注册码
  if (isset($OJ_REG_NEED_CONFIRM) && ($OJ_REG_NEED_CONFIRM=="pwd" || $OJ_REG_NEED_CONFIRM=="pwd+confirm" || $OJ_REG_NEED_CONFIRM=="pwd+email")) {
    $err_str = "";
    $err_str_prefix = "";
    if (isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE) {
      $class = $mysqli->real_escape_string(trim($_POST['class']));
      $err_str_prefix = "{$MSG_Class}【{$class}】的";
    }
    $regcode = trim($_POST['regcode']);
    if ($regcode == ""){
      $err_str="请输入{$MSG_REG_CODE}！";
    } else {
      $reg_code = get_class_regcode($class);
      if (!$reg_code) {
        $err_str="{$err_str_prefix}{$MSG_REG_CODE}不正确，请联系管理员！"; //数据库中没有对应班级的注册码记录
      } else if ($regcode != $reg_code->reg_code) {
        $err_str="{$err_str_prefix}{$MSG_REG_CODE}不正确，请联系管理员！";
      } else if ($reg_code->remain_num == 0) {
        $err_str="{$err_str_prefix}注册名额已用完，注册通道关闭，请联系管理员！";
      }
    }   
    if ($err_str != ""){
      echo "<script language='javascript'>\n";
      echo "alert('$err_str');\n history.go(-1);\n</script>";
      exit(0);
    }
  }
  // OJ 用户名合法性判断
  $err_str="";
  $err_cnt=0;
  $len;
  $user_id=$mysqli->real_escape_string(trim($_POST['user_id']));
  $nick=$mysqli->real_escape_string(trim($_POST['nick']));
  $email=$mysqli->real_escape_string(trim($_POST['email']));
  $school=$mysqli->real_escape_string(trim($_POST['school']));
  if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){	  
    $class=$mysqli->real_escape_string(trim($_POST['class']));
    $stu_id=$mysqli->real_escape_string(trim($_POST['stu_id']));
    $real_name=$mysqli->real_escape_string(trim($_POST['real_name']));
  }
  $vcode=$mysqli->real_escape_string(trim($_POST['vcode']));
  // echo $user_id.$email.$vcode."<br>";
  // echo $_SESSION["vcode"];
  if($OJ_VCODE&&($vcode!= $_SESSION["vcode"]||$vcode==""||$vcode==null)) { // 验证码错误 
    $_SESSION["vcode"]=null;
    $err_str=$err_str."验证码错误！\\n";//$err_str=$err_str."Verification Code Wrong!\\n";
    $err_cnt++;
  }
  if($OJ_LOGIN_MOD!="hustoj"){
    $err_str=$err_str."System do not allow register.\\n";
    $err_cnt++;
  }
  $len=strlen($user_id);
  if($len<3 || $len>20){
    //$err_str=$err_str."User ID Too Long!\\n";
	  //$err_str=$err_str."User ID Too Short!\\n";
	  $err_str=$err_str."用户名长度要求3-20个字符！\\n";
    $err_cnt++;
  }
  if (!is_valid_user_name($user_id)){
    $err_str=$err_str."用户名只能包含英文字母和数字！\\n"; //$err_str=$err_str."User ID can only contain NUMBERs & LETTERs!\\n";	
    $err_cnt++;
  }
  $len = strlen($_POST['password']);
  if ($len<6 || $len>22){
    $err_str=$err_str."密码位数要求6-22位！\\n";
	$err_cnt++;    
  } else if (strcmp($_POST['password'],$_POST['rptpassword'])!=0){    
	  $err_str=$err_str."两次输入的密码不一致！\\n";//$err_str=$err_str."Password Not Same!\\n";
    $err_cnt++;
  }
  $len=strlen($nick);
  if ($len==0){
    $nick=$user_id;
  } else if(!preg_match("/^[\u{4e00}-\u{9fa5}_a-zA-Z0-9]{1,60}$/", $nick) || mb_strlen($nick, 'utf-8')>20) {
    $err_str=$err_str."输入的{$MSG_NICK}限20个以内的汉字、字母、数字或下划线 ！\\n";
    $err_cnt++;
  } 
  if(!preg_match("/^[\u{4e00}-\u{9fa5}a-zA-Z0-9]{0,60}$/", $school) || mb_strlen($school, 'utf-8')>20) { //
    $err_str=$err_str."输入的{$MSG_SCHOOL}限20个以内的汉字、字母或数字 ！\\n";
    $err_cnt++;
  }
  if(isset($OJ_NEED_CLASSMODE)&&$OJ_NEED_CLASSMODE){
    if(!preg_match("/^[a-zA-Z0-9]{0,20}$/", $stu_id)) {
      $err_str=$err_str."输入的{$MSG_StudentID}要求为20位以内的字母+数字或者纯数字的学号！\\n";
      $err_cnt++;
    }
    if(!preg_match("/^[\u{4e00}-\u{9fa5}a-zA-Z]{0,60}$/", $real_name)) {
      $err_str=$err_str."输入的{$MSG_REAL_NAME}要求为20字以内的中文或英文姓名！\\n";
      $err_cnt++;
    }
    if(!class_is_exist($class)){
      $err_str=$err_str."{$MSG_Class}【{$class}】不存在！\\n";
      $err_cnt++;
    }
  }
  $len=strlen($_POST['email']);
  if ($len>100){
    $err_str=$err_str."输入的电子邮箱地址过长！\\n";//$err_str=$err_str."Email Too Long!\\n";
    $err_cnt++;
  } else if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $email)) {
	  $err_str=$err_str."输入的电子邮箱地址不合法！\\n";//$err_str=$err_str."Email Illegal!\\n";
    $err_cnt++;
  } else {
    $sql="SELECT count(`email`) e FROM `users` WHERE `email`='$email'";
    $result = $mysqli->query($sql);
    if($result->fetch_object()->e > 0){
      $err_str=$err_str.$email."已被注册！\\n";
      $err_cnt++;
    }
  }
  if ($err_cnt>0){
    print "<script language='javascript'>\n";
    print "alert('";
    print $err_str;
    print "');\n history.go(-1);\n</script>";
    exit(0);
  }

  // 检查用户是否存在
  $password=pwGen($_POST['password']);
  $sql="SELECT `user_id` FROM `users` WHERE `users`.`user_id` = '".$user_id."'";
  $result=$mysqli->query($sql);
  $rows_cnt=$result->num_rows;
  $result->free();
  if ($rows_cnt == 1){
    print "<script language='javascript'>\n";
    print "alert('用户名已存在，请更换！');\n";//print "alert('User Existed!\\n');\n";
    print "history.go(-1);\n</script>";
    exit(0);
  }
  // 在OJ上注册
  $nick=(htmlentities ($nick,ENT_QUOTES,"UTF-8"));
  $school=(htmlentities ($school,ENT_QUOTES,"UTF-8"));
  $email=(htmlentities ($email,ENT_QUOTES,"UTF-8"));
  $ip=$_SERVER['REMOTE_ADDR'];
  if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
    $REMOTE_ADDR = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $tmp_ip=explode(',',$REMOTE_ADDR);
    $ip =(htmlentities($tmp_ip[0],ENT_QUOTES,"UTF-8"));
  }
  if(isset($OJ_REG_NEED_CONFIRM) && ($OJ_REG_NEED_CONFIRM=="on" || $OJ_REG_NEED_CONFIRM=="pwd+confirm" || $OJ_REG_NEED_CONFIRM=="pwd+email")) {
	  $defunct="Y";
  } else {
	  $defunct="N";
  }
  $give_points=0;
  if (isset($OJ_points_enable) && $OJ_points_enable) {
    $sql="SELECT `give_points` FROM `class_list` WHERE `class_name`='$class'";
    $result = $mysqli->query($sql);
    if($row = $result->fetch_object()) $give_points=$row->give_points;
  }
  $sql="INSERT INTO `users`("
  ."`user_id`,`email`,`defunct`,`ip`,`password`,`reg_time`,`nick`,`school`,`class`, `stu_id`, `real_name`, `points`)"
  ."VALUES('".$user_id."','".$email."','".$defunct."','".$_SERVER['REMOTE_ADDR']."','".$password."',NOW(),'".$nick."','".$school."','".$class."','".$stu_id."','".$real_name."', $give_points)";

  $mysqli->query($sql) or die ($mysqli->error);
  if($give_points>0) {
    $sql="INSERT INTO `points_log`(`item`,`user_id`, `pay_type`, `pay_points`,`pay_time` ) ";
    $sql.="VALUES('账号注册$MSG_InitialPoints','$user_id',3,$give_points, NOW())";//插入积分日志
    $mysqli->query($sql);
  }
  $msg = "账号注册成功！";
  $sql="INSERT INTO `loginlog`(`user_id`,`password`,`ip`,`time`) VALUES('$user_id','register','$ip',NOW())";
  $mysqli->query($sql);
  if(isset($OJ_REG_NEED_CONFIRM) && ($OJ_REG_NEED_CONFIRM=="on" || $OJ_REG_NEED_CONFIRM=="pwd+confirm")){
    $msg = $msg."\\n请联系管理员审核通过！";
  } else if(isset($OJ_REG_NEED_CONFIRM) && $OJ_REG_NEED_CONFIRM=="pwd+email"){
    $activateCode = createPwd("", 30, false);
    $sql="UPDATE `users` SET `activateCode`='$activateCode',`activateTimelimit`=DATE_ADD(now(), Interval 1 day) WHERE `user_id`='$user_id'";
    $mysqli->query($sql);
    //******************** 发送激活邮件 ********************************
    require("plugins/PHPMailer/PHPMailerAutoload.php");
    require_once("plugins/PHPMailer/class.phpmailer.php");
    require_once("plugins/PHPMailer/class.smtp.php");
    $mailcontent = "<p>欢迎来到".$OJ_NAME."！</p>";
    $mailcontent .= "<p>点击下面链接确认并激活你的新账户：<br>";
    $URL="http://".$_SERVER['HTTP_HOST'];
    if($_SERVER["SERVER_PORT"]!="80"){
      $URL.=":".$_SERVER["SERVER_PORT"];
    }
    $URL.="/register.php?code=".$activateCode;
    $mailcontent .="<a href='$URL' target='_blank' style='text-decoration: none; font-weight: bold; color: #006699;' rel='noopener' > $URL </a></p>";
    $mailcontent .= "<p>如果以上链接无法点击，请将它复制并粘贴到你的浏览器的地址栏。</p>";
    $mailcontent .= "<p>注意：请您在收到邮件24小时内使用，否则该链接将会失效。</p>";//邮件内容
    $mail = new PHPMailer();
    //$mail->SMTPDebug = 2;
    $mail->CharSet = "UTF-8";        //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8
    $mail->IsSMTP();                 // 设定使用SMTP服务
    $mail->SMTPAuth = true;          // 启用 SMTP 验证功能
    $mail->Host = $SMTP_SERVER;      // SMTP 服务器
    $mail->Port = $SMTP_SERVER_PORT; // SMTP服务器的端口号
    if($mail->Port!=25) {
      $mail->SMTPSecure = "ssl";     // 非25端口就启用SSL
    }
    $mail->Username   = $SMTP_USER;  // SMTP服务器用户名
    $mail->Password   = $SMTP_PASS;  // SMTP服务器密码
    $mail->SetFrom($mail->Username, $OJ_NAME);    // 设置发件人地址和名称
    $mail->AddReplyTo($mail->Username,$OJ_NAME);  // 设置邮件回复人地址和名称
    $mail->Subject = $OJ_NAME." 确认你的新账号--系统邮件请勿回复";    // 设置邮件标题
    $mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端"; // 可选项，向下兼容考虑
    $mail->MsgHTML($mailcontent);                 // 设置邮件内容
    $mail->AddAddress($email);//收件人
    if(!$mail->Send()) {
      $msg .= "\\n邮件发送失败，请联系管理员处理！";// . $mail->ErrorInfo;
    } else {
      $msg .= "\\n请于24小时内登录邮箱{$email}查看邮件，确认账号并激活！";
    }
    //************************ 发送激活邮件 ****************************
  } else {    
	  $sql="UPDATE `users` SET `accesstime`=NOW() WHERE `user_id`='$user_id'";
    $mysqli->query($sql);
	  $_SESSION['user_id']=$user_id;
    $sql="SELECT `rightstr` FROM `privilege` WHERE `user_id`='".$_SESSION['user_id']."'";
    //echo $sql."<br />";
    $result=$mysqli->query($sql);
    echo $mysqli->error;
    while ($row=$result->fetch_array()){
      $_SESSION[$row['rightstr']]=true;
      //echo $_SESSION[$row['rightstr']]."<br />";
    }
    $_SESSION['ac']=Array();
    $_SESSION['sub']=Array();
  }
  if (isset($OJ_REG_NEED_CONFIRM) && ($OJ_REG_NEED_CONFIRM=="pwd" || $OJ_REG_NEED_CONFIRM=="pwd+confirm" || $OJ_REG_NEED_CONFIRM=="pwd+email")) {
    //注册完成，该班级的注册名额减1
    if ($reg_code->reg_code != -1) {// $reg_code->reg_code == -1 表示注册名额不限次数
      $sql = "UPDATE`reg_code` SET `remain_num`=`remain_num`-1 WHERE `class_name`='$reg_code->class_name' AND `remain_num`>'0'";
      $mysqli->query($sql);
    }
  }
?>

<script>
	alert("<?php echo $msg ?>");
  window.location.href='index.php';
</script>