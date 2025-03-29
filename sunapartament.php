<?php
/*
Plugin Name: sunApartament
Plugin URI: 
Description: First Plugin
Version: 1.0
Author: Agen
Author URI: 
Licence: GPLv2 or later
Text Domain: sunapartament
Domain Path: /lang
*/


if (!defined('ABSPATH')) {
   die;
}

define('SUNAPARTAMENT_PATH',plugin_dir_path(__FILE__));

// Вызываем функцию при активации плагина
register_activation_hook(__FILE__, 'create_booking_tables');

// Функция для создания таблиц в базе данных
function create_booking_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Таблица для персональных данных
    $table_personal_data = $wpdb->prefix . 'sun_personal_data';
    $sql_personal_data = "CREATE TABLE $table_personal_data (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        middle_name varchar(100),
        email varchar(100) NOT NULL,
        phone varchar(50) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // Таблица для бронирований
    $table_bookings = $wpdb->prefix . 'sun_bookings';
    $sql_bookings = "CREATE TABLE $table_bookings (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        booking_id varchar(20) NOT NULL,
        personal_data_id mediumint(9) NOT NULL,
        apartament_id mediumint(9) NOT NULL,
        checkin_date date NOT NULL,
        checkout_date date NOT NULL,
        total_price decimal(10,2) NOT NULL,
        payment_method varchar(50) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        terms_accepted tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY booking_id (booking_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result1 = dbDelta($sql_personal_data);
    $result2 = dbDelta($sql_bookings);
    
    // Логируем результаты для отладки
    error_log('Создание таблицы personal_data: ' . print_r($result1, true));
    error_log('Создание таблицы bookings: ' . print_r($result2, true));
}

// Проверяем и создаем таблицы при каждой загрузке сайта
function check_and_create_booking_tables() {
    global $wpdb;
    
    $personal_data_table = $wpdb->prefix . 'sun_personal_data';
    $bookings_table = $wpdb->prefix . 'sun_bookings';
    
    // Проверяем существование таблиц
    $personal_data_exists = $wpdb->get_var("SHOW TABLES LIKE '$personal_data_table'") === $personal_data_table;
    $bookings_exists = $wpdb->get_var("SHOW TABLES LIKE '$bookings_table'") === $bookings_table;
    
    // Если хотя бы одна таблица не существует, создаем обе таблицы
    if (!$personal_data_exists || !$bookings_exists) {
        create_booking_tables();
        error_log('Таблицы созданы через hook init: personal_data=' . ($personal_data_exists ? 'да' : 'нет') . ', bookings=' . ($bookings_exists ? 'да' : 'нет'));
    }
}

// Вызываем функцию проверки таблиц при инициализации WordPress
add_action('init', 'check_and_create_booking_tables');

require_once plugin_dir_path(__FILE__) . 'inc/booking-notifications.php';

// Добавьте эту строку в начало основного файла вашего плагина (обычно это файл с именем sunapartament.php или подобным)
require_once plugin_dir_path(__FILE__) . 'reset-availability.php';

// Подключаем файл с классом для работы с кастомным типом записи
if(!class_exists('sunApartamentcpt')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartamentcpt.php';
}
// Подключаем файл с классом для работы с шорткодом
if(!class_exists('sunApartamentShortcode')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-shortcode.php';
}

// Подключаем файл с классом для работы с доступностью квартир
if(!class_exists('sunApartamentAvailability')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-availability.php';
}
// Подключаем файл с классом для работы с календарем
if(!class_exists('sunApartamentAvailabilityCalendar')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-availability-calendar.php';
}
// Подключаем файл с классом для работы с доступностью квартир
if(!class_exists('sunApartamentBookingForm')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-booking-form.php';
}

// Подключаем файл с классом для работы с изображением апартаментов
if(!class_exists('sunApartamentImg')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-img.php';
}

// Подключаем файл с классом для работы с ценой
if(!class_exists('sunApartamentPrice')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-price.php';
}

// Подключаем файл с классом для работы с удобствами
if(!class_exists('sunApartamentAmenities')){
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-amenities.php';
}

// Подключаем файл с классом для главного меню плагина
if (!class_exists('sunApartamentMenu')) {
    require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-menu.php';
}   

if(!class_exists('Gamajo_Template_Loader')){
    require SUNAPARTAMENT_PATH . 'inc/class-gamajo-template-loader.php';
}
require SUNAPARTAMENT_PATH . 'inc/class-sunapartament-template-loader.php';

class sunApartament{

    function register(){
        add_action('admin_enqueue_scripts',[$this,'enqueue_admin']);
        add_action('wp_enqueue_scripts', [$this,'enqueue_front']);
    }

    public function enqueue_admin() {
        wp_enqueue_style('sunapartament_style_admin', plugins_url('/assets/css/admin/style.css', __FILE__));
        wp_enqueue_script('sunapartament_script_admin', plugins_url('/assets/js/admin/scripts.js', __FILE__), array('jquery', 'media-upload', 'media-views'), '1.0', true);
        wp_enqueue_style('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css');
        wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js', [], null, true);
    }

    public function enqueue_front(){

        wp_enqueue_style('sunapartament_style', plugins_url('/assets/css/front/style.css',__FILE__));
        wp_enqueue_script('sunapartament_script', plugins_url('/assets/js/front/scripts.js', __FILE__),array('jquery'),'1.0',true);
       
        wp_enqueue_script('jquery-form');
        wp_enqueue_style('sunapartament-booking-form_style', plugins_url('/assets/css/front/booking-form.css',__FILE__));
        wp_enqueue_script('sunapartament-booking-form_script', plugins_url('/assets/js/front/booking-form.js', __FILE__),[], null, true);
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js', [], null, true);
        wp_enqueue_script('flatpickr-l10n-ru', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js', ['flatpickr-js'], null, true);
        wp_enqueue_script('share-yandex', 'https://yastatic.net/share2/share.js', [], null, true);
      
    }


    static function activation(){
        // Вызываем создание таблиц при активации
        create_booking_tables();
        flush_rewrite_rules();
    }
    
    static function deactivation(){
        flush_rewrite_rules();
    }
}

if(class_exists('sunApartament')){
    $sunApartament = new sunApartament();
    $sunApartament->register();
}

register_activation_hook(__FILE__, array($sunApartament,'activation') );
register_deactivation_hook(__FILE__, array($sunApartament,'deactivation') );