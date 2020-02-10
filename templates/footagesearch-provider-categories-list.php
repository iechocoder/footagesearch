<div class="footagesearch-provider-categories">
    <h2><?php _e('Categories', 'footagesearch'); ?></h2>
    <a href="#" class="footagesearch-view-all footagesearch-view-all-categories"><?php _e('View All', 'footagesearch'); ?></a>
    <div class="clear"></div>
    <?php foreach ($categories as $cat_item) {?>
        <div class="footagesearch-provider-category">
            <?php if($cat_item['link']){?>
                <a href="<?php echo esc_url($cat_item['link']); ?>">
            <?php } ?>
                <?php if($cat_item['thumb']) { ?>
                    <img src="<?php echo $cat_item['thumb']; ?>" width="235" height="130">
                <?php } ?>
            <?php if($cat_item['link']){?>
                </a>
            <?php } ?>

            <div class="footagesearch-provider-category-caption">
                <?php if($cat_item['link']){?>
                    <a href="<?php echo esc_url($cat_item['link']); ?>">
                <?php } ?>
                    <img src="<?php echo  get_template_directory_uri() . '/images/cat_icon.png'?>">
                    <?php echo $cat_item['title']; ?>
                <?php if($cat_item['link']){?>
                    </a>
                <?php } ?>
                <?php if($cat_item['count']) { ?>
                    <div class="footagesearch-provider-category-count"><?php echo $cat_item['count']; ?></div>
                <?php } ?>
            </div>

        </div>
    <?php } ?>
    <div class="clear"></div>
</div>