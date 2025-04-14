<?php

// Подключение стилей родительской темы
add_action('wp_enqueue_scripts', 'storefront_test_enqueue_styles');
function storefront_test_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'));
}


// Регистрация Custom Post Type "Cities"
function register_cities_post_type() {
    $labels = array(
        'name' => 'Cities',
        'singular_name' => 'City',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New City',
        'edit_item' => 'Edit City',
        'new_item' => 'New City',
        'view_item' => 'View City',
        'search_items' => 'Search Cities',
        'not_found' => 'No cities found',
        'not_found_in_trash' => 'No cities found in Trash',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-location',
        'supports' => array('title', 'editor', 'thumbnail'),
        'rewrite' => array('slug' => 'cities'),
    );

    register_post_type('city', $args);
}
add_action('init', 'register_cities_post_type');

// Создание метабокса для координат
function add_city_coordinates_meta_box() {
    add_meta_box(
        'city_coordinates',
        'City Coordinates',
        'render_city_coordinates_meta_box',
        'city',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_city_coordinates_meta_box');

// Отображение метабокса
function render_city_coordinates_meta_box($post) {
    wp_nonce_field('city_coordinates_nonce', 'city_coordinates_nonce');
    
    $latitude = get_post_meta($post->ID, '_city_latitude', true);
    $longitude = get_post_meta($post->ID, '_city_longitude', true);
    
    echo '<div class="coordinates-fields">';
    echo '<label for="city_latitude">Latitude:</label>';
    echo '<input type="text" id="city_latitude" name="city_latitude" value="' . esc_attr($latitude) . '" style="width: 100%; margin-bottom: 10px;">';
    
    echo '<label for="city_longitude">Longitude:</label>';
    echo '<input type="text" id="city_longitude" name="city_longitude" value="' . esc_attr($longitude) . '" style="width: 100%;">';
    echo '</div>';
}

// Сохранение данных
function save_city_coordinates_meta($post_id) {
    if (!isset($_POST['city_coordinates_nonce']) || 
        !wp_verify_nonce($_POST['city_coordinates_nonce'], 'city_coordinates_nonce') ||
        !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['city_latitude'])) {
        update_post_meta(
            $post_id,
            '_city_latitude',
            sanitize_text_field($_POST['city_latitude'])
        );
    }

    if (isset($_POST['city_longitude'])) {
        update_post_meta(
            $post_id,
            '_city_longitude',
            sanitize_text_field($_POST['city_longitude'])
        );
    }
}
add_action('save_post', 'save_city_coordinates_meta');

// Регистрация таксономии "Countries"
function register_countries_taxonomy() {
    $labels = array(
        'name' => 'Countries',
        'singular_name' => 'Country',
        'search_items' => 'Search Countries',
        'all_items' => 'All Countries',
        'parent_item' => 'Parent Country',
        'parent_item_colon' => 'Parent Country:',
        'edit_item' => 'Edit Country',
        'update_item' => 'Update Country',
        'add_new_item' => 'Add New Country',
        'new_item_name' => 'New Country Name',
        'menu_name' => 'Countries',
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true, // Как категории (true) или теги (false)
        'public' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'country'),
    );

    register_taxonomy('country', array('city'), $args);
}
add_action('init', 'register_countries_taxonomy');



///////////////////////////////////////////////////////////////////
// виджет должен показывать название города и текущую температуру 
// используя сторонний API (OpenWeatherMap)
///////////////////////////////////////////////////////////////////

class City_Weather_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'city_weather_widget',
            __('City Weather Widget', 'text_domain'),
            array('description' => __('Displays weather for selected city', 'text_domain'))
        );
    }

    // Форма настроек виджета
    public function form($instance) {
        $api_key = !empty($instance['api_key']) ? $instance['api_key'] : '';
        $city_id = !empty($instance['city_id']) ? $instance['city_id'] : '';
        $units = !empty($instance['units']) ? $instance['units'] : 'metric';
        
        // Получаем все города
        $cities = new WP_Query(array(
            'post_type' => 'city',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('api_key'); ?>">OpenWeatherMap API Key:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('api_key'); ?>"
                   name="<?php echo $this->get_field_name('api_key'); ?>" type="text"
                   value="<?php echo esc_attr($api_key); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('city_id'); ?>">Select City:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('city_id'); ?>"
                    name="<?php echo $this->get_field_name('city_id'); ?>">
                <option value="">— Select —</option>
                <?php while ($cities->have_posts()) : $cities->the_post(); ?>
                    <option value="<?php echo get_the_ID(); ?>" 
                        <?php selected($city_id, get_the_ID()); ?>>
                        <?php the_title(); ?>
                    </option>
                <?php endwhile; wp_reset_postdata(); ?>
            </select>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('units'); ?>">Temperature Units:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('units'); ?>"
                    name="<?php echo $this->get_field_name('units'); ?>">
                <option value="metric" <?php selected($units, 'metric'); ?>>°C</option>
                <option value="imperial" <?php selected($units, 'imperial'); ?>>°F</option>
            </select>
        </p>
        <?php
    }

    // Сохранение настроек
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['api_key'] = sanitize_text_field($new_instance['api_key']);
        $instance['city_id'] = absint($new_instance['city_id']);
        $instance['units'] = in_array($new_instance['units'], array('metric', 'imperial')) 
                           ? $new_instance['units'] 
                           : 'metric';
        return $instance;
    }

    // Вывод виджета
    public function widget($args, $instance) {
        if (empty($instance['city_id']) || empty($instance['api_key'])) return;

        $city = get_post($instance['city_id']);
        $latitude = get_post_meta($instance['city_id'], '_city_latitude', true);
        $longitude = get_post_meta($instance['city_id'], '_city_longitude', true);
        
        if (!$city || !$latitude || !$longitude) return;

        // Получаем данные погоды
        $weather_data = $this->get_weather_data(
            $instance['city_id'],
            $latitude,
            $longitude,
            $instance['api_key'],
            $instance['units']
        );

        echo $args['before_widget'];
        
        if (!empty($weather_data)) {
            echo '<div class="weather-widget">';
            echo '<h3 class="weather-title">' . esc_html($city->post_title) . '</h3>';
            echo '<div class="weather-temp">';
            echo round($weather_data['temp']) . '°' . ($instance['units'] == 'metric' ? 'C' : 'F');
            echo '</div>';
            echo '</div>';
        } else {
            echo '<p>Weather data unavailable</p>';
        }
        
        echo $args['after_widget'];
    }

    // Запрос к API OpenWeatherMap
    private function get_weather_data($city_id, $lat, $lon, $api_key, $units) {
        $transient_key = 'weather_' . md5($lat . $lon . $units);
        $data = get_transient($transient_key);

        if (!$data) {
            $response = wp_remote_get(
                "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units={$units}&appid={$api_key}"
            );

            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                $data = array(
                    'temp' => $body['main']['temp'],
                    'humidity' => $body['main']['humidity'],
                    'description' => $body['weather'][0]['description'],
                    'icon' => $body['weather'][0]['icon']
                );
                
                // Сохраняем температуру в метаполе
                update_post_meta($city_id, '_city_temperature', $data['temp']);
            
                set_transient($transient_key, $data, HOUR_IN_SECONDS);
            }
        } else {
            // Обновляем метаполе даже при использовании кэша (можно не делать это)
            update_post_meta($city_id, '_city_temperature', $data['temp']);
        }
        
        return $data;
    }
}

