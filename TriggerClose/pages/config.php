<?php

auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

layout_page_header( plugin_lang_get( 'config_title' ) );
layout_page_begin( 'manage_overview_page.php' );
print_manage_menu( 'manage_plugin_page.php' );

$saved_categories = plugin_config_get('categories');
if(!$saved_categories) {
	$saved_categories = array();
}

$saved_statuses = plugin_config_get('statuses');
if(!$saved_statuses) {
	$saved_statuses = array();
}

$saved_privileges = plugin_config_get('privileges');
$users_affected_by_privileges = 0;
if(!$saved_privileges) {
	$saved_privileges = array();
} else {
	# We sort the privileges so that [0] represents the most privileged
	# permission entry, since user_count_level() gives us all users of
	# that level *or higher*
	sort($saved_privileges);
	$users_affected_by_privileges = user_count_level($saved_privileges[0]);
}

// @todo find a corresponding function in the API
$query = " SELECT id,realname,username FROM " . 
         db_get_table('mantis_user_table') .
         " WHERE access_level=" . DEVELOPER .
         " ORDER BY date_created DESC";

$result = db_query($query);
$user_count = db_num_rows($result);
$saved_user = plugin_config_get('user');

$api = new TriggerCloseApi();

if(isset($_SESSION['TriggerClose_flash_message'])) {
	echo '<p>'.$_SESSION['TriggerClose_flash_message'].'</p>';
	unset($_SESSION['TriggerClose_flash_message']);
}

?>

<br />


<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">
<form action="<?php echo plugin_page('config_edit')?>" method="post">
<fieldset>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
    <h4 class="widget-title lighter">
        <i class="ace-icon fa fa-exchange"></i>
        <?php echo plugin_lang_get( 'config_title' ) ?>
    </h4>
</div>
<?php echo form_security_field('plugin_format_config_edit') ?>

<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">

   <!-- How to check -->
   <tr>
        <td class="category">
            <?php echo plugin_lang_get( 'check_on_page_loads' ) ?>
            <p class="small"><?php echo plugin_lang_get( 'enable_cron' ) ?></p>   
        </td>
        <td>
        	<input type="checkbox" id="maybe_close_active" name="maybe_close_active" value="1" <?php echo plugin_config_get('maybe_close_active')? 'checked="checked"' : null ?> />
        </td>
    </tr>

   <!-- Close After X seconds -->
   <tr>
       <td class="category">
            <?php echo plugin_lang_get( 'timeout_to_close' ) ?>
            <p class="small"><?php echo plugin_lang_get( 'zero_means' ) ?></p>   
       </td>
       <td>
			<input type="text" id="after_seconds" name="after_seconds" value="<?php echo plugin_config_get('after_seconds') ?>" />
       </td>
   </tr>

   <!-- Note to add while closing -->
   <tr>
       <td class="category">
            <?php echo plugin_lang_get( 'note_when_closing' ) ?> 
       </td>
       <td>
		<textarea name="message" cols="80" rows="10"><?php echo plugin_lang_get('note_text_when_closing') ?></textarea>
       </td>
   </tr>

   <!-- Categories to check -->
   <tr>
       <td class="category">
            <?php echo plugin_lang_get( 'categories_to_check' ) ?> 
       </td>
       <td>
		<select multiple="multiple" size="10" name="categories[]" id="categories">
		<?php foreach($api->available_categories() as $category_id => $label) {?>
			<option <?php if(in_array($category_id, $saved_categories)) { ?>selected="selected"<?php }?> value="<?php echo $category_id ?>"><?php echo $label ?></option>
		<?php } ?>
		</select>
       </td>
   </tr>

   <!-- Access level to check -->
   <tr>
       <td class="category">
            <?php echo plugin_lang_get( 'privileges' ) ?> 
            <?php echo '<br>' . $users_affected_by_privileges . ' ' . plugin_lang_get( 'qty_of_users' )?> 

       </td>
       <td>
		<select multiple="multiple" size="10" name="privileges[]" id="privileges">
		<?php foreach($api->available_privileges() as $privilege_id => $label) {?>
			<option <?php if(in_array($privilege_id, $saved_privileges)) { ?>selected="selected"<?php }?> value="<?php echo $privilege_id ?>"><?php echo $label ?></option>
		<?php } ?>
		</select>
       </td>
   </tr>

   <!-- Statuses to check -->
   <tr>
       <td class="category">
            <?php echo plugin_lang_get( 'statuses_to_check' ) ?> 
            <?php $api->available_statuses(); ?>
       </td>
       <td>
   		<select multiple="multiple" size="<?php echo count($api->available_statuses()) ?>" name="statuses[]" id="statuses">
		<?php foreach($api->available_statuses() as $status => $label) { ?>
			<option <?php if(in_array($status, $saved_statuses)) { ?>selected="selected"<?php }?> value="<?php echo $status ?>"><?php echo $label ?></option>
		<?php } ?>
		</select>
       </td>
   </tr>

   <!-- Close issue as user -->
   <tr>
       <td class="category">
            <?php echo plugin_lang_get( 'close_as_user' ) ?> 
       </td>
       <td>
		<select name="user" id="user">
		<?php while($user_count--) {
			$row = db_fetch_array($result);
		?>
			<option <?php if($row['id'] == $saved_user) { ?>selected="selected"<?php }?> value="<?php echo $row['id'] ?>"><?php echo $row['username'] ?></option>
		<?php } ?>
		</select>
       </td>
   </tr>

   <!-- Button Save -->
   <tr>
	   <td class="center" colspan="2">
			<input type="submit" class="button" value="<?php echo plugin_lang_get( 'save' ) ?>" />
	   </td>
   </tr>

</table>
</div>
</div>
</div>

</div>
</fieldset>

</div>
</div>
</div>

</form>

<h3 id="cron"><?php echo plugin_lang_get( 'using_cron' ) ?></h3>
<p><?php echo plugin_lang_get( 'config_cron' ) ?><br />
<pre><?php echo plugin_lang_get( 'cron_line' );  echo realpath(dirname(__FILE__).'/../TriggerCloseCli.php') ?></pre><br />
<?php echo plugin_lang_get( 'cron_help' ) ?>

<?php
layout_page_end();
