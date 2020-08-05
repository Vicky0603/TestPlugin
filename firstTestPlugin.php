<?php
/*
Plugin Name: FirstTestPlugin
Plugin URI: 
Description: Adding buttons of different services like VK, FB and etc
*/

//connecting the filter, which will work before showing the content
add_filter( 'the_content', 'yandexshare_run' );

// function adds buttons
function yandexshare_run($content) {
    // get service for icons
    $sharebtn = get_option('yandexshare_bnts');
    
    // if no service selected, only main to be used
    if ( !$sharebtn ) $sharebtn = 'vkontakte,odnoklassniki,facebook,twitter,gplus';
    
    // show if icons are big
    $bigbtn = get_option('yandexshare_bigbtn');
    
    // code of Yandex Share. Details — https://tech.yandex.ru/share/
    // Necessary scripts
    $script = '
        <script type="text/javascript" src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js" charset="utf-8"></script>
        <script type="text/javascript" src="//yastatic.net/share2/share.js" charset="utf-8"></script>';
    // here we consider desired size for icons
    $data = '<div class="ya-share2" data-services="' . $sharebtn . '"' . ($bigbtn ? '' : ' data-size="s"') . '></div>';
    
    // connecting the scripts,
    // post and blocks
    return $script . $data . $content . $data;
}

// adding one option to main menu WP
add_action('admin_menu', 'yandexshare_admin_menu');
function yandexshare_admin_menu(){
    add_options_page('YandexShare', 'YandexShare', 'manage_options', 'yandexshare-options', 'yandexshare_admin_manage');
}

//  main page for settings
function yandexshare_admin_manage() {
    // the available icons
    $share_bnt_list = array(
        'vkontakte'     => '?????????',
        'facebook'      => 'Facebook',
        'odnoklassniki' => '?????????????',
        'moimir'        => '??????',
        'gplus'         => 'Google+',
        'twitter'       => 'Twitter',
        'blogger'       => 'Blogger',
        'linkedin'      => 'LinkedIn',
        'lj'            => 'Livejournal',
        'viber'         => 'Viber',
        'whatsapp'      => 'WhatsApp',
    );
    
    // POST-parametets shown from our database
    if (isset($_POST['yandexshare_bigbtn'])) {
        // big button
        $bigbtn = empty($_POST['yandexshare_bigbtn']) ? 0 : 1;
        
        // if installed - add, otherwise deleting,
        // anyway the result is 0
        if ($bigbtn) update_option('yandexshare_bigbtn', 1);
        else delete_option('yandexshare_bigbtn');
        
        // if button installes required...
        if (isset($_POST['yandexshare_bnts']) && is_array($_POST['yandexshare_bnts'])) {
            // arranging it to the necessary format
            $sharebtn =  $_POST['yandexshare_bnts']
                       ? implode(',', $_POST['yandexshare_bnts'])
                       : '';
        }
        
        // if all marks are removed...
        if (empty($sharebtn)) {
            // delete from settings of WP
            delete_option('yandexshare_bnts');
            // leaving the initial result
            $sharebtn = 'vkontakte,odnoklassniki,facebook,twitter,gplus';
        } else {
            // otherwise installing the necessary buttons
            update_option('yandexshare_bnts', $sharebtn);
        }
        $success = true;
    } else {
        // get the content of buttons
        $bigbtn = get_option('yandexshare_bigbtn');
        $sharebtn = get_option('yandexshare_bnts');
        
        // if no service selected - show the main services;
        // for deactivation of the plugin, please select the option deactivate!
        if ( !$sharebtn ) $sharebtn = 'vkontakte,odnoklassniki,facebook,twitter,gplus';
    }
    $sharebtn_list = explode(',', $sharebtn);
?>
<div class="wrap">
    <h2>Settings YandexShare</h2>
    <?php if ( isset($success) ) {
        echo '<div class="updated"><p>Settings succesfully saved.</p></div>';
    } ?>
    <form action="" method="post">
      <table class="table">
        <tbody>
          <tr>
            <th>Use big buttons</th>
            <td>
              <select name="yandexshare_bigbtn">
                <option value="0">No</option>
                <option<?= $bigbtn ? ' selected="selected"' : '' ?> value="1">Yes</option>
              </select>
            </td>
          </tr>
          
          <tr>
            <th>Services to use</th>
            <td>
              <select name="yandexshare_bnts[]" size="<?= count($share_bnt_list) ?>" multiple>
                <?php foreach ($share_bnt_list as $k => $v) {
                    echo '<option ' , in_array($k, $sharebtn_list) ? 'selected="selected" ' : '' , 'value="' . $k . '">' . $v . '</option>';
                } ?>
              </select>
              <p class="description">Delete/remove services to be done by clicking a big button </p>
            </td>
          </tr>
        </tbody>
      </table>
      <p class="submit"><input type="submit" value="Save changes" class="button button-primary"></p>
    </form>
</div>
<?php
}

// ??????? ?????? ?? ????????? ? ?????? ????????
add_filter('plugin_action_links', 'yandexshare_links', 10, 2);

// ???????? ?????? ?? ?????????
function yandexshare_links($links, $file) {
    $plugin_file = basename(__FILE__);
    if ($file == $plugin_file) {
        $settings_link = '<a href="options-general.php?page=yandexshare-options">?????????</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

// if plugin is deleted, everything to be deleted
register_uninstall_hook(__FILE__, 'yandexshare_remove');

// to delete options of plugins
function yandexshare_remove(){
    delete_option('yandexshare_bnts');
    delete_option('yandexshare_bigbtn');
}


