<?php 
  if (isset($_GET['delete'])) {
    if(!current_user_can('delete_posts')) {
      return;
    }

    inst_reaper_delete_image($_GET['delete']);

  }

?>
<?php if (!get_option('inst_client_id')) { ?>
  <div class="error">
    <p>To use the Instagram Reaper, you must create an Instagram Client and supply the Client ID.  Click <a href="http://instagram.com/developer" target="_blank">here</a> to get started.</p>
    <p>If you have a Client ID ready to go, enter it <a href="<?php echo admin_url();?>admin.php?page=instagram_reaper/lib/inst_reaper_options.php">here</a> to make this message go away</p> 
  </div>
<?php } ?>
<h2>Reaped Images</h2>
<div class="wrap" data-next-page="1">
  <?php 
    include('inst_reaper_scroll.php');
  ?>
</div>

<script>
  $ = jQuery;
  var busy = false;
  $(window).bind('scroll', function(){
    var bottom = $('.single-image').last().position().top + $('.single-image').last().height();
    var scroll = $(window).scrollTop() + $(window).height();
    if (scroll > bottom && busy == false) {
      loadMore();
    }

    function loadMore() {
      var nextPage = $('.wrap').data().nextPage;
      $.ajax({
        type: 'GET',
        datatype: 'html',
        beforeSend: function(){busy = true;},
        url: "<?php echo admin_url();?>admin.php?page=instagram_reaper/lib/inst_reaper_results.php&page_number=" + nextPage,
        success: function(data){
          $('.wrap').data().nextPage++;
          $('.wrap').append($(data).find('.single-image'));
        }
      }).always(function(){busy = false;})
    }

  })
</script>