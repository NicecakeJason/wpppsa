<?php
if (!class_exists('sunApartamentMenu')) {
    class sunApartamentMenu {
        public function __construct() {
            add_action('admin_menu', [$this, 'add_admin_menu']);
        }

        /**
         * Добавляем главное меню и подпункты.
         */
        public function add_admin_menu() {
            // Главное меню
            add_menu_page(
                'Sun Apartament', // Заголовок страницы
                'Sun Apartament', // Название в меню
                'manage_options', // Права доступа
                'sun-apartament', // Уникальный идентификатор страницы
                [$this, 'render_main_page'], // Функция для отрисовки главной страницы
                'dashicons-admin-home', // Иконка
                6 // Позиция в меню
            );
    
            // Подпункт "Апартаменты"
            add_submenu_page(
                'sun-apartament', // Родительский slug
                'Апартаменты', // Заголовок страницы
                'Апартаменты', // Название в меню
                'manage_options', // Права доступа
                'edit.php?post_type=apartament', // Ссылка на страницу
                null // Функция для отрисовки
            );
    
            // Подпункт "Типы"
            add_submenu_page(
                'sun-apartament', // Родительский slug
                'Типы апартаментов', // Заголовок страницы
                'Типы', // Название в меню
                'manage_options', // Права доступа
                'edit-tags.php?taxonomy=apartament-type&post_type=apartament', // Ссылка на страницу управления таксономией
                null // Функция для отрисовки
            );
    
            
        }
    
        /**
         * Удаляем ненужные пункты меню.
         */
        public function remove_unwanted_menu_items() {
            // Удаляем пункт меню "Доступность апартаментов" из общего меню
            remove_menu_page('apartament-availability'); // Укажите правильный slug
        }

        /**
         * Отрисовываем главную страницу плагина.
         */
        public function render_main_page() {
            echo '<div class="wrap">';
            echo '<h1>Sun Apartament</h1>';
            echo '<p>Добро пожаловать в плагин для управления апартаментами.</p>';
            echo '</div>';
        }

        
    }
}

// Инициализируем класс для главного меню плагина
if (class_exists('sunApartamentMenu')) {
   new sunApartamentMenu();
}