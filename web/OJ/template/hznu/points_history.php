<?php
/**
 * This file is created
 * by lixun516@qq.com
 * @2021.06.07
 **/
?>

<?php $title=$MSG_PointsHistory;?>
<?php 
require_once("header.php");
$args=Array();
if(isset($user)) $args['user']=$user;
if(isset($page)) $args['page']=$page;
function generate_url($data){
    global $args;
    $link="points_history.php?";
    foreach ($args as $key => $value) {
        if(isset($data["$key"])){
            $value=htmlentities($data["$key"]);
            $link.="&$key=$value";
        }
        else if($value){
            $link.="&$key=".htmlentities($value);
        }
    }
    return $link;
}
 ?>
<style>
  .am-form-inline > .am-form-group {
    margin-left: 15px;
  }
  .am-form-inline {
    margin-bottom: 1.5rem;
  }
</style>
<div class='am-container'>
  <div class="am-avg-md-1" style="margin-top: 20px; margin-bottom: 20px;">
  </div>
<!-- 页标签 start -->
<div class="am-g">
  <ul class="am-pagination am-text-center">
        <?php $link = generate_url(Array("page"=>"1"))?>
        <li><a href="<?php echo $link ?>">Top</a></li>
    <?php $link = generate_url(Array("page"=>max($page-1, 1)))?>
      <li><a href="<?php echo $link ?>">&laquo; Prev</a></li>
        <?php
        //分页
        $page_size=10;
        $page_start=max(ceil($page/$page_size-1)*$page_size+1,1);
        $page_end=min(ceil($page/$page_size-1)*$page_size+$page_size,$view_total_page);
        for ($i=$page_start;$i<$page;$i++){
          $link=generate_url(Array("page"=>"$i"));
          echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        $link=generate_url(Array("page"=>"$page"));
        echo "<li class='am-active'><a href=\"$link\">{$page}</a></li>";
        for ($i=$page+1;$i<=$page_end;$i++){
          $link=generate_url(Array("page"=>"$i"));
          echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        if ($i <= $view_total_page){
          $link=generate_url(Array("page"=>"$i"));
          echo "<li><a href=\"$link\">{$i}</a></li>";
        }
      ?>
        <?php $link = generate_url(Array("page"=>min($page+1,intval($view_total_page)))) ?>
      <li><a href="<?php echo $link ?>">Next &raquo;</a></li>
  </ul>
</div>
<!-- 页标签 end --> 
  <div class="am-avg-md-1 well" style="font-size: normal;">
    <table class="am-table am-table-hover am-table-striped" style="white-space: nowrap;">
      <!-- 表头 start -->
      <thead>
      <tr><th class='am-text-left' colspan='8'>
      <?php 
        echo $MSG_USER_ID."：<a href='./userinfo.php?user=$user'>$user($nick)</a>&nbsp;&nbsp;&nbsp;&nbsp;{$MSG_points}：$nowPoints <span class='am-icon-apple'></span>&nbsp;&nbsp;&nbsp;&nbsp;{$MSG_InitialPoints}：$InitialPoints <span class='am-icon-apple'></span>"; 
        if(isset($OJ_points_reChange)&&$OJ_points_reChange){
          echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='./points_rechange.php'><span class='am-icon-credit-card-alt'></span> $MSG_points$MSG_Recharge </a>";
        }
        if(!isset($OJ_points_submit)) $OJ_points_submit=1;
        if(!isset($OJ_points_AC)) $OJ_points_AC=1;
        if(!isset($OJ_points_firstAC)) $OJ_points_firstAC=1;
        if(!isset($OJ_points_Wrong)) $OJ_points_Wrong=0;
      ?>
      </th></tr>
      <tr><th class='am-text-left' colspan='8'>
      温馨提示：
      <ol>
        <li>提交一次代码扣除 <?php echo $OJ_points_submit; ?> <span class='am-icon-apple'></span>。</li>
        <li>若提交的代码正确，奖励 <?php echo $OJ_points_AC; ?> <span class='am-icon-apple'></span>
      <?php 
        if($OJ_points_firstAC!=$OJ_points_AC){
          echo "；若是第一次AC该题，再奖励 ".($OJ_points_firstAC-$OJ_points_AC)." <span class='am-icon-apple'></span>";
        }
      ?>。</li><li>若提交的代码错误，没有积分奖励
      <?php if($OJ_points_Wrong != 0){
          echo "，并加罚 ".$points_Wrong." <span class='am-icon-apple'></span>";
        }
      ?>。</li>
          <li>不积跬步，无以至千里；不积小流，无以成江海。请充分思考，将代码考虑成熟、测试完备后再行提交。<br>速度快不能证明你强，又快又准才是真本事。</li>
      </ol>
      </th></tr>
      <tr>
        <th class='am-text-left' style='width:2%'><?php echo $MSG_ID ?></th>
        <th class='am-text-left' style='width:10%'>Code</th>
        <th class='am-text-left' style='width:13%'><?php echo $MSG_SUBMIT_TIME ?></th>
        <th class='am-text-left' style='width:40%'><?php echo $MSG_Logs ?></th>
        <th class='am-text-left'><?php echo $MSG_Operator ?></th>
        <th class='am-text-left'><?php echo $MSG_Income ?></th>
        <th class='am-text-left'><?php echo $MSG_Expenditure ?></th>
        <th class='am-text-left'><?php echo $MSG_LeftTime ?></th>
      </tr>
      </thead>
      <!-- 表头 end -->
      
      <!-- 列出积分明细 start -->
      <tbody>
      <?php
      foreach($view_logs as $row){
		  echo "<tr class='am-text-left'>";
          foreach($row as $table_cell){
              echo "<td>";
              echo $table_cell;
              echo "</td>";
          }
          echo "</tr>";
      }
      ?>
      </tbody>
      <!-- 列出积分明细 end -->
    
    </table>
  </div>

<!-- 页标签 start -->
<div class="am-g">
  <ul class="am-pagination am-text-center">
        <?php $link = generate_url(Array("page"=>"1"))?>
        <li><a href="<?php echo $link ?>">Top</a></li>
    <?php $link = generate_url(Array("page"=>max($page-1, 1)))?>
      <li><a href="<?php echo $link ?>">&laquo; Prev</a></li>
        <?php
        //分页
        $page_size=10;
        $page_start=max(ceil($page/$page_size-1)*$page_size+1,1);
        $page_end=min(ceil($page/$page_size-1)*$page_size+$page_size,$view_total_page);
        for ($i=$page_start;$i<$page;$i++){
          $link=generate_url(Array("page"=>"$i"));
          echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        $link=generate_url(Array("page"=>"$page"));
        echo "<li class='am-active'><a href=\"$link\">{$page}</a></li>";
        for ($i=$page+1;$i<=$page_end;$i++){
          $link=generate_url(Array("page"=>"$i"));
          echo "<li><a href=\"$link\">{$i}</a></li>";
        }
        if ($i <= $view_total_page){
          $link=generate_url(Array("page"=>"$i"));
          echo "<li><a href=\"$link\">{$i}</a></li>";
        }
      ?>
        <?php $link = generate_url(Array("page"=>min($page+1,intval($view_total_page)))) ?>
      <li><a href="<?php echo $link ?>">Next &raquo;</a></li>
  </ul>
</div>
<!-- 页标签 end -->

<?php include "footer.php" ?>