// Регистрация виджета
add_action('widgets_init', function() {
    register_widget('City_Weather_Widget');
});


////////////////////////////////////////////////////
// Крон-задача для обновления автоматического обновления температур
add_action('wp', 'setup_weather_cron');
function setup_weather_cron() {
    if (!wp_next_scheduled('update_weather_data_hook')) {
        wp_schedule_event(time(), 'hourly', 'update_weather_data_hook');
    }
}

add_action('update_weather_data_hook', 'update_all_cities_weather');
function update_all_cities_weather() {
    global $wpdb;
    
    $cities = $wpdb->get_results("
        SELECT ID, 
            MAX(CASE WHEN meta_key = '_city_latitude' THEN meta_value END) AS lat,
            MAX(CASE WHEN meta_key = '_city_longitude' THEN meta_value END) AS lng
        FROM {$wpdb->posts}
        LEFT JOIN {$wpdb->postmeta} ON ID = post_id
        WHERE post_type = 'city'
        GROUP BY ID
    ");

    foreach ($cities as $city) {
        $api_key = get_option('weather_api_key');
        if (!$api_key) continue;
        
        $response = wp_remote_get(
            "https://api.openweathermap.org/data/2.5/weather?lat={$city->lat}&lon={$city->lng}&units=metric&appid={$api_key}"
        );
        
        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            update_post_meta($city->ID, '_city_temperature', $body['main']['temp']);
        }
    }
}


//////////////////////////////////////////////////////////////////
// Обработчик AJAX для поиска
add_action('wp_ajax_weather_table_search', 'handle_weather_table_search');
add_action('wp_ajax_nopriv_weather_table_search', 'handle_weather_table_search');

function handle_weather_table_search() {
    check_ajax_referer('weather-search-nonce', 'security');
    
    include get_theme_file_path('weather-table-content.php');
    
    wp_die();
}

//////////////////////////////////////////////////////////////////
// Регистрация скриптов для AJAX-поиска
add_action('wp_enqueue_scripts', 'weather_table_scripts');
function weather_table_scripts() {
    if (is_page_template('page-weather-table.php')) {
        wp_enqueue_script('jquery');
        
        wp_register_script(
            'weather-table-ajax',
            get_stylesheet_directory_uri() . '/js/weather-table.js',
            array('jquery'),
            filemtime(get_stylesheet_directory() . '/js/weather-table.js'),
            true
        );
        
        wp_localize_script('weather-table-ajax', 'weatherTableParams', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('weather-search-nonce')
        ));
        
        wp_enqueue_script('weather-table-ajax');
    }
}

//////////////////////////////////////////////////////////////////
// Запрещаем прямой доступ к файлу таблицы
add_action('template_redirect', 'block_direct_access');
function block_direct_access() {
    if (strpos($_SERVER['REQUEST_URI'], 'weather-table-content.php') !== false) {
        wp_die('Direct access not allowed');
    }
}

///////////////////////////////////////////////////////////////////
// Добавляем контент перед таблицей
add_action('before_cities_table', 'display_table_stats', 10, 1);
function display_table_stats($cities) {
    $cities_count = 0;
    if (is_object($cities)) {
        $cities_count = 1;
    } elseif (is_countable($cities)) {
        $cities_count = count($cities);
    }
    
    echo '<div class="table-stats">';
    echo '<p>Total cities: ' . $cities_count . '</p>';
    echo '</div>';
}

// Добавляем контент после таблицы
add_action('after_cities_table', 'display_table_disclaimer');
function display_table_disclaimer() {
    echo '<div class="table-disclaimer">';
    echo '<p>Data updates every 15 minutes</p>';
    echo '</div>';
}
