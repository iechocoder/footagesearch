<br/>
<div style="width:100%;">
<?php
    if(!empty($result['data'])){
        foreach($result['data'] as $k=>$user){
             $name = (!empty($user['company_name'])) ? $user['company_name'] : $user['fname'].' '.$user['lname']; ?>
            <div><?php echo $html[$k];?><a href="/contributor/profile/<?php echo $user['login'];?>"><?php echo $name;?></a></div>
        <?php
        }
    }else{
        ?> No Contributors<?php
    }
?>
</div>
