<?php
/*
Plugin Name: TeleWoo
Description: Sends published products to a Telegram channel with image and info.
*/

// Register settings
add_action('admin_init', 'custom_telegram_messages_settings');

function custom_telegram_messages_settings() {
    register_setting('custom_telegram_messages_group', 'custom_message');
    register_setting('custom_telegram_messages_group', 'show_image');
    register_setting('custom_telegram_messages_group', 'inline_button_text');
    register_setting('custom_telegram_messages_group', 'telegram_bot_api');
    register_setting('custom_telegram_messages_group', 'telegram_channel_username');
    register_setting('custom_telegram_messages_group', 'send_on_create'); // New setting for sending on create
}

// Hook into the save_post action
add_action('save_post', 'telegram_send_product_to_channel');

function telegram_send_product_to_channel($post_id) {
    // Check if the post is a product and the checkbox is checked for sending on create
    if (get_post_type($post_id) === 'product' && get_option('send_on_create', false) && wp_is_post_revision($post_id)) {
        return; // Exit if the post is an update (revision)
    }

    $product = get_post($post_id);

    // Get the custom message from options
    $custom_message = get_option('custom_message');

    // Replace shortcodes with dynamic content
    $replacements = array(
        '{post_title}' => $product->post_title,
        '{ID}' => $post_id,
        '{terms:product_cat}' => implode(', ', wp_get_post_terms($post_id, 'product_cat', array('fields' => 'names'))),
        '{cf:description}' => get_post_meta($post_id, 'description', true),
        '{cf:_regular_price}' => get_post_meta($post_id, '_regular_price', true),
    );

    // Replace custom field shortcodes
    $custom_fields = get_post_custom($post_id);
    foreach ($custom_fields as $key => $value) {
        $replacements['{cf:' . $key . '}'] = $value[0];
    }

    $message = str_replace(array_keys($replacements), array_values($replacements), $custom_message);

    // Get Bot API and Channel Username from options
    $telegram_bot_api = get_option('telegram_bot_api');
    $telegram_channel_username = get_option('telegram_channel_username');

    // Set up your Telegram Bot API parameters
    $chat_id = get_channel_id_from_username($telegram_bot_api, $telegram_channel_username);

    if (!$chat_id) {
        return; // Exit if channel ID cannot be determined
    }

    // Check if the option to show product image is enabled
    $show_image = get_option('show_image', 0);
    $image_url = '';
    if ($show_image) {
        $image_url = get_the_post_thumbnail_url($post_id, 'full');
    }

    // Get inline button text from options
    $inline_button_text = get_option('inline_button_text', '📦 See The Product'); // Default text

    // Inline keyboard markup
    $keyboard = array(
        'inline_keyboard' => array(
            array(
                array(
                    'text' => $inline_button_text,
                    'url' => get_permalink($post_id),
                ),
            ),
        ),
    );

    // Encode the keyboard markup as JSON
    $keyboard_json = json_encode($keyboard);

    // Send message to Telegram channel
    $telegram_url = "https://api.telegram.org/bot{$telegram_bot_api}/sendPhoto"; // Change to sendPhoto method
    $response = wp_remote_post($telegram_url, array(
        'body' => array(
            'chat_id' => $chat_id,
            'photo' => $image_url, // Use 'photo' parameter instead of 'text'
            'caption' => $message, // Caption for the image
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard_json,
        ),
    ));
}

// Function to get the channel ID from the username
function get_channel_id_from_username($bot_api, $channel_username) {
    $getChatUrl = "https://api.telegram.org/bot{$bot_api}/getChat?chat_id=@{$channel_username}";
    $response = wp_remote_get($getChatUrl);

    if (is_wp_error($response)) {
        return false;
    }

    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);

    if (isset($data['ok']) && $data['ok'] === true) {
        return $data['result']['id'];
    }

    return false;
}

// Callback function to render admin page
function custom_telegram_messages_page() {
    ?>
    <div class="wrap">
        <h1>Custom Telegram Messages</h1>
        <form method="post" action="options.php">
            <?php settings_fields('custom_telegram_messages_group'); ?>
            <?php do_settings_sections('custom-telegram-messages'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Compose Custom Message</th>
                    <td>
                        <textarea name="custom_message" cols="50" rows="10"><?php echo esc_textarea(get_option('custom_message')); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Status</th>
                    <td>
                        <label for="show_image">
                            <input type="checkbox" id="show_image" name="show_image" value="1" <?php checked(get_option('show_image', 0), 1); ?>>
                            Enable
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Inline Keyboard Button Text</th>
                    <td>
                        <input type="text" name="inline_button_text" value="<?php echo esc_attr(get_option('inline_button_text', '📦 See The Product')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Bot API</th>
                    <td>
                        <input type="text" name="telegram_bot_api" value="<?php echo esc_attr(get_option('telegram_bot_api')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Channel Username</th>
                    <td>
                        <input type="text" name="telegram_channel_username" value="<?php echo esc_attr(get_option('telegram_channel_username')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Send post only when created (not updated)</th>
                    <td>
                        <label for="send_on_create">
                            <input type="checkbox" id="send_on_create" name="send_on_create" value="1" <?php checked(get_option('send_on_create', false), 1); ?>>
                            Check this box to send the post only when created and not updated.
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Shortcode Placeholders</th>
                    <td>
                        <p>Use the following placeholders in your custom message:</p>
                        <ul>
                            <li>{post_title} - Replaced with the post title.</li>
                            <li>{ID} - Replaced with the post ID.</li>
                            <li>{terms:product_cat} - Replaced with the post category.</li>
                            <li>{cf:description} - Replaced with the custom field "description".</li>
                            <li>{cf:_regular_price} - Replaced with the custom field "_regular_price".</li>
                            <li>{cf:custom_field_name} - Replaced with the value of the specified custom field.</li>
                        </ul>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Create dashboard menu page
add_action('admin_menu', 'custom_telegram_messages_menu');

function custom_telegram_messages_menu() {
    add_menu_page(
        'TeleWoo - Custom Telegram Messages', // Page title
        'TeleWoo',                           // Menu title
        'manage_options',                    // Capability
        'custom-telegram-messages',          // Menu slug
        'custom_telegram_messages_page',     // Callback function
        'fa-brands fa-telegram',             // FontAwesome icon class
        30                                  // Position in the menu
    );

    // Load FontAwesome in WordPress admin
    add_action('admin_enqueue_scripts', 'load_fontawesome');

    function load_fontawesome() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
    }
}
