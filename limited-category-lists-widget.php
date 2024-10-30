<?php
/*
Plugin Name: Limited Category Lists Widget
Description: Limited Category Lists Widget is a wordPress widget, lists the limited category as shown in the name. 
Author: Tomoya Otake
Version: 0.1
Author URI: http://www.jaco-bass.com/
Plugin URI: http://www.jaco-bass.com/blog/2007/09/limited-category-lists-widget/
*/

//----------------------------------------------------------------------------
//MAIN WIDGET BODY
//----------------------------------------------------------------------------

// We're putting the plugin's functions in one big function we then
// call at 'plugins_loaded' (add_action() at bottom) to ensure the
// required Sidebar Widget functions are available.
function widget_limited_catlists_init() {

	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return; // ...and if not, exit gracefully from the script.

	// This function prints the sidebar widget--the cool stuff!
	function widget_limited_catlists($args, $number = 1) {
		if ( $output = wp_cache_get('widget_limited_catlists') )
			return print($output);

		ob_start();
		// $args is an array of strings which help your widget
		// conform to the active theme: before_widget, before_title,
		// after_widget, and after_title are the array keys.
		extract($args);

		// Collect our widget's options, or define their defaults.
		$options = get_option('widget_limited_catlists');

		$title = empty($options[$number]['title']) ? __('Recent Posts') : $options[$number]['title'];
		$category = (int) empty($options[$number]['category']) ? 1 : $options[$number]['category'];
		if ( !$limit = (int) $options[$number]['limit'] )
			$limit = 10;
		else if ( $limit < 1 )
			$limit = 1;
		else if ( $limit > 15 )
			$limit = 15;

		$r = new WP_Query("showposts=$limit&what_to_show=posts&nopaging=0&cat=$category");
		if ($r->have_posts()) :
?>
			<?php echo $before_widget; ?>
				<?php echo $before_title . $title . $after_title; ?>
				<ul>
				<?php  while ($r->have_posts()) : $r->the_post(); ?>
				<li><a href="<?php the_permalink() ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
				<?php endwhile; ?>
				</ul>
			<?php echo $after_widget; ?>
	<?php
		endif;
		wp_cache_add('widget_limited_catlists', ob_get_flush());
        }

	function wp_flush_widget_limited_catlists() {
		wp_cache_delete('widget_limited_catlists');
	}

	add_action('save_post', 'wp_flush_widget_limited_catlists');
	add_action('post_deleted', 'wp_flush_widget_limited_catlists');

	function widget_limited_catlists_control($number) {

		// Collect our widget's options.
		$options = $newoptions = get_option('widget_limited_catlists');

		// This is for handing the control form submission.
		if ( $_POST["limited_catlists-submit-$number"] ) {
			$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["limited_catlists-title-$number"]));
			$newoptions[$number]['category'] = (int) ($_POST["limited_catlists-category-$number"]);
			$newoptions[$number]['limit'] = (int) ($_POST["limited_catlists-limit-$number"]);
		}

		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_limited_catlists', $options);
			wp_flush_widget_limited_catlists();
		}

		$title = attribute_escape($options[$number]['title']);
		if ( !$category = (int) $options[$number]['category'] )
			$category = 1;
		if ( !$limit = (int) $options[$number]['limit'] )
			$limit = 5;

// The HTML below is the control form for editing options.

?>

		<p><label for="limited_catlists-title-<?php echo "$number"; ?>"><?php _e('Title:'); ?> <input style="width: 250px;" id="limited_catlists-title-<?php echo "$number"; ?>" name="limited_catlists-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		<p><label for="limited_catlists-category-<?php echo "$number"; ?>"><?php _e('Category ID:'); ?> <input style="width: 25px; text-align: center;" id="limited_catlists-category-<?php echo "$number"; ?>" name="limited_catlists-category-<?php echo "$number"; ?>" type="text" value="<?php echo $category; ?>" /></label></p>
		<p><label for="limited_catlists-limit-<?php echo "$number"; ?>"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="limited_catlists-limit-<?php echo "$number"; ?>" name="limited_catlists-limit-<?php echo "$number"; ?>" type="text" value="<?php echo $limit; ?>" /></label> <?php _e('(at most 15)'); ?></p>
		<input type="hidden" id="limited_catlists-submit-<?php echo "$number"; ?>" name="limited_catlists-submit-<?php echo "$number"; ?>" value="1" />


<?php
	}
	// Tell Dynamic Sidebar about our new widget and its control
	widget_limited_catlists_register();
}

//----------------------------------------------------------------------------
//MULTIPLE WIDGET HANDLING
//----------------------------------------------------------------------------

function widget_limited_catlists_setup() {
	$options = $newoptions = get_option('widget_limited_catlists');
	if ( isset($_POST['limited_catlists-number-submit']) ) {
		$number = (int) $_POST['limited_catlists-number'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['number'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_limited_catlists', $options);
		widget_limited_catlists_register($options['number']);
	}
}

function widget_limited_catlists_page() {
	$options = $newoptions = get_option('widget_limited_catlists');
?>
	<div class="wrap">
		<form method="POST">
		<h2>Limited Category Lists Widgets</h2>
		<p style="line-height: 30px;"><?php _e('How many Limited Category Lists widgets would you like?'); ?>
		<select id="limited_catlists-number" name="limited_catlists-number" value="<?php echo $options['number']; ?>">
<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
		</select>
		<span class="submit"><input type="submit" name="limited_catlists-number-submit" id="limited_catlists-number-submit" value="<?php _e('Save'); ?>" /></span></p>
		</form>
	</div>
<?php
}

function widget_limited_catlists_register() {
	$options = get_option('widget_limited_catlists');
	$number = $options['number'];
	if ( $number < 1 ) $number = 1;
	if ( $number > 9 ) $number = 9;
	for ($i = 1; $i <= 9; $i++) {
		$name = array('Limited CatLists %s', null, $i);
		register_sidebar_widget($name, $i <= $number ? 'widget_limited_catlists' : /* unregister */ '', $i);
		register_widget_control($name, $i <= $number ? 'widget_limited_catlists_control' : /* unregister */ '', 90, 300, $i);
	}
	add_action('sidebar_admin_setup', 'widget_limited_catlists_setup');
	add_action('sidebar_admin_page', 'widget_limited_catlists_page');
}

//----------------------------------------------------------------------------
//HOOK IN
//----------------------------------------------------------------------------

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('plugins_loaded', 'widget_limited_catlists_init');

?>
