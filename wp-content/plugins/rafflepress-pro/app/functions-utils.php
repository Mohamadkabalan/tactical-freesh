<?php

/**
 *  Get IP
 */
function rafflepress_pro_get_ip()
{
    $ip = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) and strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 6) {
        $ip = strip_tags($_SERVER['HTTP_X_FORWARDED_FOR']);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP']) and strlen($_SERVER['HTTP_CLIENT_IP']) > 6) {
        $ip = strip_tags($_SERVER['HTTP_CLIENT_IP']);
    } elseif (!empty($_SERVER['REMOTE_ADDR']) and strlen($_SERVER['REMOTE_ADDR']) > 6) {
        $ip = strip_tags($_SERVER['REMOTE_ADDR']);
    }//endif
    if (!$ip) {
        $ip = '127.0.0.1';
    }
    return strip_tags($ip);
}

/**
 *  Get IP
 */
function rafflepress_pro_convert_string_to_boolean(&$value, $key)
{
    if ($value == 'false' || $value == 'true') {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

/**
 * Get Enviroment
 */
function rafflepress_pro_is_localhost()
{
    // $localhost = array('127.0.0.1','::1');

    // $is_localhost = false;
    // if (in_array($_SERVER['REMOTE_ADDR'], $localhost) || !empty($_GET['debug'])) {
    //     $is_localhost = true;
    // }
    $is_localhost = false;
    if (defined('RAFFLEPRESS_LOCAL_JS')) {
        $is_localhost = true;
    }

    return $is_localhost;
}

/**
 * Entry Options
 */
function rafflepress_pro_entry_options()
{
    $entry_options = array(
        'visit-fb' => array(
            'name' => __('Visit us on Facebook', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'subscribers',
        ), 'facebook-like-share' => array(
            'name' => __('Like our Page', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'subscribers',
        ),

        'twitter-follow' => array(
            'name' => __('Follow us on Twitter', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'subscribers',
        ), 'instagram-follow' => array(
            'name' => __('Visit us on Instagram', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'subscribers',
        ), 'pinterest-follow' => array(
            'name' => __('Follow us on Pinterest', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'subscribers',
        ), 'linkedin-follow' => array(
            'name' => __('Follow us on LinkedIn', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'subscribers',
        ), 'twitch-follow' => array(
            'name' => __('Follow us on Twitch', 'rafflepress'), 'is_pro' => true, 'cat' => 'subscribers',
        ), 'youtube-follow' => array(
            'name' => __('Visit a YouTube Channel', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'subscribers',
        ), 'join-newsletter' => array(
            'name' => __('Join an Email Newsletter', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'subscribers',
        ),

        'refer-a-friend' => array(
            'name' => __('Refer a Friend - Viral', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'engagement',
        ), 'fb-page-post' => array(
            'name' => __('View Facebook Post / Video', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'engagement',
        ),

        'instagram-page-post' => array(
            'name' => __('View Instagram Post / Video', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'engagement',
        ), 'watch-a-video' => array(
            'name' => __('Watch a Video', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'engagement',
        ), 'automatic-entry' => array(
            'name' => __('Automatic Entry', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'engagement',
        ),

        'tweet' => array(
            'name' => __('Tweet a Message', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'traffic',
        ), 'facebook-share' => array(
            'name' => __('Share on Facebook', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'traffic',
        ), 'visit-a-page' => array(
            'name' => __('Visit a Page', 'rafflepress-pro'), 'is_pro' => false, 'cat' => 'traffic',
        ), 'linkedin-share' => array(
            'name' => __('Share on LinkedIn', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'question' => array(
            'name' => __('Answer a Question', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'polls-surveys' => array(
            'name' => __('Polls & Surveys', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'submit-image' => array(
            'name' => __('Submit an Image', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'invent-your-own' => array(
            'name' => __('Invent Your Own', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'comment' => array(
            'name' => __('Leave a Comment', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'blogpost' => array(
            'name' => __('Write a Blog Post', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'rss' => array(
            'name' => __('Subscribe to RSS Feed', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ), 'podcast' => array(
            'name' => __('Subscribe to Podcast', 'rafflepress-pro'), 'is_pro' => true, 'cat' => 'traffic',
        ),
    );

    return $entry_options;
}


/**
 * Get times
 */
function rafflepress_pro_get_default_timezone()
{
    $general_settings = get_option('rafflepress_settings');
    $timezone = 'UTC';

    if (!empty($general_settings)) {
        $general_settings = json_decode($general_settings);
        if (!empty($general_settings->default_timezone)) {
            $timezone = $general_settings->default_timezone;
        }
    }

    return $timezone;
}


/**
 * Get times
 */
function rafflepress_pro_get_times()
{
    $times = array();
    $times[] = array(
        'v' => '', 'l' => __('Select Time', 'rafflepress-pro'),
    );
    $times[] = array(
        'v' => '00:00', 'l' => '12:00 A.M.',
    );
    $times[] = array(
        'v' => '00:30', 'l' => '12:30 A.M.',
    );
    $times[] = array(
        'v' => '01:00', 'l' => '1:00 A.M.',
    );
    $times[] = array(
        'v' => '01:30', 'l' => '1:30 A.M.',
    );
    $times[] = array(
        'v' => '02:00', 'l' => '2:00 A.M.',
    );
    $times[] = array(
        'v' => '02:30', 'l' => '2:30 A.M.',
    );
    $times[] = array(
        'v' => '03:00', 'l' => '3:00 A.M.',
    );
    $times[] = array(
        'v' => '03:30', 'l' => '3:30 A.M.',
    );
    $times[] = array(
        'v' => '04:00', 'l' => '4:00 A.M.',
    );
    $times[] = array(
        'v' => '04:30', 'l' => '4:30 A.M.',
    );
    $times[] = array(
        'v' => '05:00', 'l' => '5:00 A.M.',
    );
    $times[] = array(
        'v' => '05:30', 'l' => '5:30 A.M.',
    );
    $times[] = array(
        'v' => '06:00', 'l' => '6:00 A.M.',
    );
    $times[] = array(
        'v' => '06:30', 'l' => '6:30 A.M.',
    );
    $times[] = array(
        'v' => '07:00', 'l' => '7:00 A.M.',
    );
    $times[] = array(
        'v' => '07:30', 'l' => '7:30 A.M.',
    );
    $times[] = array(
        'v' => '08:00', 'l' => '8:00 A.M.',
    );
    $times[] = array(
        'v' => '08:30', 'l' => '8:30 A.M.',
    );
    $times[] = array(
        'v' => '09:00', 'l' => '9:00 A.M.',
    );
    $times[] = array(
        'v' => '09:30', 'l' => '9:30 A.M.',
    );
    $times[] = array(
        'v' => '10:00', 'l' => '10:00 A.M.',
    );
    $times[] = array(
        'v' => '10:30', 'l' => '10:30 A.M.',
    );
    $times[] = array(
        'v' => '11:00', 'l' => '11:00 A.M.',
    );
    $times[] = array(
        'v' => '11:30', 'l' => '11:30 A.M.',
    );
    $times[] = array(
        'v' => '12:00', 'l' => '12:00 P.M.',
    );
    $times[] = array(
        'v' => '12:30', 'l' => '12:30 P.M.',
    );
    $times[] = array(
        'v' => '13:00', 'l' => '1:00 P.M.',
    );
    $times[] = array(
        'v' => '13:30', 'l' => '1:30 P.M.',
    );
    $times[] = array(
        'v' => '14:00', 'l' => '2:00 P.M.',
    );
    $times[] = array(
        'v' => '14:30', 'l' => '2:30 P.M.',
    );
    $times[] = array(
        'v' => '15:00', 'l' => '3:00 P.M.',
    );
    $times[] = array(
        'v' => '15:30', 'l' => '3:30 P.M.',
    );
    $times[] = array(
        'v' => '16:00', 'l' => '4:00 P.M.',
    );
    $times[] = array(
        'v' => '16:30', 'l' => '4:30 P.M.',
    );
    $times[] = array(
        'v' => '17:00', 'l' => '5:00 P.M.',
    );
    $times[] = array(
        'v' => '17:30', 'l' => '5:30 P.M.',
    );
    $times[] = array(
        'v' => '18:00', 'l' => '6:00 P.M.',
    );
    $times[] = array(
        'v' => '18:30', 'l' => '6:30 P.M.',
    );
    $times[] = array(
        'v' => '19:00', 'l' => '7:00 P.M.',
    );
    $times[] = array(
        'v' => '19:30', 'l' => '7:30 P.M.',
    );
    $times[] = array(
        'v' => '20:00', 'l' => '8:00 P.M.',
    );
    $times[] = array(
        'v' => '20:30', 'l' => '8:30 P.M.',
    );
    $times[] = array(
        'v' => '21:00', 'l' => '9:00 P.M.',
    );
    $times[] = array(
        'v' => '21:30', 'l' => '9:30 P.M.',
    );
    $times[] = array(
        'v' => '22:00', 'l' => '10:00 P.M.',
    );
    $times[] = array(
        'v' => '22:30', 'l' => '10:30 P.M.',
    );
    $times[] = array(
        'v' => '23:00', 'l' => '11:00 P.M.',
    );
    $times[] = array(
        'v' => '23:30', 'l' => '11:30 P.M.',
    );

    return $times;
}

/**
 * Check per
 */
function rafflepress_pro_get_api_key()
{
    $rafflepress_api_key = '';

    if (defined('RAFFLEPRESS_API_KEY')) {
        $rafflepress_api_key = RAFFLEPRESS_API_KEY;
    }

    if (empty($rafflepress_api_key)) {
        $rafflepress_api_key = get_option('rafflepress_api_key ');
    }

    return $rafflepress_api_key;
}

/**
 * Get timezones
 */
function rafflepress_pro_get_timezones()
{
    // timezones
    $zonen = array();
    $continents = array(
        'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'
    );

    foreach (timezone_identifiers_list() as $zone) {
        $zone = explode('/', $zone);
        if (!in_array($zone[0], $continents)) {
            continue;
        }

        // This determines what gets set and translated - we don't translate Etc/* strings here, they are done later
        $exists = array(
            0 => (isset($zone[0]) && $zone[0]), 1 => (isset($zone[1]) && $zone[1]), 2 => (isset($zone[2]) && $zone[2]),
        );
        $exists[3] = ($exists[0] && 'Etc' !== $zone[0]);
        $exists[4] = ($exists[1] && $exists[3]);
        $exists[5] = ($exists[2] && $exists[3]);

        $zonen[] = array(
            'continent' => ($exists[0] ? $zone[0] : ''), 'city' => ($exists[1] ? $zone[1] : ''),
            'subcity' => ($exists[2] ? $zone[2] : ''),
            't_continent' => ($exists[3] ? translate(str_replace('_', ' ', $zone[0]), 'continents-cities') : ''),
            't_city' => ($exists[4] ? translate(str_replace('_', ' ', $zone[1]), 'continents-cities') : ''),
            't_subcity' => ($exists[5] ? translate(str_replace('_', ' ', $zone[2]), 'continents-cities') : ''),
        );
    }
    usort($zonen, '_wp_timezone_choice_usort_callback');

    $structure = array();

    foreach ($zonen as $key => $zone) {
        // Build value in an array to join later
        $value = array($zone['continent']);

        if (empty($zone['city'])) {
            // It's at the continent level (generally won't happen)
            $display = $zone['t_continent'];
        } else {
            // It's inside a continent group

            // Continent optgroup
            if (!isset($zonen[$key - 1]) || $zonen[$key - 1]['continent'] !== $zone['continent']) {
                $label = $zone['t_continent'];
                //$structure[] = $label ;
            }

            // Add the city to the value
            $value[] = $zone['city'];

            // get offset
            // $timezone = $label.'/'.str_replace(' ', '_', $zone['t_city']);
            // $time = new \DateTime('now', new DateTimeZone($timezone));
            // $timezoneOffset = $time->format('P');

            $display = $zone['t_city'];
            if (!empty($zone['subcity'])) {
                // Add the subcity to the value
                $value[] = $zone['subcity'];
                $display .= ' - '.$zone['t_subcity'];
            }
        }

        // Build the value
        $value = join('/', $value);

        // get offset
        $time = new DateTime('now', new DateTimeZone($value));
        $timezoneOffset = $time->format('P');
        $structure[$label][] = array(
            'v' => $value, 'l' => $display.' ('.$timezoneOffset.' GMT)',
        );
    }

    $structure['UTC'][] = array(
        'v' => 'UTC', 'l' => 'UTC',
    );

    return $structure;
}

/**
 * Get Giveaway Slug
 */
function rafflepress_pro_get_slug()
{
    $settings = get_option('rafflepress_settings');
    $slug = 'rp';
    if (!empty($settings)) {
        $settings = json_decode($settings);
        if (!empty($settings->slug) && $settings->slug != 'rafflepress') {
            $slug = $settings->slug;
        }
    }

    return $slug;
}

/**
 * Add to array if value does not exist
 */
function rafflepress_pro_array_add($arr, $key, $value)
{
    if (!array_key_exists($key, $arr)) {
        $arr[$key] = $value;
    }
    return $arr;
}


/**
 * Multiple inserts
 */
function rafflepress_pro_wp_insert_rows(
    $row_arrays = array(),
    $wp_table_name = null,
    $update = false,
    $primary_key = null
) {
    global $wpdb;
    $wp_table_name = esc_sql($wp_table_name);
    // Setup arrays for Actual Values, and Placeholders
    $values = array();
    $place_holders = array();
    $query = '';
    $query_columns = '';

    $query .= "INSERT INTO `{$wp_table_name}` (";
    foreach ($row_arrays as $count => $row_array) {
        foreach ($row_array as $key => $value) {
            if ($count == 0) {
                if ($query_columns) {
                    $query_columns .= ', '.$key.'';
                } else {
                    $query_columns .= ''.$key.'';
                }
            }

            $values[] = $value;

            $symbol = '%s';
            if (is_numeric($value) && $key != 'action_id') {
                if (is_float($value)) {
                    $symbol = '%f';
                } else {
                    $symbol = '%d';
                }
            }
            if (isset($place_holders[$count])) {
                $place_holders[$count] .= ", '$symbol'";
            } else {
                $place_holders[$count] = "( '$symbol'";
            }
        }
        // mind closing the GAP
        $place_holders[$count] .= ')';
    }

    $query .= " $query_columns ) VALUES ";

    $query .= implode(', ', $place_holders);

    if ($update) {
        $update = " ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
        $cnt = 0;
        foreach ($row_arrays[0] as $key => $value) {
            if ($cnt == 0) {
                $update .= "$key=VALUES($key)";
                $cnt = 1;
            } else {
                $update .= ", $key=VALUES($key)";
            }
        }
        $query .= $update;
    }

    $sql = $wpdb->prepare($query, $values);
    if ($wpdb->query($sql)) {
        return true;
    } else {
        return false;
    }
}


/**
 * Check per
 */
function rafflepress_pro_cu($rper = null)
{
    if (!empty($rper)) {
        $uper = explode(',', get_option('rafflepress_per'));
        if (in_array($rper, $uper)) {
            return true;
        } else {
            return false;
        }
    } else {
        $a = get_option('rafflepress_a');
        if ($a) {
            return true;
        } else {
            return false;
        }
    }
}


function rafflepress_pro_upgrade_link($medium = 'link')
{
    return apply_filters('rafflepress_pro_upgrade_link',
        'https://rafflepress.com/lite-upgrade/?utm_source=WordPress&utm_campaign=liteplugin&utm_medium='.sanitize_key(apply_filters('rafflepress_pro_upgrade_link_medium',
            $medium)));
}


function rafflepress_pro_disable_admin_notices()
{
    global $wp_filter;
    if (is_user_admin()) {
        if (isset($wp_filter['user_admin_notices'])) {
            unset($wp_filter['user_admin_notices']);
        }
    } elseif (isset($wp_filter['admin_notices'])) {
        unset($wp_filter['admin_notices']);
    }
    if (isset($wp_filter['all_admin_notices'])) {
        unset($wp_filter['all_admin_notices']);
    }
}

if (!empty($_GET['page']) && strpos($_GET['page'], 'rafflepress') !== false) {
    add_action('admin_print_scripts', 'rafflepress_pro_disable_admin_notices');
}


/**
 * Install addon.
 * @since 1.0.0
 */
function rafflepress_pro_install_addon()
{
    // Run a security check.
    check_ajax_referer('rafflepress_pro_install_addon', 'nonce');

    // Check for permissions.
    if (!current_user_can(apply_filters('rafflepress_install_plugins_capability', 'install_plugins'))) {
        wp_send_json_error();
    }

    // Check DISALLOW_FILE_MODS constant
    if (defined('DISALLOW_FILE_MODS') && DISALLOW_FILE_MODS === true) {
        wp_send_json_error();
    }

    // Install the addon.
    if (isset($_POST['plugin'])) {
        $download_url = $_POST['plugin'];

        global $hook_suffix;

        // Set the current screen to avoid undefined notices.
        set_current_screen();

        // Prepare variables.
        $method = '';
        $url = add_query_arg(array(
                'page' => 'rafflepress_pro',
            ), admin_url('admin.php'));
        $url = esc_url($url);

        // Start output bufferring to catch the filesystem form if credentials are needed.
        ob_start();
        if (false === ($creds = request_filesystem_credentials($url, $method, false, false, null))) {
            $form = ob_get_clean();
            echo json_encode(array('form' => $form));
            wp_die();
        }

        // If we are not authenticated, make it happen now.
        if (!WP_Filesystem($creds)) {
            ob_start();
            request_filesystem_credentials($url, $method, true, false, null);
            $form = ob_get_clean();
            echo json_encode(array('form' => $form));
            wp_die();
        }

        // We do not need any extra credentials if we have gotten this far, so let's install the plugin.
        require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';

        if (version_compare($wp_version, '5.3.0') >= 0) {
            require_once RAFFLEPRESS_PRO_PLUGIN_PATH.'app/includes/skin53.php';
        } else {
            require_once RAFFLEPRESS_PRO_PLUGIN_PATH.'app/includes/skin.php';
        }

        // Create the plugin upgrader with our custom skin.
        $installer = new Plugin_Upgrader($skin = new RafflePress_Skin());
        $installer->install($download_url);

        // Flush the cache and return the newly installed plugin basename.
        wp_cache_flush();
        if ($installer->plugin_info()) {
            $plugin_basename = $installer->plugin_info();
            echo json_encode(array('plugin' => $plugin_basename));
            wp_die();
        }
    }

    // Send back a response.
    echo json_encode(true);
    wp_die();
}


/**
 * Deactivate addon.
 * @since 1.0.0
 */
function rafflepress_pro_deactivate_addon()
{
    // Run a security check.
    check_ajax_referer('rafflepress_pro_deactivate_addon', 'nonce');

    // Check for permissions.
    if (!current_user_can(apply_filters('rafflepress_activate_plugins_capability', 'activate_plugins'))) {
        wp_send_json_error();
    }

    $type = 'addon';
    if (!empty($_POST['type'])) {
        $type = sanitize_key($_POST['type']);
    }

    if (isset($_POST['plugin'])) {
        deactivate_plugins($_POST['plugin']);

        if ('plugin' === $type) {
            wp_send_json_success(esc_html__('Plugin deactivated.', 'rafflepress-pro'));
        } else {
            wp_send_json_success(esc_html__('Addon deactivated.', 'rafflepress-pro'));
        }
    }

    wp_send_json_error(esc_html__('Could not deactivate the addon. Please deactivate from the Plugins page.',
        'rafflepress-pro'));
}


/**
 * Activate addon.
 * @since 1.0.0
 */
function rafflepress_pro_activate_addon()
{
    // Run a security check.
    check_ajax_referer('rafflepress_pro_activate_addon', 'nonce');

    // Check for permissions.
    if (!current_user_can(apply_filters('rafflepress_activate_plugins_capability', 'activate_plugins'))) {
        wp_send_json_error();
    }

    if (isset($_POST['plugin'])) {
        $type = 'addon';
        if (!empty($_POST['type'])) {
            $type = sanitize_key($_POST['type']);
        }

        $activate = activate_plugins($_POST['plugin']);

        if (!is_wp_error($activate)) {
            if ('plugin' === $type) {
                wp_send_json_success(esc_html__('Plugin activated.', 'rafflepress-pro'));
            } else {
                wp_send_json_success(esc_html__('Addon activated.', 'rafflepress-pro'));
            }
        }
    }

    wp_send_json_error(esc_html__('Could not activate addon. Please activate from the Plugins page.',
        'rafflepress-pro'));
}

function rafflepress_pro_get_plugins_list()
{
    check_ajax_referer('rafflepress_pro_get_plugins_list', 'nonce');

    $am_plugins = array(
        'google-analytics-for-wordpress/googleanalytics.php' => 'monsterinsights',
        'optinmonster/optin-monster-wp-api.php' => 'optinmonster', 'wp-mail-smtp/wp_mail_smtp.php' => 'wpmailsmtp',
        'wpforms-lite/wpforms.php' => 'wpforms', 'coming-soon/coming-soon.php' => 'seedprod',
        'trustpulse-api/trustpulse.php' => 'trustpulse',
    );
    $all_plugins = get_plugins();

    $response = array();

    foreach ($am_plugins as $slug => $label) {
        if (array_key_exists($slug, $all_plugins)) {
            if (is_plugin_active($slug)) {
                $response[$label] = 'Active';
            } else {
                $response[$label] = 'Inactive';
            }
        } else {
            $response[$label] = 'Not Installed';
        }
    }

    wp_send_json($response);
}

function rafflepress_pro_plugin_nonce()
{
    check_ajax_referer('rafflepress_pro_plugin_nonce', 'nonce');

    if (!current_user_can(apply_filters('rafflepress_install_plugins_capability', 'install_plugins'))) {
        wp_send_json_error();
    }

    $install_plugin_nonce = wp_create_nonce('install-plugin_'.sanitize_text_field($_POST['plugin']));

    wp_send_json($install_plugin_nonce);
}

function rafflepress_pro_is_dev_url($url = '')
{
    $is_local_url = false;
    // Trim it up
    $url = strtolower(trim($url));
    // Need to get the host...so let's add the scheme so we can use parse_url
    if (false === strpos($url, 'http://') && false === strpos($url, 'https://')) {
        $url = 'http://'.$url;
    }
    $url_parts = parse_url($url);
    $host = !empty($url_parts['host']) ? $url_parts['host'] : false;
    if (!empty($url) && !empty($host)) {
        if (false !== ip2long($host)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $is_local_url = true;
            }
        } elseif ('localhost' === $host) {
            $is_local_url = true;
        }

        $tlds_to_check = array('.local', ':8888', ':8080', ':8081', '.invalid', '.example', '.test');
        foreach ($tlds_to_check as $tld) {
            if (false !== strpos($host, $tld)) {
                $is_local_url = true;
                break;
            }
        }
        if (substr_count($host, '.') > 1) {
            $subdomains_to_check = array('dev.', '*.staging.', 'beta.', 'test.');
            foreach ($subdomains_to_check as $subdomain) {
                $subdomain = str_replace('.', '(.)', $subdomain);
                $subdomain = str_replace(array('*', '(.)'), '(.*)', $subdomain);
                if (preg_match('/^('.$subdomain.')/', $host)) {
                    $is_local_url = true;
                    break;
                }
            }
        }
    }
    return $is_local_url;
}


if (!empty($_GET['rafflepress-preview']) || !empty($_GET['iframe']) || (!empty($_GET['context']) && $_GET['context'] == 'edit')) {
    add_action('init', 'rafflepress_pro_remove_ngg_print_scripts');
}
function rafflepress_pro_remove_ngg_print_scripts()
{
    if (class_exists('C_Photocrati_Resource_Manager')) {
        remove_all_actions('wp_print_footer_scripts', 1);
    }
}

function rafflepress_pro_custom_upload_dir($dir)
{
    // $dir_data already you might want to use
    $custom_dir = 'custom';
    return array(
            'path' => $dir['basedir'].'/rafflepress', 'url' => $dir['baseurl'].'/rafflepress',
            'subdir' => '/rafflepress',
        ) + $dir;
}


/**
 * Set time limit
 */

function rafflepress_pro_set_time_limit($limit = 0)
{

    if (function_exists('set_time_limit') && false === strpos(ini_get('disable_functions'),
            'set_time_limit') && !ini_get('safe_mode')) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
        @set_time_limit($limit); // @codingStandardsIgnoreLine
    }
}


/**
 * Create index.html file in the specified directory if it doesn't exist.
 */
function rafflepress_pro_create_index_html_file($path)
{

    if (!is_dir($path) || is_link($path)) {
        return false;
    }
    $index_file = wp_normalize_path(trailingslashit($path).'index.html');

    // Do nothing if index.html exists in the directory.
    if (file_exists($index_file)) {
        return false;
    }

    // Create empty index.html.
    return file_put_contents($index_file, ''); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * Create export direcory for tmp files.
 */
function rafflepress_pro_get_tmpdir()
{

    $upload_dir = wp_upload_dir();
    $export_path = trailingslashit(realpath($upload_dir['basedir'])).'rafflepressexport';

    if (!file_exists($export_path)) {
        wp_mkdir_p($export_path);
    }
    rafflepress_pro_create_index_html_file($export_path);

    $export_path = wp_normalize_path($export_path);
    return $export_path;
}

/**
 * create tmp files.
 */
function rafflepress_pro_get_tmpfname($tmpname)
{

    if (empty($tmpname)) {
        return '';
    }

    $export_dir = rafflepress_pro_get_tmpdir();
    $export_file = $export_dir.'/'.sanitize_key($tmpname);
    touch($export_file);

    return $export_file;

}


/*add_filter('woocommerce_payment_complete_order_status', 'tf_grant_rafflepress_entries', 10, 3);

function tf_grant_rafflepress_entries($payment_complete_status, $order_id, $order)
{

    $current_status = $order->get_status();

    // We to fire if it's coming from one of the following statuses:
    $allowed_current_statuses = array('on-hold', 'pending', 'failed');

    if ('processing' === $payment_complete_status && in_array($current_status, $allowed_current_statuses)) {
        global $wpdb;
        $entries_earned = 25;
        $tablename = $wpdb->prefix.'rafflepress_giveaways';
        $sql = "SELECT * FROM $tablename WHERE active = 1";
        $query = $wpdb->prepare($sql);
        $giveaways = $wpdb->get_results($query);
        foreach ($giveaways as $key => $giveaway) {
            $giveaway_id = $giveaway->id;
            $settings = json_decode($giveaway->settings, true);
            $entry_options = $settings['entry_options'];
            if (sizeof($entry_options)) {
                foreach ($entry_options as $index => $entry_option) {
                    if ($entry_option['type'] == 'invent-your-own') {
                        $action_id = $entry_option['id'];
                        $action_name = $entry_option['name'];
                        $user = $order->get_user();
                        $user_id=$order->user_id;
                        //Add contestent to giveaway
                        $tablename = $wpdb->prefix.'rafflepress_contestants';
                        $sql = "SELECT id FROM $tablename WHERE email = %s AND giveaway_id = %d";
                        $safe_sql = $wpdb->prepare($sql, $user->user_email, $giveaway_id);
                        $contestant_id = $wpdb->get_var($safe_sql);
                        if (empty($contestant_id)) {
                            $wpdb->insert($tablename, array(
                                    'giveaway_id' => $giveaway_id,
                                    'email' => !isset($user->user_email)  ? $order->get_data()["billing"]["email"] : $user->user_email,
                                    'fname' => !isset($user->display_name)? $order->get_data()["billing"]["first_name"] : $user->display_name,
                                    'lname' => !isset($user->display_name)? $order->get_data()["billing"]["last_name"] : $user->display_name,
                                    'winning_entry_id' => 0,
                                    'token' => get_option('rafflepress_token')
                                ), array(
                                    '%d',
                                    '%s',
                                    '%s',
                                    '%s',
                                ));
                            $contestant_id = $wpdb->insert_id;
                        }

                        // Programmatically Complete Invent Your Own Action
                        $entries_tablename = $wpdb->prefix.'rafflepress_entries';
                        $insert_arrays = array();
                        $entry_meta['action'] = $action_name;
                        for ($i = 1; $i <= $entries_earned; $i++) {
                            if($contestant_id){
                                $insert_arrays[] = array(
                                    'giveaway_id' => $giveaway_id,
                                    'contestant_id' => $contestant_id,
                                    'action_id' => $action_id,
                                    'meta' => wp_json_encode($entry_meta),
                                );
                            }
                        }
                        rafflepress_pro_wp_insert_rows($insert_arrays, $entries_tablename);
                    }
                }

            }
        }


    }
    return $payment_complete_status;
}*/

add_action('woocommerce_subscription_payment_complete', 'custom_add_funds', 10, 1);
add_action('woocommerce_subscription_renewal_payment_complete', 'custom_add_funds', 10, 1);
function custom_add_funds($subscription)
{
    // getting user id current subscription object
    $user_id = get_post_meta($subscription->id, '_customer_user', true);
    $user = get_user_by('id', $user_id);
    global $wpdb;
    if($user){
        $entries_earned = 50;
        $tablename = $wpdb->prefix.'rafflepress_giveaways';
        $sql = "SELECT * FROM $tablename WHERE active = 1";
        $query = $wpdb->prepare($sql);
        $giveaways = $wpdb->get_results($query);
        foreach ($giveaways as $key => $giveaway) {
            $giveaway_id = $giveaway->id;
            $settings = json_decode($giveaway->settings, true);
            $entry_options = $settings['entry_options'];
            if (sizeof($entry_options)) {
                foreach ($entry_options as $index => $entry_option) {
                    if ($entry_option['type'] == 'invent-your-own') {
                        $action_id = $entry_option['id'];
                        $action_name = $entry_option['name'];
                        //Add contestent to giveaway
                        $tablename = $wpdb->prefix.'rafflepress_contestants';
                        $sql = "SELECT id FROM $tablename WHERE email = %s AND giveaway_id = %d";
                        $safe_sql = $wpdb->prepare($sql, $user->user_email, $giveaway_id);
                        $contestant_id = $wpdb->get_var($safe_sql);
                        if (empty($contestant_id)) {
                            $wpdb->insert($tablename, array(
                                'giveaway_id' => $giveaway_id,
                                'email' =>  $user->user_email,
                                'fname' =>  $user->display_name,
                                'lname' =>  $user->display_name,
                                'winning_entry_id' => 0,
                                'token' => get_option('rafflepress_token')
                            ), array(
                                '%d',
                                '%s',
                                '%s',
                                '%s',
                            ));
                            $contestant_id = $wpdb->insert_id;
                        }

                        // Programmatically Complete Invent Your Own Action
                        $entries_tablename = $wpdb->prefix.'rafflepress_entries';
                        $insert_arrays = array();
                        $entry_meta['action'] = $action_name;
                        for ($i = 1; $i <= $entries_earned; $i++) {
                            if($contestant_id){
                                $insert_arrays[] = array(
                                    'giveaway_id' => $giveaway_id,
                                    'contestant_id' => $contestant_id,
                                    'action_id' => $action_id,
                                    'meta' => wp_json_encode($entry_meta),
                                );
                            }
                        }
                        rafflepress_pro_wp_insert_rows($insert_arrays, $entries_tablename);
                    }
                }
            }
        }
    }

}
