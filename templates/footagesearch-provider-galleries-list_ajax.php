<div class="footagesearch-categories-list">
    <ul>
<?php foreach ($galleries as $gallery_item) {?>
        <li class="footagesearch-category">
            <h2><a href="<?php echo esc_url($gallery_item['link']); ?>"><?php echo $gallery_item['title']; ?></a></h2>
            <a href="<?php echo esc_url($gallery_item['link']); ?>">
                <img src="<?php echo (!empty($gallery_item['preview_clip'])) ? urldecode($gallery_item['preview_clip']) : '/backend-content/profiles/no-photo.jpg'; ?>" alt="<?php echo $gallery_item['count']; ?> images" />
            </a>
            <p><?php echo $gallery_item['description']; ?></p>
        </li>
<?php } ?>
    </ul>
    <div class="clear"></div>
</div>