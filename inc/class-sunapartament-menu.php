<?php
if (!class_exists('sunApartamentMenu')) {
    class sunApartamentMenu {
        public function __construct() {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_dashboard_scripts']);
        }

        /**
         * Добавляем скрипты и стили для панели управления
         */
        public function enqueue_dashboard_scripts($hook) {
            if ($hook == 'toplevel_page_sun-apartament') {
                wp_enqueue_style('sun-dashboard-style', plugins_url('/assets/css/admin/dashboard.css', dirname(__FILE__)));
                wp_enqueue_script('sun-dashboard-script', plugins_url('/assets/js/admin/dashboard.js', dirname(__FILE__)), ['jquery', 'jquery-ui-datepicker'], '1.0', true);
                
                // Добавляем Font Awesome для иконок
                wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
                
                // Добавляем Chart.js для графиков
                wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', ['jquery'], '3.7.0', true);
            }
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
                [$this, 'render_dashboard_page'], // Функция для отрисовки главной страницы
                'dashicons-building', // Иконка
                6 // Позиция в меню
            );
    
            // Подпункт "Панель управления"
            add_submenu_page(
                'sun-apartament', // Родительский slug
                'Панель управления', // Заголовок страницы
                'Панель управления', // Название в меню
                'manage_options', // Права доступа
                'sun-apartament', // Ссылка на страницу
                [$this, 'render_dashboard_page'] // Функция для отрисовки
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
            
            // Подпункт "Бронирования"
            add_submenu_page(
                'sun-apartament', // Родительский slug
                'Все бронирования', // Заголовок страницы
                'Бронирования', // Название в меню
                'manage_options', // Права доступа
                'edit.php?post_type=sun_booking', // Ссылка на страницу
                null // Функция для отрисовки
            );
            
            // Подпункт "Календарь"
            // add_submenu_page(
            //     'sun-apartament', // Родительский slug
            //     'Календарь доступности', // Заголовок страницы
            //     'Календарь', // Название в меню
            //     'manage_options', // Права доступа
            //     'sun-booking-calendar', // Ссылка на страницу
            //     null // Функция для отрисовки (использует метод из класса SunApartamentAvailabilityCalendar)
            // );
        }
    
        /**
         * Удаляем ненужные пункты меню.
         */
        public function remove_unwanted_menu_items() {
            // Удаляем пункт меню "Доступность апартаментов" из общего меню
            remove_menu_page('apartament-availability'); // Укажите правильный slug
        }

        /**
         * Отрисовываем панель управления плагина.
         */
        public function render_dashboard_page() {
            // Получаем статистические данные
            $stats = $this->get_dashboard_stats();
            
            // HTML для панели управления
            ?>
            <div class="wrap sun-dashboard">
                <h1 class="sun-dashboard-title">
                    <i class="fas fa-building"></i> Sun Apartament - Панель управления
                </h1>
                
                <div class="sun-dashboard-header">
                    <div class="sun-dashboard-actions">
                        <a href="<?php echo admin_url('post-new.php?post_type=apartament'); ?>" class="button button-primary">
                            <i class="fas fa-plus"></i> Добавить апартамент
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=sun_booking'); ?>" class="button button-primary">
                            <i class="fas fa-calendar-plus"></i> Новое бронирование
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=sun-booking-calendar'); ?>" class="button">
                            <i class="fas fa-calendar-alt"></i> Календарь
                        </a>
                    </div>
                </div>
                
                <div class="sun-dashboard-widgets">
                    <!-- Статистика -->
                    <div class="sun-dashboard-row">
                        <div class="sun-dashboard-card">
                            <div class="card-icon blue">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="card-content">
                                <h3>Всего апартаментов</h3>
                                <p class="card-value"><?php echo $stats['total_apartaments']; ?></p>
                            </div>
                        </div>
                        
                        <div class="sun-dashboard-card">
                            <div class="card-icon green">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="card-content">
                                <h3>Активные бронирования</h3>
                                <p class="card-value"><?php echo $stats['active_bookings']; ?></p>
                            </div>
                        </div>
                        
                        <div class="sun-dashboard-card">
                            <div class="card-icon orange">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="card-content">
                                <h3>Заезды сегодня</h3>
                                <p class="card-value"><?php echo $stats['checkins_today']; ?></p>
                            </div>
                        </div>
                        
                        <div class="sun-dashboard-card">
                            <div class="card-icon purple">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="card-content">
                                <h3>Выезды сегодня</h3>
                                <p class="card-value"><?php echo $stats['checkouts_today']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Графики и таблицы -->
                    <div class="sun-dashboard-row">
                        <!-- Последние бронирования -->
                        <div class="sun-dashboard-box">
                            <h2 class="box-title">
                                <i class="fas fa-list"></i> Последние бронирования
                                <a href="<?php echo admin_url('edit.php?post_type=sun_booking'); ?>" class="box-title-link">Все бронирования</a>
                            </h2>
                            <div class="box-content">
                                <?php $this->render_recent_bookings(); ?>
                            </div>
                        </div>
                        
                        <!-- Графики загруженности -->
                        <div class="sun-dashboard-box">
                            <h2 class="box-title">
                                <i class="fas fa-chart-bar"></i> Загруженность апартаментов
                            </h2>
                            <div class="box-content">
                                <canvas id="occupancyChart" height="200"></canvas>
                                <script>
                                    jQuery(document).ready(function($) {
                                        var ctx = document.getElementById('occupancyChart').getContext('2d');
                                        var chart = new Chart(ctx, {
                                            type: 'bar',
                                            data: {
                                                labels: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                                                datasets: [{
                                                    label: 'Загруженность (%)',
                                                    data: <?php echo json_encode($stats['occupancy_data']); ?>,
                                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                                    borderColor: 'rgba(54, 162, 235, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        max: 100
                                                    }
                                                }
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sun-dashboard-row">
                        <!-- Ближайшие заезды -->
                        <div class="sun-dashboard-box">
                            <h2 class="box-title">
                                <i class="fas fa-sign-in-alt"></i> Ближайшие заезды
                            </h2>
                            <div class="box-content">
                                <?php $this->render_upcoming_checkins(); ?>
                            </div>
                        </div>
                        
                        <!-- Доступность апартаментов -->
                        <div class="sun-dashboard-box">
                            <h2 class="box-title">
                                <i class="fas fa-home"></i> Доступность апартаментов
                            </h2>
                            <div class="box-content">
                                <?php $this->render_apartments_availability(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
                .sun-dashboard {
                    margin: 20px 0;
                }
                
                .sun-dashboard-title {
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .sun-dashboard-header {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 30px;
                }
                
                .sun-dashboard-actions {
                    display: flex;
                    gap: 10px;
                }
                
                .sun-dashboard-row {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                
                .sun-dashboard-card {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    padding: 20px;
                    display: flex;
                    width: calc(25% - 15px);
                    align-items: center;
                }
                
                .card-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 20px;
                    font-size: 24px;
                    color: white;
                }
                
                .card-icon.blue { background-color: #2196F3; }
                .card-icon.green { background-color: #4CAF50; }
                .card-icon.orange { background-color: #FF9800; }
                .card-icon.purple { background-color: #9C27B0; }
                
                .card-content h3 {
                    margin: 0;
                    font-size: 16px;
                    color: #666;
                }
                
                .card-value {
                    font-size: 28px;
                    font-weight: bold;
                    margin: 5px 0 0;
                    color: #333;
                }
                
                .sun-dashboard-box {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    width: calc(50% - 10px);
                    overflow: hidden;
                }
                
                .box-title {
                    background: #f5f5f5;
                    padding: 15px;
                    margin: 0;
                    border-bottom: 1px solid #ddd;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    font-size: 16px;
                }
                
                .box-title i {
                    margin-right: 8px;
                    color: #666;
                }
                
                .box-title-link {
                    font-size: 14px;
                    text-decoration: none;
                }
                
                .box-content {
                    padding: 15px;
                }
                
                .bookings-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .bookings-table th,
                .bookings-table td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #eee;
                }
                
                .status-badge {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 20px;
                    font-size: 12px;
                }
                
                .status-confirmed { background-color: #E8F5E9; color: #388E3C; }
                .status-pending { background-color: #FFF8E1; color: #FFA000; }
                .status-completed { background-color: #E3F2FD; color: #1976D2; }
                .status-cancelled { background-color: #FFEBEE; color: #D32F2F; }
                
                @media (max-width: 1200px) {
                    .sun-dashboard-card {
                        width: calc(50% - 10px);
                    }
                    .sun-dashboard-box {
                        width: 100%;
                        margin-bottom: 20px;
                    }
                }
                
                @media (max-width: 768px) {
                    .sun-dashboard-card {
                        width: 100%;
                    }
                }
            </style>
            <?php
        }
        
        /**
         * Получение статистических данных для панели управления
         */
        private function get_dashboard_stats() {
            $stats = [];
            
            // Общее количество апартаментов
            $apartaments_count = wp_count_posts('apartament');
            $stats['total_apartaments'] = $apartaments_count->publish;
            
            // Количество активных бронирований
            $bookings_count = wp_count_posts('sun_booking');
            $stats['active_bookings'] = $bookings_count->confirmed + $bookings_count->pending;
            
            // Получаем текущую дату в различных форматах для надежности
            $today_dmy = date('d.m.Y'); // 12.03.2025
            $today_ymd = date('Y-m-d'); // 2025-03-12
            
            // Проверим и выведем отладочную информацию
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Debug - Today's date (d.m.Y): {$today_dmy}");
                error_log("Debug - Today's date (Y-m-d): {$today_ymd}");
            }
            
            // Проверка наличия брони на 12.03.2025-18.03.2025
            $this->debug_specific_booking_period('12.03.2025', '18.03.2025');
            
            // Количество заездов и выездов сегодня, используем оба формата для надежности
            $stats['checkins_today'] = $this->count_bookings_for_date('_booking_checkin_date', $today_dmy);
            $stats['checkouts_today'] = $this->count_bookings_for_date('_booking_checkout_date', $today_dmy);
            
            // Данные для графика загруженности по месяцам
            $stats['occupancy_data'] = $this->get_occupancy_by_month();
            
            return $stats;
        }
        
        /**
         * Отладочная функция для проверки конкретного бронирования
         */
        private function debug_specific_booking_period($start_date, $end_date) {
            if (!defined('WP_DEBUG') || !WP_DEBUG) {
                return;
            }
            
            global $wpdb;
            
            // Проверяем наличие бронирований на указанный период
            $query = $wpdb->prepare(
                "SELECT p.ID, p.post_title, pm1.meta_value as checkin, pm2.meta_value as checkout
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_booking_checkin_date'
                LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_booking_checkout_date'
                WHERE p.post_type = 'sun_booking'
                AND pm1.meta_value = %s 
                AND pm2.meta_value = %s",
                $start_date,
                $end_date
            );
            
            $bookings = $wpdb->get_results($query);
            
            error_log("Debug - Checking for bookings in period: {$start_date} to {$end_date}");
            error_log("Debug - Found bookings: " . count($bookings));
            
            foreach ($bookings as $booking) {
                error_log("Debug - Booking ID: {$booking->ID}, Title: {$booking->post_title}");
                error_log("Debug - Check-in: {$booking->checkin}, Check-out: {$booking->checkout}");
            }
        }
        
        /**
         * Подсчет количества бронирований для конкретной даты
         */
        private function count_bookings_for_date($meta_key, $date) {
            global $wpdb;
            
            // Проверяем формат сохранения дат в базе данных
            // Возможные форматы: 'd.m.Y', 'Y-m-d' или другие
            
            // Создаем запрос, который будет работать независимо от формата даты
            $query = $wpdb->prepare(
                "SELECT COUNT(p.ID) FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'sun_booking'
                AND p.post_status IN ('confirmed', 'pending')
                AND pm.meta_key = %s
                AND (pm.meta_value = %s OR pm.meta_value = %s)",
                $meta_key,
                $date,
                date('Y-m-d', strtotime($date)) // Альтернативный формат даты
            );
            
            $count = $wpdb->get_var($query);
            
            // Отладочная информация
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Debug - Checking bookings for date: {$date}");
                error_log("Debug - Meta key: {$meta_key}");
                error_log("Debug - Count result: {$count}");
                error_log("Debug - SQL Query: {$query}");
            }
            
            return $count;
        }
        
        /**
         * Получение данных о загруженности по месяцам
         */
        private function get_occupancy_by_month() {
            // В реальном приложении здесь нужна логика расчета загруженности
            // Пример данных для демонстрации
            return [75, 68, 82, 60, 65, 90, 95, 97, 85, 70, 65, 78];
        }
        
        /**
         * Отображение последних бронирований
         */
        private function render_recent_bookings() {
            $args = [
                'post_type' => 'sun_booking',
                'posts_per_page' => 5,
                'orderby' => 'date',
                'order' => 'DESC',
                'post_status' => ['confirmed', 'pending', 'completed', 'cancelled']
            ];
            
            $recent_bookings = new WP_Query($args);
            
            if ($recent_bookings->have_posts()) {
                echo '<table class="bookings-table">';
                echo '<thead><tr>';
                echo '<th>№ бронирования</th>';
                echo '<th>Гость</th>';
                echo '<th>Апартамент</th>';
                echo '<th>Даты</th>';
                echo '<th>Статус</th>';
                echo '</tr></thead>';
                echo '<tbody>';
                
                while ($recent_bookings->have_posts()) {
                    $recent_bookings->the_post();
                    $booking_id = get_the_ID();
                    $apartament_id = get_post_meta($booking_id, '_booking_apartament_id', true);
                    $first_name = get_post_meta($booking_id, '_booking_first_name', true);
                    $last_name = get_post_meta($booking_id, '_booking_last_name', true);
                    $checkin_date = get_post_meta($booking_id, '_booking_checkin_date', true);
                    $checkout_date = get_post_meta($booking_id, '_booking_checkout_date', true);
                    $status = get_post_status();
                    
                    $status_class = 'status-' . $status;
                    $status_text = '';
                    
                    switch ($status) {
                        case 'confirmed':
                            $status_text = 'Подтверждено';
                            break;
                        case 'pending':
                            $status_text = 'Ожидает';
                            break;
                        case 'completed':
                            $status_text = 'Завершено';
                            break;
                        case 'cancelled':
                            $status_text = 'Отменено';
                            break;
                        default:
                            $status_text = $status;
                    }
                    
                    echo '<tr>';
                    echo '<td><a href="' . get_edit_post_link($booking_id) . '">' . get_the_title() . '</a></td>';
                    echo '<td>' . esc_html($last_name . ' ' . $first_name) . '</td>';
                    echo '<td>' . ($apartament_id ? esc_html(get_the_title($apartament_id)) : '-') . '</td>';
                    echo '<td>' . esc_html($checkin_date . ' - ' . $checkout_date) . '</td>';
                    echo '<td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p>Нет бронирований.</p>';
            }
            
            wp_reset_postdata();
        }
        
        /**
         * Отображение ближайших заездов
         */
        private function render_upcoming_checkins() {
            $today = date('d.m.Y');
            $week_later = date('d.m.Y', strtotime('+7 days'));
            
            $args = [
                'post_type' => 'sun_booking',
                'posts_per_page' => 5,
                'post_status' => ['confirmed', 'pending'],
                'meta_query' => [
                    [
                        'key' => '_booking_checkin_date',
                        'value' => [$today, $week_later],
                        'compare' => 'BETWEEN',
                        'type' => 'DATE'
                    ]
                ],
                'orderby' => 'meta_value',
                'meta_key' => '_booking_checkin_date',
                'order' => 'ASC'
            ];
            
            $upcoming_checkins = new WP_Query($args);
            
            if ($upcoming_checkins->have_posts()) {
                echo '<table class="bookings-table">';
                echo '<thead><tr>';
                echo '<th>Дата заезда</th>';
                echo '<th>Гость</th>';
                echo '<th>Апартамент</th>';
                echo '</tr></thead>';
                echo '<tbody>';
                
                while ($upcoming_checkins->have_posts()) {
                    $upcoming_checkins->the_post();
                    $booking_id = get_the_ID();
                    $apartament_id = get_post_meta($booking_id, '_booking_apartament_id', true);
                    $first_name = get_post_meta($booking_id, '_booking_first_name', true);
                    $last_name = get_post_meta($booking_id, '_booking_last_name', true);
                    $checkin_date = get_post_meta($booking_id, '_booking_checkin_date', true);
                    
                    echo '<tr>';
                    echo '<td><a href="' . get_edit_post_link($booking_id) . '">' . esc_html($checkin_date) . '</a></td>';
                    echo '<td>' . esc_html($last_name . ' ' . $first_name) . '</td>';
                    echo '<td>' . ($apartament_id ? esc_html(get_the_title($apartament_id)) : '-') . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p>Нет ближайших заездов.</p>';
            }
            
            wp_reset_postdata();
        }
        
        /**
         * Отображение доступности апартаментов
         */
        private function render_apartments_availability() {
            $args = [
                'post_type' => 'apartament',
                'posts_per_page' => 10,
                'orderby' => 'title',
                'order' => 'ASC'
            ];
            
            $apartments = new WP_Query($args);
            
            if ($apartments->have_posts()) {
                echo '<table class="bookings-table">';
                echo '<thead><tr>';
                echo '<th>Апартамент</th>';
                echo '<th>Статус</th>';
                echo '<th>Тип</th>';
                echo '<th>Действия</th>';
                echo '</tr></thead>';
                echo '<tbody>';
                
                while ($apartments->have_posts()) {
                    $apartments->the_post();
                    $apartament_id = get_the_ID();
                    
                    // Получаем бронирования для апартамента
                    $today = date('Y-m-d');
                    
                    // Получаем даты недоступности
                    $booked_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
                    
                    // Проверяем, занят ли апартамент сегодня
                    $is_occupied = is_array($booked_dates) && isset($booked_dates[$today]);
                    
                    // Получаем термины таксономии "Тип апартамента"
                    $types = get_the_terms($apartament_id, 'apartament-type');
                    $type_name = $types ? $types[0]->name : '-';
                    
                    echo '<tr>';
                    echo '<td><a href="' . get_edit_post_link($apartament_id) . '">' . get_the_title() . '</a></td>';
                    echo '<td>' . ($is_occupied ? 
                        '<span class="status-badge status-confirmed">Занят</span>' : 
                        '<span class="status-badge status-completed">Свободен</span>') . '</td>';
                    echo '<td>' . esc_html($type_name) . '</td>';
                    echo '<td>';
                    echo '<a href="' . admin_url('post.php?post=' . $apartament_id . '&action=edit') . '" class="button button-small">Редактировать</a> ';
                    echo '<a href="' . admin_url('post-new.php?post_type=sun_booking&apartament_id=' . $apartament_id) . '" class="button button-small">Забронировать</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p>Нет апартаментов.</p>';
            }
            
            wp_reset_postdata();
        }
    }
}

// Инициализируем класс для главного меню плагина
if (class_exists('sunApartamentMenu')) {
   new sunApartamentMenu();
}