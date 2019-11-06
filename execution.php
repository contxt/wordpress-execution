<?php

/*
 * Plugin Name: Execution
 * Description: Shows the time to process pages in the administration section of Wordpress.
 * Author: contxt
 * Version: 1.0
 * Text Domain: execution
 * Domain Path: /languages/
 *
 * @package execution
*/

//Options
add_action('admin_menu', function () {
	add_options_page('Execution settings', 'Execution', 'manage_options', 'execution', 'execution_options_page');
});

add_action('admin_init', function () {
	register_setting('execution_options_page', 'slow');
});

function execution_options_page() {
?>
<div class="wrap">
 <h1>Execution Settings</h1>
<form action="options.php" method="post">
 <?php
settings_fields('execution_options_page');
do_settings_sections('execution_options_page'); ?>
<table class="form-table">
<tbody>
	<tr>
		<th>
			<label for="threshold" title="e.g. 100ms">
				Page load threshold
			</label>
		</th>
	<td>
		<input type="number" min="50" name="slow"
		value="<?php echo esc_attr(get_option('slow', 200)); ?>"
		step="50" class="small-text">
		</td>
	</tr>
</tbody>
</table>
 <?php submit_button(); ?>
</form>
</div>
 <?php
}

// Get languages
function execution_load_textdomain() {
	load_plugin_textdomain('execution', false, basename(dirname(__FILE__)).'/languages/');
}

// Load styles
function plugin_styles() {
	wp_register_style('execution_style', plugin_dir_url(__FILE__).'execution.css');
	wp_enqueue_style('execution_style');
}

// Stop timer
function page_load_end() {
	return microtime(true);
}

// Load information to admin bar
function add_menu() {
	global $wp_admin_bar, $timestart;
	$time_limit = !get_option('slow') > 0 ? 200 : get_option('slow');
	$execution_text = __('Execution time', 'execution');
	$timeframe = 'ms';
	$total_time = floor((page_load_end() - $timestart) * 1000);
	$heavy = $total_time > $time_limit ? 'execution-warning' : '';
	if ($total_time > 1000) {
		$total_time = number_format($total_time / 1000, 2);
		$timeframe = 's';
	}
	$execution_href = 'javascript:location.assign(location)';
	$execution_divider = '&nbsp;:&nbsp;';
	$total_time = <<<execution
	<style>
	@media screen and (max-width: 782px) {
		#wpadminbar #wp-admin-bar-execution .ab-icon:before {
			content: "{$total_time}{$timeframe}";
			font-weight: bolder;
		}
	}
	</style>
	<span class="ab-icon {$heavy}"></span>
	<span class="ab-label">{$execution_text}{$execution_divider}</span>
	<span class="execution-full {$heavy}">{$total_time}{$timeframe}</span>
execution;
	$args = array(
		'id' => 'execution',
		'title' => $total_time,
		'href' => $execution_href,
	);
	$wp_admin_bar->add_node($args);
}

// Load Wordpress actions if the user is an administrator
if(!function_exists('wp_get_current_user')) {
	include(ABSPATH . "wp-includes/pluggable.php"); 
}
if(current_user_can('administrator')) {
	add_action('plugins_loaded', 'execution_load_textdomain');
	add_action('admin_enqueue_scripts', 'plugin_styles');
	add_action('wp_enqueue_scripts', 'plugin_styles');
	add_action('admin_footer', 'page_load_end');
	add_action('admin_bar_menu', 'add_menu', 999);
}
