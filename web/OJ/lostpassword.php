<?php
/**
 * This file is modified
 * by yybird
 * @2016.06.02
 **/
?>

<?php
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
$view_title= "Welcome To Online Judge";
require_once("./include/const.inc.php");
require_once("./include/my_func.inc.php");
if (isset($_POST['user_id'])) {
    require_once "include/check_post_key.php";
    $lost_user_id=$mysqli->real_escape_string(trim($_POST['user_id']));
    $lost_email=$mysqli->real_escape_string(trim($_POST['email']));
    $vcode=trim($_POST['vcode']);
    if($lost_user_id&&($vcode!= $_SESSION["vcode"]||$vcode==""||$vcode==null) ){
        echo "<script language='javascript'>\n";
        echo "alert('$MSG_VCODE$MSG_Wrong !');\n";
        echo "history.go(-1);\n";
        echo "</script>";
        exit(0);
    }
    if(get_magic_quotes_gpc()){
        $lost_user_id=stripslashes($lost_user_id);
        $lost_email=stripslashes($lost_email);
    }
    $sql="SELECT `email` FROM `users` WHERE `user_id`='$lost_user_id'";
    $result=$mysqli->query($sql);
    $row = $result->fetch_array();
    $result->free();
    if($row && $row['email']==$lost_email && strpos($lost_email,'@')){
        $_SESSION['lost_user_id']=$lost_user_id;
        $_SESSION['lost_key']=createPwd();
        //******************** 发送邮件 ********************************
        require("plugins/PHPMailer/PHPMailerAutoload.php");
        require_once("plugins/PHPMailer/class.phpmailer.php");
        require_once("plugins/PHPMailer/class.smtp.php");
        $URL="http://".$_SERVER['HTTP_HOST'];
        if($_SERVER["SERVER_PORT"]!="80"){
          $URL.=":".$_SERVER["SERVER_PORT"];
        }
        $mailcontent = "<p>$lost_user_id， 这封信是由 $OJ_NAME 发送的。</p>";
        $mailcontent .="<p>您收到这封邮件，是由于这个邮箱地址在 $OJ_NAME 被登记为用户邮箱， 且该用户请求使用 Email 密码重置功能所致。</p>";
        $mailcontent .="----------------------------------------------------------------------<br><p><strong>重要！</strong></p>";
        $mailcontent .="<p>如果您没有提交密码重置的请求或不是 $OJ_NAME 的注册用户，请立即忽略并删除这封邮件。只有在您确认需要重置密码的情况下，才需要继续阅读下面的内容。</p>";
        $mailcontent .="----------------------------------------------------------------------<br><p><strong>密码重置说明：</strong></p>";
        $mailcontent .= "<p>为了验证您的身份,请将下面红色加粗的16位".$MSG_Securekey."输入密码重置页面以确认身份:";
        $mailcontent .= "<br><strong><font color=red>".$_SESSION['lost_key']."</font></strong></p>";
        $mailcontent .= "<p>这个".$MSG_Securekey."也将成为您重置后的新密码！您可以在用户控制面板中随时修改您的密码。</p>";
        $mailcontent .= "<p>本请求提交者的 IP 为 ".$_SERVER['REMOTE_ADDR']." 。</p>";
        $mailcontent .= "<p>此致 <br>$OJ_NAME <a href='$URL'>$URL</a><br>".date("Y-m-d H:i",time());//邮件内容
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
        $mail->Subject = $OJ_NAME." 登录密码重置--系统邮件请勿回复";    // 设置邮件标题
        $mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端"; // 可选项，向下兼容考虑
        $mail->MsgHTML($mailcontent);                 // 设置邮件内容
        $mail->AddAddress($row['email']);//收件人
        if(!$mail->Send()) {
            $view_errors= "密码重置邮件发送失败，请联系管理员处理！";// . $mail->ErrorInfo;
            require("template/".$OJ_TEMPLATE."/error.php");
            exit(0);
        } else {
            require("template/".$OJ_TEMPLATE."/lostpassword2.php");
        }
    } else {
        $view_errors= "没有相应的{$MSG_USER_ID}和$MSG_EMAIL";
        require("template/".$OJ_TEMPLATE."/error.php");
        exit(0);
    }
} else {
/////////////////////////Template
require("template/".$OJ_TEMPLATE."/lostpassword.php");
/////////////////////////Common foot
}?>
