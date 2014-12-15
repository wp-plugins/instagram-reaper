<?php
if (isset($_GET['page_number'])) {
  $pnum = intval($_GET['page_number']);
  $images = inst_reaper_get_harvest_in_range(($pnum * 50), 50);
} else {
  $images = inst_reaper_get_harvest_in_range(0, 50);
}
foreach($images as $image) { ?>
  <div class="single-image">
    <img src="<?php echo $image['src_thumb']; ?>" />
    <div class="overlay">
      <a href="<?php echo admin_url();?>admin.php?page=instagram_reaper/lib/inst_reaper_results.php&delete=<?php echo $image['id']; ?>">
        delete
      </a>
    </div>
  </div>
<?php }