<?php
/**
 * Plugin Name: KillBait URL Sender
 * Version: 1.0
 * Description: Easily send URLs to Killbait news aggregator for selected categories and languages. 
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Plugin URI: https://github.com/killbaitnews/killbait_url_sender/
 * Author: KilLBait
 * Author URI: https://killbait.com
 * Text Domain: killbait-url-sender
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


if (!defined('ABSPATH')) {
    exit; 
}

// Config menu
if (!function_exists('killbait_menu')) {
	add_action('admin_menu', 'killbait_menu');
	function killbait_menu() {
		add_options_page('KillBait URL Sender Settings', 'KillBait URL Sender', 'manage_options', 'killbait', 'killbait_settings_page');
	}
}

// Config page
if (!function_exists('killbait_settings_page')) {
	function killbait_settings_page() {
		?>
		<div class="wrap">
			<h1>KillBait URL Sender configuration</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('killbait_settings_group');
				do_settings_sections('killbait');
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

// Register options
if (!function_exists('killbait_settings_init')) {
	add_action('admin_init', 'killbait_settings_init');
	function killbait_settings_init() {
		
	register_setting('killbait_settings_group', 'killbait_apikey', [
		'sanitize_callback' => 'sanitize_text_field'
	]);

	register_setting('killbait_settings_group', 'killbait_language', [
		'sanitize_callback' => 'sanitize_text_field'
	]);

	register_setting('killbait_settings_group', 'killbait_categories', [
		'sanitize_callback' => 'killbait_sanitize_categories'
	]);


		add_settings_section('killbait_section', 'General configuration', null, 'killbait');

		add_settings_field('killbait_apikey', 'API Key', 'killbait_apikey_callback', 'killbait', 'killbait_section');
		add_settings_field('killbait_language', 'Language', 'killbait_language_callback', 'killbait', 'killbait_section');
		add_settings_field('killbait_categories', 'Categories', 'killbait_categories_callback', 'killbait', 'killbait_section');
	}
}


// Callback for api key
if (!function_exists('killbait_apikey_callback')) {
	function killbait_apikey_callback() {
		$apikey = get_option('killbait_apikey', '');
		echo "<input type='text' name='killbait_apikey' value='" . esc_attr($apikey) . "' class='regular-text'>";
		echo "<p><a href='https://killbait.com/api/doc' target='_blank'>" . esc_html__('Get your API Key', 'killbait-url-sender') . "</a></p>";
	}
}

// Callback for language
if (!function_exists('killbait_language_callback')) {
	function killbait_language_callback() {
		$language = get_option('killbait_language', 'es');
		echo "<select name='killbait_language'>
				<option value='en' " . esc_attr(selected($language, 'en', false)) . ">" . esc_html__('English', 'killbait-url-sender') . "</option>
				<option value='es' " . esc_attr(selected($language, 'es', false)) . ">" . esc_html__('Spanish', 'killbait-url-sender') . "</option>
			  </select>";
				
	}
}

// Callback for categories
if (!function_exists('killbait_categories_callback')) {
	function killbait_categories_callback() {
		$selected_categories = get_option('killbait_categories', array()); 
		
		if (!is_array($selected_categories)) {
			$selected_categories = array(); 
		}

		$categories = get_categories(array('hide_empty' => false)); 

		echo '<select name="killbait_categories[]" multiple="multiple" class="regular-text" style="height: 150px;">';
		foreach ($categories as $category) {
			$selected = in_array($category->term_id, $selected_categories) ? 'selected' : '';
			echo "<option value='" . esc_attr($category->term_id) . "' " . esc_attr($selected) . ">" . esc_html($category->name) . "</option>";
		}
		echo '</select><p><em>If no category is selected, the plugin will apply to all published posts.</em></p>';
	}
}





// Send post url to KillBait on publish
if (!function_exists('killbait_validate_post_url_on_publish')) {
	add_action('transition_post_status', 'killbait_validate_post_url_on_publish', 10, 3);
	function killbait_validate_post_url_on_publish($new_status, $old_status, $post) {
		if (!isset($new_status) || !isset($old_status) || !$post || !isset($post->ID)) {
			error_log('KILLBAIT Error: No valid parameters at killbait_validate_post_url_on_publish.');
			return;
		}

		if ($old_status !== 'publish' && $new_status === 'publish') 
		{
			$apikey = get_option('killbait_apikey');

			if (function_exists('pll_get_post_language')) 
				$language = pll_get_post_language($post->ID, 'slug'); // 'es', 'en', etc.
			else 
				$language = get_option('killbait_language', 'en'); // Fallback if Polylang is not active

			if (!in_array($language, ['es', 'en'])) {
				$language = 'en';
			}
		
			$post_url = get_permalink($post->ID);

			$selected_categories = get_option('killbait_categories', array()); 

			if (empty($apikey) || empty($language) || empty($post_url) || empty($selected_categories)) {
				error_log('KILLBAIT Error: Missing ApiKey, language, categories or Post URL.');
				return;
			}

			$post_categories = wp_get_post_categories($post->ID);

			$intersection = array_intersect($post_categories, $selected_categories);
			if (!empty($selected_categories) && empty($intersection)) {
				error_log('KILLBAIT Error: Post does not belong to any selected category.');
				return;
			}

			$body = http_build_query(array(
				'apikey'   => $apikey,
				'url'      => $post_url,
				'language' => $language
			));

			$response = wp_remote_post('https://server1.killbait.com/api/public/queueURL', array(
				'body'    => $body,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'timeout' => 10
			));

			if (is_wp_error($response)) {
				error_log('KILLBAIT API Error: ' . $response->get_error_message());
				return;
			}

			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			error_log('KILLBAIT API response code: ' . intval($response_code) . '.');
			error_log('KILLBAIT API response body: ' . sanitize_text_field($response_body) . '.');
			
		}
	}
}

//Sanitize categories
if (!function_exists('killbait_sanitize_categories')) {
	function killbait_sanitize_categories($input) {
		if (!is_array($input)) {
			return [];
		}

		return array_map('intval', $input);
	}
}