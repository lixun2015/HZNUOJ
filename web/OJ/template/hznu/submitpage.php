<?php
  /**
   * This file is created
   * by yybird
   * @2016.03.23
   * last modified
   * by yybird
   * @2016.04.12
  **/
?>

<?php
  $title=$MSG_SUBMIT;
  if(isset($_GET['id']))
    require_once("header.php");
  else
    require_once("contest_header.php");
  require_once "include/const.inc.php";
  if(isset($OJ_points_submit)){
    $points_pay = $OJ_points_submit;
  } else {
    $points_pay = 1;
  }
  if (isset($OJ_points_enable) && $OJ_points_enable && !IS_ADMIN($_SESSION['user_id']) && !isset($_SESSION['contest_id']) ){ //还要不是比赛用户isset($_SESSION['user_id']) && !isset($_SESSION['contest_id'])
    if( $points < 0){
      $view_errors= "<a href='./points_rechange.php'>余额不足，当前剩余 ".round($points,2)." <span class='am-icon-apple'></span>，请点击此处{$MSG_Recharge}{$MSG_points}。</a>";
      require("template/".$OJ_TEMPLATE."/error.php");
      exit(0);
    } else if($points < $points_pay){
      $view_errors= "<a href='./points_rechange.php'>提交一次代码需扣除 $points_pay <span class='am-icon-apple'></span>，当前剩余 ".round($points,2)." <span class='am-icon-apple'></span>，请点击此处{$MSG_Recharge}{$MSG_points}。</a>";
      require("template/".$OJ_TEMPLATE."/error.php");
      exit(0);
    }
  }
?>
<div class="am-container" style="padding-top: 20px;">
  <?php
  if(isset($_GET['cid'])) {
      $title = "Problem <strong>".PID($pid)."</strong> of Contest <strong>".$_GET['cid']."</strong>";
  }
  else {
      $title = "Problem <strong>$id</strong>";
  }
  if (isset($OJ_points_enable)&&$OJ_points_enable && !isset($_SESSION['contest_id'])){
    $title .= "&nbsp;&nbsp;【当前{$MSG_points}：".round($points,2)." <span class='am-icon-apple'></span>】";
    if(isset($OJ_points_AC)){
      $points_AC = $OJ_points_AC;
    } else {
      $points_AC = 1;
    }
    if(isset($OJ_points_firstAC)){
      $points_firstAC = $OJ_points_firstAC;
    } else {
      $points_firstAC = 1;
    }
    if(isset($OJ_points_Wrong)){
      $points_Wrong = $OJ_points_Wrong;
    } else {
      $points_Wrong = 0;
    }
    $title .= "<br>提交一次代码将扣除 $points_pay <span class='am-icon-apple'></span>；提交代码正确，奖励 $points_AC <span class='am-icon-apple'></span>";
    if($points_firstAC!=$points_AC){
      $title .= "；若是第一次AC该题，再奖励 ".($points_firstAC-$points_AC)."<span class='am-icon-apple'></span>";
    }
    if($points_Wrong != 0){
      $title .= "；若提交代码错误，加罚 ".$points_Wrong."<span class='am-icon-apple'></span>";
    }
  }
  ?>
  <div class="am-g">
    <script src="include/checksource.js"></script>
    <form id="submit_form" action="submit.php" method="post">
      <?php require_once "include/set_post_key.php" ?>
      <div class="am-u-md-10 am-u-md-centered" style="width:1000px;">
       <?php  echo "<h2>$title</h2>" ?>
        <div class="am-g am-text-center" style="margin-bottom: 20px;">
          <div class="am-u-md-6">
            <label for="language"><?php echo $MSG_LANG ?>: </label>
            <select id="language" name="language" data-am-selected="{searchBox: 1, maxHeight: 400}">
                <?php
                $lang_count=count($language_ext);
                if(isset($contest_langmask))
                    $langmask=$contest_langmask;
                else
                    $langmask=$OJ_LANGMASK;
                $lang=((int)$langmask)&((1<<($lang_count))-1);
                if(isset($_COOKIE['lastlang'])) $lastlang=$_COOKIE['lastlang'];
                else $lastlang=0;
                for($i=0;$i<$lang_count;$i++){
                    $j = $language_order[$i];
                    if($lang&(1<<$j))
                        echo"<option value=$j ".( $lastlang==$j?"selected":"").">
                                ".$language_name[$j]."
                         </option>";
                }
                ?>
            </select>
          </div>         
          <div class="m-u-md-6">
            <label for="language">Theme: </label>
            <select id="theme" name="theme" data-am-selected>
              <option value="iplastic">Bright</option>
              <option value="tomorrow_night_bright">Dark</option>
            </select>
          </div>
        </div>
        <div id="editor" class="am-u-md-centered" style="width:100%; height: 400px; border: 1px solid #F0F0F0;"><?php if(isset($view_src))echo htmlentities($view_src); ?></div>
        <input type="hidden" id="source" name="source">
          <?php
          if(isset($_GET['cid'])) {
              echo "<input type='hidden' name='cid' value='$cid'>";
              echo "<input type='hidden' name='pid' value='$pid'>";
          }
          else {
              echo "<input type='hidden' name='id' value='$id'>";
          }
          ?>
      </div>
      <div class="am-g am-text-center" style="margin-top: 20px;">
        <button class="am-btn am-btn-success"><?php echo $MSG_SUBMIT ?></button>
      </div>
    </form>
  </div>
</div>
<?php require_once("footer.php") ?>
<script src="plugins/ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/theme-iplastic.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/theme-tomorrow_night_bright.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-c_cpp.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-pascal.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-java.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-ruby.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-batchfile.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-python.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-php.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-perl.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-csharp.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-objectivec.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-scheme.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-lua.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-javascript.js" type="text/javascript" charset="utf-8"></script>
<script src="plugins/ace/mode-golang.js" type="text/javascript" charset="utf-8"></script>
<script>
    language_mod = ["c_cpp","c_cpp","pascal","java","ruby","batchfile","python","php","perl","csharp","objectivec","plain_text","scheme","c_cpp","c_cpp","lua","javascript","golang","python"];
    var editor = ace.edit("editor");
    var $obj_select_lang = $("#language");
    var lang = $obj_select_lang.val();
	  editor.setFontSize(16);    
    <?php if($OJ_EDITE_AREA){ ?>
    editor.getSession().setMode("ace/mode/"+language_mod[lang]);
    $("#submit_form").submit(function () {
        $("#source").val(editor.getValue());
        if(!checksource($("#source").val())) return false;
        return true;
    });    
    $obj_select_lang.change(function () {
        lang = $(this).val();
        editor.getSession().setMode("ace/mode/"+language_mod[lang]);
    });
    <?php } ?>
    editor.setTheme("ace/theme/iplastic");
    $("#theme").change(function () {
        editor.setTheme("ace/theme/"+$(this).val());
    });
</script>
