<?php
if (!class_exists("sunApartamentcpt")) {
    class sunApartamentcpt {
        public function register() {
            add_action('init', [$this, 'custom_post_type']);
            add_action('init', [$this, 'register_booking_post_type']);
        }

//////////////////////////////////////////////////////////////////////////
        public function custom_post_type() {
            register_post_type('apartament',
            array(
                'public' => true,
                'has_archive' => true,
                'rewrite' => ['slug'=>'apartaments'],
                'label' => esc_html__('Апартаменты','sunapartament'),
                'show_in_menu' => false,
                'supports' => array('title','editor'),
            ));

            // Регистрация таксономии "Тип апартамента"
            $labels = array(
                'name'              => esc_html_x( 'Типы апартаментов', 'taxonomy general name', 'sunapartament' ),
                'singular_name'     => esc_html_x( 'Тип апартамента', 'taxonomy singular name', 'sunapartament' ),
                'search_items'      => esc_html__( 'Search Types', 'sunapartament' ),
                'all_items'         => esc_html__( 'Все типы', 'sunapartament' ),
                'parent_item'       => esc_html__( 'Родительский тип', 'sunapartament' ),
                'parent_item_colon' => esc_html__( 'Родительский тип:', 'sunapartament' ),
                'edit_item'         => esc_html__( 'Редактировать тип', 'sunapartament' ),
                'update_item'       => esc_html__( 'Обновить тип', 'sunapartament' ),
                'add_new_item'      => esc_html__( 'Добавить новый тип', 'sunapartament' ),
                'new_item_name'     => esc_html__( 'Добавить новое имя', 'sunapartament' ),
                'menu_name'         => esc_html__( 'Тип апартамента', 'sunapartament' ),
            );
            $args = array(
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug'=>'apartaments/type'),
                'labels' => $labels,
            );
            register_taxonomy('apartament-type', 'apartament', $args);
        }



        ////////////////////////////////////////////////////////////////////////////////////

        
        /**
         * Регистрация Custom Post Type для бронирований
         */
        public function register_booking_post_type() {
            $labels = array(
                'name'                  => 'Бронирования',
                'singular_name'         => 'Бронирование',
                'menu_name'             => 'Бронирования',
                'name_admin_bar'        => 'Бронирование',
                'add_new'               => 'Добавить новое',
                'add_new_item'          => 'Добавить новое бронирование',
                'new_item'              => 'Новое бронирование',
                'edit_item'             => 'Редактировать бронирование',
                'view_item'             => 'Просмотреть бронирование',
                'all_items'             => 'Все бронирования',
                'search_items'          => 'Поиск бронирований',
                'not_found'             => 'Бронирования не найдены',
                'not_found_in_trash'    => 'В корзине бронирования не найдены',
            );

            $args = array(
                'labels'                => $labels,
                'public'                => false,
                'publicly_queryable'    => false,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_icon'             => 'dashicons-calendar-alt',
                'query_var'             => true,
                'rewrite'               => array('slug' => 'bookings'),
                'capability_type'       => 'post',
                'has_archive'           => false,
                'hierarchical'          => false,
                'menu_position'         => 5,
                'supports'              => array('title', 'custom-fields'),
            );

            register_post_type('sun_booking', $args);
            
            // Регистрируем статусы бронирований
            register_post_status('pending', array(
                'label'                 => 'Ожидает подтверждения',
                'public'                => true,
                'exclude_from_search'   => false,
                'show_in_admin_all_list'=> true,
                'show_in_admin_status_list' => true,
                'label_count'           => _n_noop('Ожидает подтверждения <span class="count">(%s)</span>', 'Ожидает подтверждения <span class="count">(%s)</span>'),
            ));
            
            register_post_status('confirmed', array(
                'label'                 => 'Подтверждено',
                'public'                => true,
                'exclude_from_search'   => false,
                'show_in_admin_all_list'=> true,
                'show_in_admin_status_list' => true,
                'label_count'           => _n_noop('Подтверждено <span class="count">(%s)</span>', 'Подтверждено <span class="count">(%s)</span>'),
            ));
            
            register_post_status('cancelled', array(
                'label'                 => 'Отменено',
                'public'                => true,
                'exclude_from_search'   => false,
                'show_in_admin_all_list'=> true,
                'show_in_admin_status_list' => true,
                'label_count'           => _n_noop('Отменено <span class="count">(%s)</span>', 'Отменено <span class="count">(%s)</span>'),
            ));
            
            register_post_status('completed', array(
                'label'                 => 'Завершено',
                'public'                => true,
                'exclude_from_search'   => false,
                'show_in_admin_all_list'=> true,
                'show_in_admin_status_list' => true,
                'label_count'           => _n_noop('Завершено <span class="count">(%s)</span>', 'Завершено <span class="count">(%s)</span>'),
            ));
        }

    }
}
//////////////////////////////////////////////////////////////////////////





if (class_exists('sunApartamentcpt')) {
    $sunApartamentcpt = new sunApartamentcpt();
    $sunApartamentcpt->register();
}