<?php
/**
 * Plugin Name: Killbait URL Sender
 * Description: Envía automáticamente los posts publicados a https://killbait.com
 * Version: 1.0
 * Author: KillBait
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Agregar menú de configuración
add_action('admin_menu', 'clickbait_validator_menu');
function clickbait_validator_menu() {
    add_options_page('Killbait URL Sender Settings', 'Killbait URL Sender', 'manage_options', 'clickbait-validator', 'clickbait_validator_settings_page');
}

// Página de configuración
function clickbait_validator_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración de Killbait URL Sender</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('clickbait_validator_settings_group');
            do_settings_sections('clickbait-validator');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registrar opciones
add_action('admin_init', 'clickbait_validator_settings_init');
function clickbait_validator_settings_init() {
    register_setting('clickbait_validator_settings_group', 'clickbait_validator_apikey');
    register_setting('clickbait_validator_settings_group', 'clickbait_validator_language');

    add_settings_section('clickbait_validator_section', 'Configuración General', null, 'clickbait-validator');

    add_settings_field('clickbait_validator_apikey', 'API Key', 'clickbait_validator_apikey_callback', 'clickbait-validator', 'clickbait_validator_section');
    add_settings_field('clickbait_validator_language', 'Idioma', 'clickbait_validator_language_callback', 'clickbait-validator', 'clickbait_validator_section');
}

// Callback para API Key
function clickbait_validator_apikey_callback() {
    $apikey = get_option('clickbait_validator_apikey', '');
    echo "<input type='text' name='clickbait_validator_apikey' value='$apikey' class='regular-text'>";
}

// Callback para selección de idioma
function clickbait_validator_language_callback() {
    $language = get_option('clickbait_validator_language', 'es');
    echo "<select name='clickbait_validator_language'>
            <option value='es' " . selected($language, 'es', false) . ">Español</option>
            <option value='en' " . selected($language, 'en', false) . ">Inglés</option>
          </select>";
}

// Modificar la función de validación para usar las opciones guardadas
add_action('save_post', 'validate_post_url_on_publish', 10, 2);
function validate_post_url_on_publish($ID, $post) {
	
	error_log('Se está ejecutando validate_post_url_on_publish');
	error_log('API Key: ' . $apikey);
	error_log('Post URL: ' . $post_url);
	
    $api_url = 'https://server1.killbait.com/api/validateURL';
    $apikey = get_option('clickbait_validator_apikey', '');
    $language = get_option('clickbait_validator_language', 'es');
    
    if (empty($apikey)) {
        error_log('Error: API Key no configurada en Killbait URL Sender.');
        return;
    }
    
    $post_url = get_permalink($ID);
    $args = array(
        'body' => json_encode(array(
            'url' => $post_url,
            'language' => $language,
            'apikey' => $apikey,
        )),
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'timeout' => 10
    );

    $response = wp_remote_post($api_url, $args);
	
	if (is_wp_error($response)) {
    error_log('Error en la solicitud API: ' . $response->get_error_message());
} else {
    error_log('Respuesta API: ' . wp_remote_retrieve_body($response));
}

    if (is_wp_error($response)) {
        error_log('Error en la validación de Clickbait: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    if (preg_match('/^[a-f0-9\-]{36}$/i', $body)) {
        update_post_meta($ID, '_clickbait_validation_id', $body);
    } else {
        $data = json_decode($body, true);
        if (isset($data['status'])) {
            update_post_meta($ID, '_clickbait_validation_result', $data);
        }
    }
}
