<?php
// include('lib/reaper_core_functions.php');

function save_inst_reaper_options() {
  // include('lib/reaper_core_functions.php');
  update_option('inst_client_id', $_POST['client_id']);
  $inst_options = get_option('inst_reaper_options');

  $username = $_POST['username'];
  $query = $_POST['query'];
  $user_id = inst_reaper_get_user_id_by_name($username);
  $hashtag = $_POST['hashtag'];
  $count = $_POST['count'];
  $recurrence = $_POST['recurrence'];

  $inst_options = array(
    'chron' => true,
    'query' => $query,
    'username' => $username,
    'user_id' => $user_id,
    'hashtag' => $hashtag,
    'count' => $count,
    'recurrence' => $recurrence
  );
  update_option('inst_reaper_options', $inst_options);
  wp_clear_scheduled_hook( 'inst_reaper_event' );
  wp_schedule_event( time(), $recurrence, 'inst_reaper_event');
}

if ( isset( $_POST['inst_reaper_stop_nonce'] ) && wp_verify_nonce( $_POST['inst_reaper_stop_nonce'], 'inst_reaper_stop_nonce' ) ) { stop_inst_reaper_chron(); }
if ( isset( $_POST['inst_reaper_options_nonce'] ) && wp_verify_nonce( $_POST['inst_reaper_options_nonce'], 'inst_reaper_options_nonce' ) ) { 
  save_inst_reaper_options(); 
}

if (!get_option('inst_client_id')) { ?>
  <div class="error">
    <p>To use the Instagram Reaper, you must create an Instagram Client and supply the Client ID.  Click <a href="http://instagram.com/developer" target="_blank">here</a> to get started.</p>
    <p>If you have a Client ID ready to go, enter it in below to make this message go away</p> 
  </div>
<?php } ?>

<?php $inst_options = get_option('inst_reaper_options'); ?>

<div class="wrap">
  <h2>Instagram Reaper Settings</h2>


    <?php if ($inst_options['chron'] == true) { ?>
      <p>Chron is currently running</p>
      <form action="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" method="post">
        <?php wp_nonce_field( 'inst_reaper_stop_nonce','inst_reaper_stop_nonce' ); ?>
        <input type="submit" name="submit" class="button button-warning" value="Stop Chron">
      </form>
    <?php } else { ?>
      <p>Chron is not currently running</p>
    <?php } ?>


  <form action="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" method="post">
    <?php wp_nonce_field( 'inst_reaper_options_nonce','inst_reaper_options_nonce' ); ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">Instagram Client ID</th>
          <td><input type="text" name="client_id" class="full-text" value="<?php echo get_option('inst_client_id'); ?>" <?php echo current_user_can('activate_plugins') ? '' : 'disabled' ?>></td>
        </tr>
        <tr>
          <th scope="row">Query By</th>
          <td>
            <select name="query" id="query">
              <option value="hashtag" <?php echo $inst_options['query'] == 'hashtag' ? 'selected' : ''; ?>>Hashtag</option>
              <option value="username" <?php echo $inst_options['query'] == 'username' ? 'selected' : ''; ?>>Username</option>
            </select>
          </td>
        </tr>
        <?php 
          if ($inst_options == false) {
            $un_style = 'style="display: none;"';
            $ht_style = '';
          } else if (array_key_exists('query', $inst_options)) {
            if ($inst_options['query'] == 'username') {
              $un_style = '';
              $ht_style = 'style="display: none;"';
            } else if ($inst_options['query'] == 'hashtag') {
              $ht_style = '';
              $un_style = 'style="display: none;"';
            } else {
              $un_style = 'style="display: none;"';
              $ht_style = 'style="display: none;"';
            }
          } else {
            $un_style = 'style="display: none;"';
            $ht_style = '';
          }
        ?>
        <tr id="hashtag" <?php echo $ht_style; ?>>
          <th scope="row">Hashtag</th>
          <td><input type="text" name="hashtag" class="full-text" value="<?php echo $inst_options['hashtag']; ?>"></td>
        </tr>
        <tr id="username" <?php echo $un_style; ?>>
          <th scope="row">Username</th>
          <td><input type="text" name="username" class="full-text" value="<?php echo $inst_options['username']; ?>"></td>
        </tr>
        <script>
          $ = jQuery;
          $('#query').on('change', function(){
            var query = $(this).val();
            if (query == 'hashtag') {
              $('#hashtag').css('display', '');
              $('#username').css('display', 'none');
            } else {
              $('#username').css('display', '');
              $('#hashtag').css('display', 'none');
            }
          });
        </script>
        <tr>
          <th scope="row">Number of Photos to Get</th>
          <td>
            <select name="count">
              <option default <?php echo $inst_options['count'] == '' ? 'selected' : ''; ?>></option>
              <option value="10" <?php echo $inst_options['count'] == '10' ? 'selected' : ''; ?>>10</option>
              <option value="20" <?php echo $inst_options['count'] == '20' ? 'selected' : ''; ?>>20</option>
              <option value="30" <?php echo $inst_options['count'] == '30' ? 'selected' : ''; ?>>30</option>
              <option value="40" <?php echo $inst_options['count'] == '40' ? 'selected' : ''; ?>>40</option>
              <option value="50" <?php echo $inst_options['count'] == '50' ? 'selected' : ''; ?>>50</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row">Recurrence</th>
          <td>
            <select name="recurrence">
              <option default <?php echo $inst_options['recurrence'] == '' ? 'selected' : ''; ?>></option>
              <option value="minutely" <?php echo $inst_options['recurrence'] == 'minutely' ? 'selected' : ''; ?>>Every Minute</option>
              <option value="half_hour" <?php echo $inst_options['recurrence'] == 'half_hour' ? 'selected' : ''; ?>>Every 30 min</option>
              <option value="hourly" <?php echo $inst_options['recurrence'] == 'hourly' ? 'selected' : ''; ?>>Every Hour</option>
              <option value="twicedaily" <?php echo $inst_options['recurrence'] == 'twicedaily' ? 'selected' : ''; ?>>Twice Daily</option>
              <option value="daily" <?php echo $inst_options['recurrence'] == 'daily' ? 'selected' : ''; ?>>Every Day</option>
              <option value="weekly" <?php echo $inst_options['recurrence'] == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
            </select>
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit"><input type="submit" name="submit" class="button button-primary" value="<?php echo $inst_options['chron'] == true ? 'Save Changes' : 'Start Chron'; ?>"></p>

  </form>
</div>