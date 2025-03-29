<?php
/**
 * Класс для управления календарем доступности апартаментов
 */
if (!class_exists('SunApartamentAvailabilityCalendar')) {
    class SunApartamentAvailabilityCalendar {
        public function register() {
            add_action('admin_menu', [$this, 'add_calendar_page']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        }
        
        /**
         * Добавление страницы календаря в меню админки
         */
        public function add_calendar_page() {
            add_submenu_page(
                'edit.php?post_type=sun_booking',
                'Календарь доступности',
                'Календарь доступности',
                'manage_options',
                'sun-booking-calendar',
                [$this, 'render_calendar_page']
            );
        }
        
        /**
         * Подключение необходимых скриптов и стилей
         */
        public function enqueue_scripts($hook) {
            if ($hook != 'sun_booking_page_sun-booking-calendar') {
                return;
            }
            
            // Добавляем inline стили напрямую в head
            add_action('admin_head', [$this, 'add_calendar_styles']);
        }
        
        /**
         * Добавление стилей календаря непосредственно в head
         */
        public function add_calendar_styles() {
            ?>
            <style>
                /* Базовые стили таблицы */
                .sun-calendar-wrapper {
                    overflow-x: auto;
                    margin-top: 20px;
                }
                .sun-calendar {
                    border-collapse: collapse;
                    width: 100%;
                    min-width: 1200px;
                }
                .sun-calendar th,
                .sun-calendar td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: center;
                    position: relative; /* Важно для правильного позиционирования относительных элементов */
                }
                .sun-calendar th {
                    background-color: #f8f8f8;
                    position: sticky;
                    top: 0;
                    z-index: 10;
                }
                .sun-calendar th.month-header {
                    background-color: #e9e9e9;
                }
                .sun-calendar td.apartament-name {
                    background-color: #f8f8f8;
                    position: sticky;
                    left: 0;
                    z-index: 5;
                    min-width: 200px;
                    text-align: left;
                    font-weight: bold;
                }
                
                /* Важно! Стили статусов доступности */
                .sun-calendar td.booked {
                    background-color: #ffcccc !important; /* Красный для занятых */
                }
                .sun-calendar td.available {
                    background-color: #ccffcc !important; /* Зеленый для свободных */
                }
                .sun-calendar td.checkin {
                    background: linear-gradient(135deg, #ccffcc 0%, #ccffcc 49%, #ffcccc 51%, #ffcccc 100%) !important;
                }
                .sun-calendar td.checkout {
                    background: linear-gradient(135deg, #ffcccc 0%, #ffcccc 49%, #ccffcc 51%, #ccffcc 100%) !important;
                }
                
                /* Дополнительные стили */
                .sun-calendar .booking-info {
                    font-size: 11px;
                    color: #666;
                    text-shadow: 0px 0px 2px white;
                    font-weight: bold;
                    cursor: pointer;
                }
                .sun-calendar-filters {
                    margin-bottom: 20px;
                    display: flex;
                    gap: 15px;
                    align-items: flex-end;
                }
                .sun-calendar-filter-group {
                    margin-bottom: 10px;
                }
                .sun-calendar-filter-group label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: bold;
                }
                .month-navigation {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 15px;
                }
                
                /* Исправлено! Новые стили для всплывающей подсказки */
                .booking-tooltip {
                    position: fixed; /* Использование фиксированного позиционирования */
                    z-index: 1000;
                    background-color: #fff;
                    border: 1px solid #ddd;
                    padding: 10px;
                    border-radius: 4px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                    font-size: 12px;
                    max-width: 300px;
                    display: none;
                    pointer-events: none; /* Подсказка не мешает взаимодействию */
                }
                .booking-tooltip dl {
                    margin: 0;
                }
                .booking-tooltip dt {
                    font-weight: bold;
                    margin-top: 5px;
                }
                .booking-tooltip dd {
                    margin-left: 0;
                    margin-bottom: 5px;
                }
                
                /* Легенда */
                .legend {
                    margin-top: 20px;
                    display: flex;
                    gap: 15px;
                    flex-wrap: wrap;
                }
                .legend-item {
                    display: flex;
                    align-items: center;
                    font-size: 12px;
                    margin-bottom: 10px;
                }
                .legend-color {
                    width: 20px;
                    height: 20px;
                    margin-right: 5px;
                    border: 1px solid #ddd;
                }
                .legend-color.available {
                    background-color: #ccffcc; /* Зеленый */
                }
                .legend-color.booked {
                    background-color: #ffcccc; /* Красный */
                }
                .legend-color.checkin {
                    background: linear-gradient(135deg, #ccffcc 0%, #ccffcc 49%, #ffcccc 51%, #ffcccc 100%);
                }
                .legend-color.checkout {
                    background: linear-gradient(135deg, #ffcccc 0%, #ffcccc 49%, #ccffcc 51%, #ccffcc 100%);
                }
                
                /* Выделение выходных */
                .sun-calendar th.weekend {
                    background-color: #f0f0f0;
                }
            </style>
            <?php
        }
        
        /**
         * Отображение страницы календаря
         */
        public function render_calendar_page() {
            // Определяем текущий месяц и год
            $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
            $year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
            
            // Проверяем корректность месяца
            if ($month < 1 || $month > 12) {
                $month = intval(date('m'));
            }
            
            // Получаем список апартаментов
            $apartament_filter = isset($_GET['apartament']) ? intval($_GET['apartament']) : 0;
            
            $apartaments_args = [
                'post_type' => 'apartament',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ];
            
            if ($apartament_filter > 0) {
                $apartaments_args['p'] = $apartament_filter;
            }
            
            $apartaments = get_posts($apartaments_args);
            
            // Определяем период для отображения (текущий месяц + следующие 2)
            $start_date = new DateTime("$year-$month-01");
            $end_date = clone $start_date;
            $end_date->modify('+2 months');
            $end_date->modify('last day of this month');
            
            // Навигация по месяцам
            $prev_month = $month - 1;
            $prev_year = $year;
            if ($prev_month < 1) {
                $prev_month = 12;
                $prev_year--;
            }
            
            $next_month = $month + 1;
            $next_year = $year;
            if ($next_month > 12) {
                $next_month = 1;
                $next_year++;
            }
            
            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">Календарь доступности апартаментов</h1>
                
                <div class="sun-calendar-filters">
                    <form method="get">
                        <input type="hidden" name="post_type" value="sun_booking">
                        <input type="hidden" name="page" value="sun-booking-calendar">
                        
                        <div class="sun-calendar-filter-group">
                            <label for="apartament">Апартамент:</label>
                            <select name="apartament" id="apartament">
                                <option value="0">Все апартаменты</option>
                                <?php foreach ($apartaments as $apt): ?>
                                    <option value="<?php echo $apt->ID; ?>" <?php selected($apartament_filter, $apt->ID); ?>>
                                        <?php echo esc_html($apt->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="sun-calendar-filter-group">
                            <label for="month">Месяц:</label>
                            <select name="month" id="month">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>>
                                        <?php echo date_i18n('F', mktime(0, 0, 0, $m, 1, $year)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="sun-calendar-filter-group">
                            <label for="year">Год:</label>
                            <select name="year" id="year">
                                <?php
                                $current_year = intval(date('Y'));
                                for ($y = $current_year - 1; $y <= $current_year + 2; $y++):
                                ?>
                                    <option value="<?php echo $y; ?>" <?php selected($year, $y); ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="sun-calendar-filter-group">
                            <button type="submit" class="button button-primary">Применить</button>
                        </div>
                    </form>
                </div>
                
                <div class="month-navigation">
                    <a href="<?php echo add_query_arg(['month' => $prev_month, 'year' => $prev_year]); ?>" class="button">&larr; Предыдущий месяц</a>
                    <a href="<?php echo add_query_arg(['month' => $next_month, 'year' => $next_year]); ?>" class="button">Следующий месяц &rarr;</a>
                </div>
                
                <div class="sun-calendar-wrapper">
                    <table class="sun-calendar">
                        <thead>
                            <tr>
                                <th></th>
                                <?php
                                // Генерируем заголовки с месяцами
                                $current_date = clone $start_date;
                                $current_month = $current_date->format('m');
                                $days_in_month = intval($current_date->format('t'));
                                
                                // Отображаем название первого месяца
                                echo '<th colspan="' . $days_in_month . '" class="month-header">' . date_i18n('F Y', $current_date->getTimestamp()) . '</th>';
                                
                                // Переходим к следующему месяцу
                                $current_date->modify('first day of next month');
                                $next_month_1 = $current_date->format('m');
                                $days_in_next_month_1 = intval($current_date->format('t'));
                                
                                // Отображаем название второго месяца
                                echo '<th colspan="' . $days_in_next_month_1 . '" class="month-header">' . date_i18n('F Y', $current_date->getTimestamp()) . '</th>';
                                
                                // Переходим к третьему месяцу
                                $current_date->modify('first day of next month');
                                $next_month_2 = $current_date->format('m');
                                $days_in_next_month_2 = intval($current_date->format('t'));
                                
                                // Отображаем название третьего месяца
                                echo '<th colspan="' . $days_in_next_month_2 . '" class="month-header">' . date_i18n('F Y', $current_date->getTimestamp()) . '</th>';
                                ?>
                            </tr>
                            <tr>
                                <th>Апартамент</th>
                                <?php
                                // Генерируем заголовки с числами
                                $current_date = clone $start_date;
                                
                                // Дни первого месяца
                                for ($i = 1; $i <= $days_in_month; $i++) {
                                    $day_class = '';
                                    // Выделяем выходные
                                    $weekday = date('N', strtotime($current_date->format('Y-m-') . $i));
                                    if ($weekday >= 6) { // 6 - суббота, 7 - воскресенье
                                        $day_class = ' class="weekend"';
                                    }
                                    echo '<th' . $day_class . '>' . $i . '</th>';
                                }
                                
                                // Дни второго месяца
                                $current_date->modify('first day of next month');
                                for ($i = 1; $i <= $days_in_next_month_1; $i++) {
                                    $day_class = '';
                                    $weekday = date('N', strtotime($current_date->format('Y-m-') . $i));
                                    if ($weekday >= 6) {
                                        $day_class = ' class="weekend"';
                                    }
                                    echo '<th' . $day_class . '>' . $i . '</th>';
                                }
                                
                                // Дни третьего месяца
                                $current_date->modify('first day of next month');
                                for ($i = 1; $i <= $days_in_next_month_2; $i++) {
                                    $day_class = '';
                                    $weekday = date('N', strtotime($current_date->format('Y-m-') . $i));
                                    if ($weekday >= 6) {
                                        $day_class = ' class="weekend"';
                                    }
                                    echo '<th' . $day_class . '>' . $i . '</th>';
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Генерируем строки для каждого апартамента
                            foreach ($apartaments as $apartament):
                                $apartament_id = $apartament->ID;
                                
                                // Получаем забронированные даты
                                $booked_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
                                if (!is_array($booked_dates)) {
                                    $booked_dates = [];
                                }
                                
                                // Получаем старые даты для обратной совместимости
                                $old_availability = get_post_meta($apartament_id, '_apartament_availability', true);
                                $old_dates = [];
                                if ($old_availability) {
                                    $old_dates = json_decode($old_availability, true);
                                }
                                
                                // Получаем все бронирования для этого апартамента
                                $all_bookings = $this->get_apartament_bookings($apartament_id);
                                
                                echo '<tr>';
                                echo '<td class="apartament-name">' . esc_html($apartament->post_title) . '</td>';
                                
                                // Заполняем ячейки для первого месяца
                                $current_date = clone $start_date;
                                for ($i = 1; $i <= $days_in_month; $i++) {
                                    $date_string = $current_date->format('Y-m-') . sprintf('%02d', $i);
                                    $this->render_day_cell($date_string, $booked_dates, $old_dates, $all_bookings);
                                }
                                
                                // Заполняем ячейки для второго месяца
                                $current_date->modify('first day of next month');
                                for ($i = 1; $i <= $days_in_next_month_1; $i++) {
                                    $date_string = $current_date->format('Y-m-') . sprintf('%02d', $i);
                                    $this->render_day_cell($date_string, $booked_dates, $old_dates, $all_bookings);
                                }
                                
                                // Заполняем ячейки для третьего месяца
                                $current_date->modify('first day of next month');
                                for ($i = 1; $i <= $days_in_next_month_2; $i++) {
                                    $date_string = $current_date->format('Y-m-') . sprintf('%02d', $i);
                                    $this->render_day_cell($date_string, $booked_dates, $old_dates, $all_bookings);
                                }
                                
                                echo '</tr>';
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color available"></div>
                        <span>Свободно</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color booked"></div>
                        <span>Занято</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color checkin"></div>
                        <span>День заезда</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color checkout"></div>
                        <span>День выезда</span>
                    </div>
                </div>
                
                <!-- Общий контейнер для всплывающих подсказок -->
                <div id="global-tooltip" class="booking-tooltip" style="display: none;"></div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const globalTooltip = document.getElementById('global-tooltip');
                    const tooltipTriggers = document.querySelectorAll('.booking-info');
                    
                    tooltipTriggers.forEach(trigger => {
                        trigger.addEventListener('mouseenter', function(e) {
                            // Получаем данные о бронировании
                            const bookingId = this.getAttribute('data-booking-id');
                            const dayType = this.getAttribute('data-day-type');
                            const bookingNumber = this.getAttribute('data-booking-number');
                            const guestName = this.getAttribute('data-guest-name');
                            const dates = this.getAttribute('data-dates');
                            
                            // Формируем HTML для подсказки
                            let tooltipContent = '<dl>';
                            tooltipContent += '<dt>Номер бронирования:</dt>';
                            tooltipContent += '<dd>' + bookingNumber + '</dd>';
                            
                            tooltipContent += '<dt>Гость:</dt>';
                            tooltipContent += '<dd>' + guestName + '</dd>';
                            
                            tooltipContent += '<dt>Даты:</dt>';
                            tooltipContent += '<dd>' + dates + '</dd>';
                            
                            if (dayType === 'checkin') {
                                tooltipContent += '<dt>Статус:</dt>';
                                tooltipContent += '<dd><strong>День заезда</strong></dd>';
                            } else if (dayType === 'checkout') {
                                tooltipContent += '<dt>Статус:</dt>';
                                tooltipContent += '<dd><strong>День выезда</strong></dd>';
                            }
                            
                            if (bookingId) {
                                tooltipContent += '<dt>Действия:</dt>';
                                tooltipContent += '<dd><a href="' + wpAdminURL + 'post.php?post=' + bookingId + '&action=edit">Редактировать бронирование</a></dd>';
                            }
                            
                            tooltipContent += '</dl>';
                            
                            // Устанавливаем содержимое и отображаем подсказку
                            globalTooltip.innerHTML = tooltipContent;
                            globalTooltip.style.display = 'block';
                            
                            // Позиционируем подсказку рядом с курсором
                            const x = e.clientX + 10;
                            const y = e.clientY + 10;
                            
                            // Проверяем, не выходит ли подсказка за пределы экрана
                            const rect = globalTooltip.getBoundingClientRect();
                            const maxX = window.innerWidth - rect.width - 20;
                            const maxY = window.innerHeight - rect.height - 20;
                            
                            globalTooltip.style.left = Math.min(x, maxX) + 'px';
                            globalTooltip.style.top = Math.min(y, maxY) + 'px';
                        });
                        
                        trigger.addEventListener('mouseleave', function() {
                            // Скрываем подсказку при уходе курсора
                            setTimeout(() => {
                                if (!globalTooltip.matches(':hover')) {
                                    globalTooltip.style.display = 'none';
                                }
                            }, 100);
                        });
                    });
                    
                    // Обработчик для самой подсказки
                    globalTooltip.addEventListener('mouseleave', function() {
                        this.style.display = 'none';
                    });
                    
                    // Добавляем обработчик движения мыши для обновления позиции подсказки
                    document.addEventListener('mousemove', function(e) {
                        if (globalTooltip.style.display === 'block') {
                            const x = e.clientX + 10;
                            const y = e.clientY + 10;
                            
                            const rect = globalTooltip.getBoundingClientRect();
                            const maxX = window.innerWidth - rect.width - 20;
                            const maxY = window.innerHeight - rect.height - 20;
                            
                            globalTooltip.style.left = Math.min(x, maxX) + 'px';
                            globalTooltip.style.top = Math.min(y, maxY) + 'px';
                        }
                    });
                });
                
                // Добавляем URL админки для использования в JavaScript
                var wpAdminURL = '<?php echo admin_url(); ?>';
                </script>
            </div>
            <?php
        }
        
        /**
         * Отображение ячейки дня
         */
        private function render_day_cell($date_ymd, $booked_dates, $old_dates, $all_bookings) {
            $is_booked = false;
            $booking_id = null;
            $is_checkin = false;
            $is_checkout = false;
            $cell_class = 'available';
            $booking_data = null;
            
            // Сначала проверяем, является ли дата днем заезда или выезда для какого-либо бронирования
            foreach ($all_bookings as $booking) {
                // Преобразуем даты из формата d.m.Y в Y-m-d для сравнения
                $checkin_ymd = date('Y-m-d', strtotime(str_replace('.', '-', $booking['checkin_date'])));
                $checkout_ymd = date('Y-m-d', strtotime(str_replace('.', '-', $booking['checkout_date'])));
                
                if ($date_ymd == $checkin_ymd) {
                    $is_checkin = true;
                    $booking_id = $booking['id'];
                    $booking_data = $booking;
                    break;
                }
                
                if ($date_ymd == $checkout_ymd) {
                    $is_checkout = true;
                    $booking_id = $booking['id'];
                    $booking_data = $booking;
                    break;
                }
                
                // Проверяем, если день между заездом и выездом
                if ($date_ymd > $checkin_ymd && $date_ymd < $checkout_ymd) {
                    $is_booked = true;
                    $booking_id = $booking['id'];
                    $booking_data = $booking;
                }
            }
            
            // Проверяем общую занятость (если еще не определили как заезд/выезд)
            if (!$is_checkin && !$is_checkout && !$is_booked) {
                if (isset($booked_dates[$date_ymd])) {
                    $is_booked = true;
                    $booking_id = $booked_dates[$date_ymd];
                    
                    // Пытаемся найти дополнительную информацию о бронировании
                    foreach ($all_bookings as $booking) {
                        if ($booking['id'] == $booking_id) {
                            $booking_data = $booking;
                            break;
                        }
                    }
                } elseif (is_array($old_dates) && in_array($date_ymd, $old_dates)) {
                    $is_booked = true;
                }
            }
            
            // Определяем класс ячейки в зависимости от статуса
            if ($is_checkin) {
                $cell_class = 'checkin';
            } elseif ($is_checkout) {
                $cell_class = 'checkout';
            } elseif ($is_booked) {
                $cell_class = 'booked';
            }
            
            echo '<td class="' . $cell_class . '">';
            
            // В зависимости от статуса дня, отображаем соответствующую информацию
            if ($is_checkin) {
                // День заезда
                echo $this->create_booking_trigger('Заезд', $booking_data, 'checkin');
            } elseif ($is_checkout) {
                // День выезда
                echo $this->create_booking_trigger('Выезд', $booking_data, 'checkout');
            } elseif ($is_booked && $booking_data) {
                // Обычный занятый день с известным бронированием
                echo $this->create_booking_trigger($booking_data['title'], $booking_data, 'booked');
            } elseif ($is_booked) {
                // Занятый день без детальной информации
                echo '<div class="booking-info">Занято</div>';
            } else {
                echo '&nbsp;';
            }
            
            echo '</td>';
        }
        
        /**
         * Создание триггера подсказки с атрибутами данных
         */
        private function create_booking_trigger($text, $booking, $day_type = '') {
            if (!$booking) {
                return '<div class="booking-info">' . esc_html($text) . '</div>';
            }
            
            $booking_id = isset($booking['id']) ? $booking['id'] : '';
            $booking_number = isset($booking['title']) ? $booking['title'] : '';
            $guest_name = isset($booking['guest_name']) ? $booking['guest_name'] : '';
            $dates = '';
            
            if (isset($booking['checkin_date']) && isset($booking['checkout_date'])) {
                $dates = $booking['checkin_date'] . ' — ' . $booking['checkout_date'];
            }
            
            return '<div class="booking-info" data-booking-id="' . esc_attr($booking_id) . '" data-day-type="' . esc_attr($day_type) . '" data-booking-number="' . esc_attr($booking_number) . '" data-guest-name="' . esc_attr($guest_name) . '" data-dates="' . esc_attr($dates) . '">' . esc_html($text) . '</div>';
        }
        
        /**
         * Получение всех бронирований для апартамента
         */
        private function get_apartament_bookings($apartament_id) {
            $bookings = [];
            
            // Получаем бронирования из Custom Post Type
            $args = [
                'post_type' => 'sun_booking',
                'posts_per_page' => -1,
                'meta_query' => [
                    [
                        'key' => '_booking_apartament_id',
                        'value' => $apartament_id,
                        'compare' => '='
                    ]
                ],
                'post_status' => ['pending', 'confirmed', 'completed', 'publish']
            ];
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $booking_id = get_the_ID();
                    
                    $bookings[] = [
                        'id' => $booking_id,
                        'title' => get_the_title(),
                        'checkin_date' => get_post_meta($booking_id, '_booking_checkin_date', true),
                        'checkout_date' => get_post_meta($booking_id, '_booking_checkout_date', true),
                        'guest_name' => get_post_meta($booking_id, '_booking_last_name', true) . ' ' . get_post_meta($booking_id, '_booking_first_name', true),
                        'status' => get_post_status()
                    ];
                }
                wp_reset_postdata();
            }
            
            return $bookings;
        }
    }
}

// Инициализация класса
if (class_exists('SunApartamentAvailabilityCalendar')) {
    $sunApartamentAvailabilityCalendar = new SunApartamentAvailabilityCalendar();
    $sunApartamentAvailabilityCalendar->register();
}