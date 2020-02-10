<form action="<?php echo $search_action; ?>" method="get" id="filter-form">
    <div>
        <?php
        //        echo "<pre>";
        //        print_r($_REQUEST);
        //        echo "</pre>";
        ?>
        <?php //print_r($_SESSION['collection_id']); ?>
        <div class="widget-title toggle expended" style="margin: -25px -20px 0 0;"></div>
        <div class="folging-conteiner">
            <?php if ($filters) { ?>
            <ul>
                <li>
                    <!--input type="text" name="fsf" id="fs" value="<?php //echo $_REQUEST['fsf'] ? esc_attr($_REQUEST['fsf']) : 'Search within results';                                                         ?>" onclick="if(this.value == 'Search within results'){this.value='';};" onblur="if(this.value == ''){this.value = 'Search within results';};" class="text"-->
                    <input type="text" name="fsf" id="fs" <?php
                    if ($_REQUEST['fsf']) {
                        echo 'value = "' . esc_attr($_REQUEST['fsf']) . '"';
                    }
                    ?>" placeholder="Search within results" class="text">
                    <input type="hidden" name="fs"
                           value="<?php echo $_REQUEST['fs'] ? addslashes(esc_attr(urldecode($_REQUEST['fs']))) : ''; ?>"
                           placeholder="Search within results">
                    <input type="submit" class="action src-btn" value="Filter">
                </li>
                <?php
                $i = 0;
                $count = count($filters);
                //        echo "<pre>";print_r($filters);echo "</pre>";
                foreach ($filters as $filter_name => $filter) {

                    if (!$filter['additional']) {
                        $i++;
                        if ($filter['display']) {
                            ?>

                            <li class="collapsed_expanded<?php
                            if (isset($filter['collapsed']))
                                echo ' collapsed';
                            else
                                echo ' expanded';
                            if ($i == $count)
                                echo ' last'
                            ?>">
                                <p class="filter_label">
                                    <?php echo $filter['label']; ?></p>
                                <?php if ($filter['type'] == 'select') { ?>
                                    <ul>
                                        <li>
                                            <select name="<?php echo $filter_name; ?>" id="select_name" class="select">
                                                <option value=""></option>
                                                <?php foreach ($filter['options'] as $option) { ?>
                                                    <option
                                                        value="<?php echo $option['value']; ?>" <?php if ($option['selected']) echo ' selected'; ?>><?php echo $option['label']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </li>
                                    </ul>
                                <?php } else { ?>
                                    <ul>


                                        <?php
                                        switch ($filter['label']) {
                                            case 'License type':

                                                foreach ($filter['options'] as $option) {
                                                    ?>

                                                    <?php if ($option['label'] == 'RM' && $_SESSION['filter_session_array']['license_rm'] != 0) { ?>

                                                        <li id="filter_license_rm">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'RF' && $_SESSION['filter_session_array']['license_rf'] != 0) {
                                                        ?>

                                                        <li id="filter_license_rf">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                break;

                                            case 'Clips and Edited Videos':


                                                foreach ($filter['options'] as $option) {

                                                    ?>

                                                    <?php if ($option['label'] == 'Single Clips' && $_SESSION['filter_session_array']['nature_footage'] != 0) { ?>

                                                        <li id="filter_nature_footage">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'Edited Videos' && $_SESSION['filter_session_array']['natureflix'] != 0) {
                                                        ?>

                                                        <li id="filter_natureflix">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                break;


                                            case 'Format Category':
                                                foreach ($filter['options'] as $option) {

                                                    if ($option['label'] == '3D' && $_SESSION['filter_session_array']['3d'] != 0) {
                                                        ?>

                                                        <li id="filter_3d">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'Ultra HD' && $_SESSION['filter_session_array']['ultra_hd'] != 0) {
                                                        ?>

                                                        <li id="filter_ultra_hd">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'HD' && $_SESSION['filter_session_array']['hd'] != 0) {
                                                        ?>

                                                        <li id="filter_hd">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'SD' && $_SESSION['filter_session_array']['sd'] != 0) {
                                                        ?>

                                                        <li id="filter_sd">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                break;
                                            case 'Price Level':
                                                foreach ($filter['options'] as $option) {

                                                    if ($option['label'] == 'Budget' && $_SESSION['filter_session_array']['budget'] != 0) {
                                                        ?>

                                                        <li id="filter_budget">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'Standard' && $_SESSION['filter_session_array']['standard'] != 0) {
                                                        ?>

                                                        <li id="filter_standard">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'Premium' && $_SESSION['filter_session_array']['premium'] != 0) {
                                                        ?>

                                                        <li id="filter_premium">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['label'] == 'Gold' && $_SESSION['filter_session_array']['gold'] != 0) {
                                                        ?>

                                                        <li id="filter_gold">
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php
                                                            switch ($option['label']) {
                                                                default :
                                                                    echo $option['label'];
                                                                    break;
                                                            }
                                                            ?>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                                break;
                                            case 'Shot Type':
                                                foreach ($_SESSION['keywords_filter_session_array']['shot_type']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"

                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            case 'Action':
                                                foreach ($_SESSION['keywords_filter_session_array']['actions']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            case 'Appearance':
                                                foreach ($_SESSION['keywords_filter_session_array']['appearance']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            case 'Time':
                                                foreach ($_SESSION['keywords_filter_session_array']['time']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            case 'Location':
                                                foreach ($_SESSION['keywords_filter_session_array']['location']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            case 'Habitat':
                                                foreach ($_SESSION['keywords_filter_session_array']['habitat']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            case 'Subject Category':
                                                foreach ($_SESSION['keywords_filter_session_array']['subject_category']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            case 'Concept':
                                                foreach ($_SESSION['keywords_filter_session_array']['concept']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo $option;
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;

                                            case 'Collection':
                                                /* foreach ($filter['options'] as $option) {
                                                    if ($option['value'] == 'Land' && @in_array($option['value'], $_SESSION['filter_session_array']['collection_filter_name'])) {
                                                        ?>

                                                        <li>
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php echo $option['label']; ?>
                                                        </li>
                                                        <?php
                                                    }
                                                    if ($option['value'] == 'Ocean' && @in_array($option['value'], $_SESSION['filter_session_array']['collection_filter_name'])) {
                                                        ?>

                                                        <li>
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php echo $option['label']; ?>
                                                        </li>
                                                        <?php
                                                    }

                                                    if ($option['value'] == 'Adventure' && @in_array($option['value'], $_SESSION['filter_session_array']['collection_filter_name'])) {
                                                        ?>

                                                        <li>
                                                            <input name="<?php echo $filter_name; ?>[]"
                                                                   value="<?php echo $option['value']; ?>"
                                                                   type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                                   class="checkbox">
                                                            <?php echo $option['label']; ?>
                                                        </li>
                                                        <?php
                                                    }
                                                } */
                                                foreach ($_SESSION['keywords_filter_session_array']['category']['options'] as $option) {
                                                    $option = explode("|", $option);
                                                    $id = $option[0];
                                                    $option = $option[1];
                                                    ?>
                                                    <li id="filter<?php echo $id; ?>">
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option; ?>"
                                                               type="checkbox"
                                                            <?php
                                                            if (!empty($_REQUEST[$filter_name])) {
                                                                if (in_array($option, $_REQUEST[$filter_name])) echo ' checked';
                                                            }
                                                            ?>
                                                               class="checkbox">
                                                        <?php echo($option == "Land" ? "Nature & Wildlife" : ($option == "Ocean" ? "Ocean & Underwater" : ""));
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                            default :

                                                foreach ($filter['options'] as $option) {
//                                                    echo "<pre>";
//                                                    print_r($option);
//                                                    echo "</pre>";
                                                    ?>
                                                    <li>
                                                        <input name="<?php echo $filter_name; ?>[]"
                                                               value="<?php echo $option['value']; ?>"
                                                               type="checkbox"<?php if ($option['selected']) echo ' checked'; ?>
                                                               class="checkbox">
                                                        <?php
                                                        switch ($option['label']) {
//                                            case 'Adventure': echo 'People & Adventure'; break;
//                                            case 'Land': echo 'Land & Wildlife'; break;
//                                            case 'Ocean': echo 'Ocean & Underwater'; break;
                                                            default :
                                                                echo $option['label'];
                                                                break;
                                                        }
                                                        ?>
                                                    </li>
                                                    <?php
                                                }
                                                break;
                                        }
                                        ?>

                                    </ul>
                                <?php } ?>
                            </li>
                        <?php } else {
                            ?>
                            <input type="hidden" name="<?php echo $filter_name; ?>"
                                   value="<?php echo $filter['value']; ?>">

                            <?php
                        }
                    }
                }
                ?>
            </ul>
            <input type="submit" value="Filter" class="action src-btn" style="margin-top: 10px !important;">
        </div>
    </div>
    <!--End Search filter-->
    <!--div class="folging-header clearfix">
            <h4 class="widget-title">Additional Search Filters<div class="toggle expended"></div></h4>
            <div class="folging-conteiner" style="border:0;">
                <ul>
        <?php
    $i = 0;
    $count = count($filters);

    foreach ($filters as $filter_name => $filter) {
        if ($filter['additional']) {
            $i++;
            if ($filter['display']) {
                ?>

                                                                                                                                                                                                                        <li class="collapsed_expanded<?php
                if (isset($filter['collapsed']))
                    echo ' collapsed';
                else
                    echo ' expanded';
                if ($i == $count)
                    echo ' last'
                ?>">
                                                                                                                                                                                                                        <p class="filter_label"><?php echo $filter['label']; ?></p>
                    <?php if ($filter['type'] == 'select') { ?>
                                                                                                                                                                                                                            <ul>
                                                                                                                                                                                                                            <li>
                                                                                                                                                                                                                            <select name="<?php echo $filter_name; ?>" class="select">
                                                                                                                                                                                                                            <option value=""></option>
                        <?php foreach ($filter['options'] as $option) { ?>
                                                                                                                                                                                                                                <option value="<?php echo $option['value']; ?>" <?php if ($option['selected']) echo ' selected'; ?>><?php echo $option['label']; ?></option>
                        <?php } ?>
                                                                                                                                                                                                                            </select>
                                                                                                                                                                                                                            </li>
                                                                                                                                                                                                                            </ul>
                    <?php } else { ?>
                                                                                                                                                                                                                            <ul>
                        <?php foreach ($filter['options'] as $option) { ?>
                                                                                                                                                                                                                                <li><input name="<?php echo $filter_name; ?>[]" value="<?php echo $option['value']; ?>" type="checkbox"<?php if ($option['selected']) echo ' checked'; ?> class="checkbox">
                            <?php
                    switch ($option['label']) {
                        case 'Adventure':
                            echo 'People & Adventure';
                            break;
                        case 'Land':
                            echo 'Land & Wildlife';
                            break;
                        case 'Ocean':
                            echo 'Ocean & Underwater';
                            break;
                        default :
                            echo $option['label'];
                            break;
                    }
                    ?>
                                                                                                                                                                                                                                </li>
                        <?php } ?>
                                                                                                                                                                                                                            </ul>
                    <?php } ?>
                                                                                                                                                                                                                        </li>
                <?php } else {
                ?>
                                                                                                                                                                                                                        <input type="hidden" name="<?php echo $filter_name; ?>" value="<?php echo $filter['value']; ?>">
                    <?php
            }
        }
    }
    ?>
                </ul>
                <input type="submit" value="Filter" class="action src-btn" style="margin-top: 10px !important;">
            </div>
        </div-->

    <?php }
    $backend_url = get_option('backend_url');
    $_SESSION['current_words'] = str_replace("(", "$@", $_SESSION['current_words']);
    $_SESSION['current_words'] = str_replace(")", "@$", $_SESSION['current_words']);
    $apiurl = trim($backend_url, '/') . '/en/fapi/filter/words/' . urlencode($_SESSION['current_words']) . "/?format=jsonp&callback=?";
    ?>
</form>
<script language="javascript" type="text/javascript">
    jQuery(document).ready(function ($) {
        $.ajax({
            url: "<?php echo $apiurl; ?>",
            crossDomain: true,
            dataType: "jsonp",
            cache: true,
            type: "GET",
            success: function (data) {
                if (data.length > 0) {
                    for (var x = 0; x < data.length; x++) {
                        
                        $("#filter" + data[x].id).remove();
                    }
                }
            }
        });
    });
</script>