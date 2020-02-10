<?php
error_reporting(0);
$collections_suffixes = array(
    'Land',
    'Ocean',
    'Adventure'
);
//print_r($slaves) ;

?>
<div id="footagesearch-clip-preview-dialog" class="naturenewlist"></div>
<div class="footagesearch-header-wrapper">
    <h2 class="search-result-description">
        Viewing <span><?php echo $result['from']; ?></span> to <span><?php echo $result['to']; ?></span> of
        <span><?php echo $result['total']; ?></span> Video Clips
    </h2>
    <?php //var_dump($result);//var_dump($result['solrdata']['query']);?>

</div>
<?php
global $wpdb;
//$con = mysql_connect('54.208.133.139', 'root', 'dUeasQldBNsA');
//$db = mysql_select_db('fsmaster');
//if (!$con) {
//    die('Could not connect: ' . mysql_error());
//}
/* $sql = "SELECT ip_address FROM check_ip where ip_address='".$get_ip."'";
  $result = mysql_query($sql); */


@session_start();
$_SESSION['cururl'] = "";
unset($_SESSION['cururl']);
$_SESSION['cururl'] = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

function get_client_ip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

?>
<!-------------------Search Actions-------------------------->
<div class="container sorting">
    <!--Pagination-->
    <div class="grid-left">
        <?php if ($pagination) { ?>
            <div class="footagesearch-clips-list-pagination"><?php echo $pagination; ?></div>
        <?php } ?>
    </div>
    <?php
    global $wp_query;
    $search_title = get_the_title();
    $title_exp = explode(">", $search_title);
    $title_match = $title_exp[3];
    $str = implode(' ', array_keys(array_flip(explode(' ', $title_match))));
    $str1 = implode(' ', array_unique(explode(' ', $str)));
    $str_exp_word = explode("<", $str1);
    $word_search = $str_exp_word[0];
    $lastSpacePosition = strrpos($str1, ' ');
    $textWithoutLastWord = substr($str1, 0, $lastSpacePosition);
    $provider_id = get_option('provider_id');
    $words = array();
    if (isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']) {
        $word = $wp_query->query_vars['words'];
        $word_parts = explode('-', $word);
        $last_part = end($word_parts);

        if (in_array($last_part, $this->collections_suffixes)) {
            array_pop($word_parts);
            $word = implode('-', $word_parts);
        }
        $query_vars_word = stripcslashes(str_replace('-', ' ', $word));

//$words[] = $query_vars_word;
    }
    ?>
    <input type="hidden" name="provider_id" id="providerId" value="<?php echo $provider_id ?>">
    <input type="hidden" name="select_found_set_value" id="select_found_set_values" value="">
    <input type="hidden" name="search_names" id="search_name" value='<?php echo $word_search; ?>'>
    <!--Sort filters-->
    <div class="grid-right">
        <div class="sort-wrapper padding">
            <div class="footagesearch-clips-list-sort-cont">
                <select name="sort" class="footagesearch-clips-list-select footagesearch-clips-list-sort">
                    <?php foreach ($sort_options as $sort_option) { ?>
                        <option
                            value="<?php echo $sort_option['link']; ?>"<?php if ($sort_option['selected']) echo ' selected="selected"'; ?>><?php echo 'Sort ' . $sort_option['label']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="footagesearch-clips-list-actions-cont">
                <select name="footagesearch_clips_list_actions"
                        class="footagesearch-clips-list-select footagesearch-clips-list-actions">
                    <option value="">Actions</option>
                    <option value="select_all">Select all</option>
                    <option value="deselect_all">Select None</option>
                    <option value="select_found_set">Select Found Set</option>
                    <option value="add_selected_to_cart">Add Selected to Cart</option>
                    <option value="del_selected_to_cart">Remove Selected From Cart</option>
                    <option value="add_selected_to_clipbin">Add Selected to Clipbin</option>
                    <option value="del_selected_to_clipbin">Remove Selected From Clipbin</option>
                    <!--option value="preview_cart_download">Download All Preview Clips From Cart</option-->
                </select>
            </div>
            <div class="footagesearch-clips-list-perpage-cont">
                <form method="post" name="perpage_form1" id="perpage_form1"
                      action="<?php echo $perpage_form_action; ?>">
                    Clips per page
                    <select name="perpage" onchange='document.getElementById("perpage_form1").submit();'
                            class="footagesearch-clips-list-select footagesearch-clips-list-perpage">
                        <option value="20" <?php if ($perpage == 20) echo "selected" ?>>20</option>
                        <option value="40" <?php if ($perpage == 40) echo "selected" ?>>40</option>
                        <option value="80" <?php if ($perpage == 80) echo "selected" ?>>80</option>
                        <option value="100" <?php if ($perpage == 100) echo "selected" ?>>100</option>
                        <option value="120" <?php if ($perpage == 120) echo "selected" ?>>120</option>
                        <option value="200" <?php if ($perpage == 200) echo "selected" ?>>200</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="sort-wrapper">
            <div class="footagesearch-clips-list-toggle-view-cont">
                <form method="post" class="footagesearc-list-view-form">
                    <input type="hidden" name="list_view">
                </form>
                <div
                    class="footagesearch-clips-toggle-list-view<?php if (isset($list_view) && $list_view == 'list') echo ' active'; ?>">
                    &nbsp;</div>
                <div
                    class="footagesearch-clips-toggle-grid-view<?php if (!isset($list_view) || $list_view == 'grid') echo ' active'; ?>">
                    &nbsp;</div>
                <!--div class="clearboth"></div-->
            </div>
        </div>
    </div>
</div>

<div class="clearboth"></div>
<!-------------------END Search Actions-------------------------->
<?php if ($drag_and_drop_message) { ?>
    <div class="footagesearch-drag-and-drop-message">For your convenience we implemented Drag and Drop feature for clips
        to be used with Clipbins on this site. Please register to be able to use it.
    </div>
<?php } ?>
 <style type="text/css">

            .demo-table .highlight, .demo-table .selected {
                color: #F4B30A;
                text-shadow: 0 0 1px #F48F0A;
            }

            .btn-likes {
                float: left;
                padding: 0px 5px;
                cursor: pointer;
            }

            .btn-likes input[type="button"] {
                width: 30px;
                height: 25px;
                border: 0;
                cursor: pointer;
            }

            .like {
                background: url('<?php echo get_template_directory_uri(); ?>/images/icons/like-icon.png');
                background-repeat: no-repeat;
            }

            .like:hover {
                background: url('<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png');
                background-repeat: no-repeat;
            }

            .unlike {
                background: url('unlike.png')
            }

            .label-likes {
                font-size: 12px;
                color: #2F529B;
                height: 20px;
                padding-top: 3px;
            }

            /*.demo-table li{cursor:pointer;list-style-type: none;display: inline-block;color: #F0F0F0;text-shadow: 0 0 1px #666666;font-size:20px;}
            .demo-table .highlight, .demo-table .selected {color:#F4B30A;text-shadow: 0 0 1px #F48F0A;}*/
        </style>
<div id="d-clip-list" class="footagesearch-clips-<?php echo isset($list_view) ? $list_view : 'grid' ?>">

<?php //include 'footagesearch-clips-list.php'?>
</div>
<br>
<!-------------------Search Actions-------------------------->
<?php if ($result['total'] > 10) { ?>
    <div class="container sorting">
        <!--Pagination-->
        <div class="grid-left">
            <?php if ($pagination) { ?>
                <div class="footagesearch-clips-list-pagination"><?php echo $pagination; ?></div>
            <?php } ?>
        </div>
        <!--Sort filters-->
        <div class="grid-right">
            <div class="sort-wrapper padding">
                <div class="footagesearch-clips-list-sort-cont">
                    <select name="sort" class="footagesearch-clips-list-select footagesearch-clips-list-sort">
                        <?php foreach ($sort_options as $sort_option) { ?>
                            <option
                                value="<?php echo $sort_option['link']; ?>"<?php if ($sort_option['selected']) echo ' selected="selected"'; ?>><?php echo 'Sort ' . $sort_option['label']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="footagesearch-clips-list-actions-cont">
                    <select name="footagesearch_clips_list_actions"
                            class="footagesearch-clips-list-select footagesearch-clips-list-actions">
                        <option value="">Actions</option>
                        <option value="select_all">Select all</option>
                        <option value="deselect_all">Select None</option>
                        <option value="select_found_set">Select Found Set</option>
                        <option value="add_selected_to_cart">Add Selected to Cart</option>
                        <option value="add_selected_to_clipbin">Add Selected to Clipbin</option>
                    </select>
                </div>
                <div class="footagesearch-clips-list-perpage-cont">
                    <form method="post" name="perpage_form1" id="perpage_form2"
                          action="<?php echo $perpage_form_action; ?>">
                        Clips per page
                        <select name="perpage" onchange='document.getElementById("perpage_form2").submit();'
                                class="footagesearch-clips-list-select footagesearch-clips-list-perpage">
                            <option value="20" <?php if ($perpage == 20) echo "selected" ?>>20</option>
                            <option value="40" <?php if ($perpage == 40) echo "selected" ?>>40</option>
                            <option value="80" <?php if ($perpage == 80) echo "selected" ?>>80</option>
                            <option value="100" <?php if ($perpage == 100) echo "selected" ?>>100</option>
                            <option value="120" <?php if ($perpage == 120) echo "selected" ?>>120</option>
                            <option value="200" <? if ($perpage == 200) echo "selected" ?>>200</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="sort-wrapper">
                <div class="footagesearch-clips-list-toggle-view-cont">
                    <form method="post" class="footagesearc-list-view-form">
                        <input type="hidden" name="list_view">
                    </form>
                    <div
                        class="footagesearch-clips-toggle-list-view<?php if (isset($list_view) && $list_view == 'list') echo ' active'; ?>">
                        &nbsp;</div>
                    <div
                        class="footagesearch-clips-toggle-grid-view<?php if (!isset($list_view) || $list_view == 'grid') echo ' active'; ?>">
                        &nbsp;</div>
                    <!--div class="clearboth"></div-->
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<div class="clearboth"></div>
<script>
    jQuery(document).on("click", "#cboxClose2", function () {
        jQuery.colorbox.close();
    });
    jQuery(document).ready(function () {


        jQuery(".preview_download").hover(function () {
            jQuery("#footagesearch-clip-preview").css("display", "none");
        });
        jQuery(".info transitiable").hover(function () {
            jQuery("#footagesearch-clip-preview").css("display", "block");
        });

        jQuery(".inline").colorbox({inline: true, width: "80%", height: "80%"});
        jQuery(".inline1").colorbox({inline: true, width: "30%"});
    });
</script>
<!-------------------END Search Actions-------------------------->
<!-- clipPreviewBox -->
<div id="footagesearch-clip-preview"
     data-term="<?php echo $_SESSION['footagesearch_cart_license_term']; ?>"
     data-format="<?php echo $_SESSION['footagesearch_cart_license_format']; ?>"
     data-use="<?php echo $_SESSION['footagesearch_cart_license_use']; ?>"
     data-category="<?php echo $_SESSION['footagesearch_cart_license_category'] ?>"
     style="display: none;">
    <h6 class="titleHover"></h6>

    <p class="clickImage"></p>
    <br clear="all">
    <video id="" class="video-js vjs-default-skin" preload="auto" muted width="432" height="auto" data-setup="{}">
        <source src="" type="video/mp4">
    </video>
    <p class="description"></p>

    <p class="license_restrictions"></p>

    <p class="country"></p>

    <p class="price_level"></p>
    <?php
    $userdetail = wp_get_current_user();
    $username = $userdetail->user_login;
    $getUserDataByName = "select * from lib_users where login = '" . $username . "' ";
    $getUserResult = execute_query($getUserDataByName);
    $adminUserId = $getUserResult['group_id'];
    ?>

    <?php if ($adminUserId == 1) { ?>

        <p class="source_format"></p>
        <p class="dilivery_options"></p>
    <?php } ?>
    <!--    <p class="master_format"></p>-->
</div>
<div id="prewiew-download-popup"></div>
<?php

function execute_query($sql)
{
    $con = connect();
    $result = mysql_query($sql, $con) or die(mysql_error());
    if (!$result) {
        trigger_error("Problem slecting data");
    }
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    disconnect($con);
    return $row;
}

function count_query($sql)
{
    $con = connect();
    $result = mysql_query($sql, $con) or die(mysql_error());
    if (!$result) {
        trigger_error("Problem slecting data");
    }
    $row = mysql_num_rows($result);
    disconnect($con);
    return $row;
}

function connect()
{
    $slaves = array();
    include($_SERVER["DOCUMENT_ROOT"] . '/db-slaves.php');
    $con = mysql_connect($slaves[0]['db_host'], $slaves[0]['db_user'], $slaves[0]['db_password']);
    if (!$con) {
        rigger_error("Problem connecting to server");
    }
    $db = mysql_select_db(BACKEND_DB_NAME, $con);
    if (!$db) {
        trigger_error("Problem selecting database");
    }
    return $con;
}

function disconnect($con)
{
    $discdb = mysql_close($con);
    if (!$discdb) {
        trigger_error("Problem disconnecting database");
    }
}

function execute_update($sql)
{
    $con = connect();
    $result = mysql_query($sql, $con);
    $return_id = mysql_insert_id();
    if (!$result) {
        trigger_error("Problem updating data");
    }
    disconnect($con);
    return $return_id;
}

?>
<style type="text/css">
    /*#cboxClose {*/
        /*display: none !important;*/
    /*}*/

    /*#cboxLoadedContent {*/
        /*height: 400px !important;*/
    /*}*/

    .cancelbtn {
        background: #999 none repeat scroll 0 0;
        bottom: 8px;
        color: rgb(0, 138, 164);
        cursor: pointer;
        padding: 20px;
        position: absolute;
        left: 377px;
        text-align: center;
        z-index: 9999;
        color: #fff;
        border: 1px solid #404040;
    }

    .cancelbtn:hover {
        background: #404040;
        color: #FFF;
        text-decoration: none;
    }

    .downloadvideobtn {
        background: rgb(61, 205, 88) none repeat scroll 0 0;
        bottom: 8px;
        color: rgb(255, 255, 255);
        left: 5px;
        padding: 20px;
        position: absolute;
        z-index: 9999;
        border: 1px solid #217F32;
    }

    .downloadvideobtn:hover {
        background: #217F32;
        color: #FFF;
        text-decoration: none;
    }

    .downloadagreementbtn {
        background: rgb(61, 205, 88) none repeat scroll 0 0;
        bottom: 8px;
        color: #fff;
        cursor: pointer;
        left: 170px;
        padding: 20px;
        position: absolute;
        text-align: center;
        z-index: 9999;
        border: 1px solid #217F32;
    }

    .downloadagreementbtn:hover {
        background: #217F32;
        color: #FFF;
        text-decoration: none;
    }

    .remtext {
        background: #faf9c9 none repeat scroll 0 0;
        border: 1px solid #f0911d;
        bottom: 8px;
        color: #f0911d;
        font-size: 16px;
        padding: 20px;
        position: absolute;
        right: 58px;
        z-index: 9999;
    }

    .clickImage {
        margin: 0px;
        padding: 0px;
        float: right;
    }

    .titleHover {
        margin: 10px 0px 0px 0px;
        padding: 0px;
        float: left;
        width: auto
    }

    .footagesearch-clip-play-forward-actions {
        float: left !important;
        min-width: 109px !important;
        text-align: left !important;
    }

    .footagesearch-clip-cart-clipbin-actions {
        margin-left: 0px !important;
    }

    .heartPosition {
        width: 18px;
        float: right;
        margin-left: 15px;
    }
</style>


<script type="text/javascript">
    jQuery(document).ready(function () {
       // url='<?php echo get_template_directory_uri()?>/ajax/footagesearch-clips-list.php';
        url='<?php echo plugins_url()?>/footagesearch/templates/footagesearch-clips-list.php?v=1';

        jQuery.ajax({
            url: url,
        //    data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id,
            data:'1=02',
            type: "POST",
            timeout: 100000,
            dataType:"html",
            beforeSend: function () {
                jQuery('#d-clip-list').html('<img src="<?php echo get_template_directory_uri(); ?>/images/loading.gif" />');
            },
            success: function (data) {
                jQuery('#d-clip-list').html(data);
                console.log('in clip list==');

            }
//            ,
//            complete: function (data) {
//                jQuery('#d-clip-list').html(data);
//                console.log('in clip list=='+data.substring(1,10))
//            }
        });
    });

    function addLikes(id, action, deleteId) {
        jQuery('.demo-table #tutorial-' + id + ' li').each(function (index) {
            jQuery(this).addClass('selected');
            jQuery('#tutorial-' + id + ' #rating').val((index + 1));
            if (index == jQuery('.demo-table #tutorial-' + id + ' li').index(obj)) {
                return false;
            }
        });
        var user_login_id = jQuery('#user_login_id').val();
        var url = '<?php echo get_template_directory_uri(); ?>/ajax_rating.php';
        jQuery.ajax({
            url: url,
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id,
            type: "POST",
            beforeSend: function () {
                jQuery('#tutorial-' + id + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/LoaderIcon.gif" />');
            },
            success: function (data) {

                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = data;
                switch (action) {
                    case "like":
                        jQuery('#tutorial-' + id + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        jQuery('#tutorial-' + id + ' .inner_like').html('<img style="cursor:pointer;" onClick="deleteLikes(\'' + deleteId + '\',\'unlike\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        //likes = likes+1;
                        break;
                    case "unlike":
                        jQuery('#tutorial-' + id + ' .btn-likes').html('<input type="button" title="Like" class="like"  onClick="addLikes(' + id + ',\'like\')" />')
                        likes = likes - 1;
                        break;
                }
                jQuery('#likes-' + id).val(likes);
                if (likes > 0) {
                    jQuery('#tutorial-' + id + ' .label-likes').html(likes + " Like(s)");
                } else {
                    jQuery('#tutorial-' + id + ' .label-likes').html('');
                }
            }
        });
    }

    function deleteLikes(id, action, clipId) {
        jQuery('.demo-table #tutorial-' + clipId + ' li').each(function (index) {
            jQuery(this).addClass('selected');
            jQuery('#tutorial-' + clipId + ' #rating').val((index + 1));
            if (index == jQuery('.demo-table #tutorial-' + clipId + ' li').index(obj)) {
                return false;
            }
        });
        var user_login_id = jQuery('#user_login_id').val();
        var url = '<?php echo get_template_directory_uri(); ?>/ajax_rating.php';
        jQuery.ajax({
            url: url,
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id,
            type: "POST",
            beforeSend: function () {
                jQuery('#tutorial-' + clipId + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/LoaderIcon.gif" />');
            },
            success: function (data) {
                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = data;
                switch (action) {
                    case "like":
                        jQuery('#tutorial-' + clipId + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        //likes = likes+1;
                        break;
                    case "unlike":
                        jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-icon.png" />');
                        likes = likes - 1;
                        break;
                }
                jQuery('#likes-' + clipId).val(likes);
                if (likes > 0) {
                    jQuery('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                } else {
                    jQuery('#tutorial-' + clipId + ' .label-likes').html('');
                }
            }
        });
    }
</script>