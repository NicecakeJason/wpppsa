<?php
if (!class_exists('sunApartamentAvailability')) {
    class sunApartamentAvailability {
        public function register() {
            add_action('init', [$this, 'fix_db_encoding']);
            add_action('add_meta_boxes', [$this, 'add_booking_meta_boxes']);
            add_action('save_post', [$this, 'save_booking_meta'], 10, 2);
            add_action('manage_sun_booking_posts_custom_column', [$this, 'populate_booking_columns'], 10, 2);
            add_filter('manage_sun_booking_posts_columns', [$this, 'add_booking_columns']);
            add_filter('manage_edit-sun_booking_sortable_columns', [$this, 'make_booking_columns_sortable']);
            add_action('before_delete_post', [$this, 'delete_booking_dates']);
        }
        
        /**
         * Функция исправления кодировки
         */
        public function fix_db_encoding() {
            global $wpdb;
            $wpdb->query("SET NAMES 'utf8mb4'");
            $wpdb->query("SET CHARACTER SET 'utf8mb4'");
        }
        
        /**
         * Функция для декодирования unicode последовательностей в строке
         */
        private function decode_unicode_string($str) {
            if (empty($str)) return '';
            
            // Проверяем, содержит ли строка unicode последовательности
            if (preg_match('/u([0-9a-f]{4})/i', $str)) {
                return preg_replace_callback('/u([0-9a-f]{4})/i', function ($matches) {
                    return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
                }, $str);
            }
            
            return $str;
        }
        
        /**
         * Функция для исправления массива бронирований
         */
        private function fix_booking_data($booking) {
            if (!is_array($booking)) return $booking;
            
            $fixed_booking = $booking;
            
            // Исправляем имя, фамилию и отчество
            if (isset($booking['first_name'])) {
                $fixed_booking['first_name'] = $this->decode_unicode_string($booking['first_name']);
            }
            
            if (isset($booking['last_name'])) {
                $fixed_booking['last_name'] = $this->decode_unicode_string($booking['last_name']);
            }
            
            if (isset($booking['middle_name'])) {
                $fixed_booking['middle_name'] = $this->decode_unicode_string($booking['middle_name']);
            }
            
            return $fixed_booking;
        }
        
        /**
         * Добавление метабоксов для бронирований
         */
        public function add_booking_meta_boxes() {
            add_meta_box(
                'booking_details',
                'Детали бронирования',
                [$this, 'render_booking_details_metabox'],
                'sun_booking',
                'normal',
                'high'
            );
            
            add_meta_box(
                'booking_guest',
                'Данные гостя',
                [$this, 'render_booking_guest_metabox'],
                'sun_booking',
                'normal',
                'default'
            );
            
            add_meta_box(
                'booking_dates',
                'Даты проживания',
                [$this, 'render_booking_dates_metabox'],
                'sun_booking',
                'side',
                'default'
            );
        }
        
        /**
         * Отображение метабокса с деталями бронирования
         */
        public function render_booking_details_metabox($post) {
            wp_nonce_field(basename(__FILE__), 'booking_details_nonce');
            
            $apartament_id = get_post_meta($post->ID, '_booking_apartament_id', true);
            $total_price = get_post_meta($post->ID, '_booking_total_price', true);
            $payment_method = get_post_meta($post->ID, '_booking_payment_method', true);
            $guest_count = get_post_meta($post->ID, '_booking_guest_count', true) ?: 1;
            $children_count = get_post_meta($post->ID, '_booking_children_count', true) ?: 0;
            
            // Получаем список всех апартаментов
            $apartaments = get_posts(array(
                'post_type' => 'apartament',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC',
            ));
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="booking_apartament_id">Апартамент</label></th>
                    <td>
                        <select name="booking_apartament_id" id="booking_apartament_id" required>
                            <option value="">- Выберите апартамент -</option>
                            <?php foreach ($apartaments as $apartament) : ?>
                                <option value="<?php echo $apartament->ID; ?>" <?php selected($apartament_id, $apartament->ID); ?>>
                                    <?php echo esc_html($apartament->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_guest_count">Количество взрослых</label></th>
                    <td>
                        <input type="number" name="booking_guest_count" id="booking_guest_count" value="<?php echo esc_attr($guest_count); ?>" min="1" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_children_count">Количество детей</label></th>
                    <td>
                        <input type="number" name="booking_children_count" id="booking_children_count" value="<?php echo esc_attr($children_count); ?>" min="0" />
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_total_price">Общая стоимость (₽)</label></th>
                    <td>
                        <input type="number" name="booking_total_price" id="booking_total_price" value="<?php echo esc_attr($total_price); ?>" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_payment_method">Способ оплаты</label></th>
                    <td>
                        <select name="booking_payment_method" id="booking_payment_method">
                            <option value="card" <?php selected($payment_method, 'card'); ?>>Банковская карта</option>
                            <option value="cash" <?php selected($payment_method, 'cash'); ?>>Наличными при заезде</option>
                            <option value="transfer" <?php selected($payment_method, 'transfer'); ?>>Банковский перевод</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php
        }
        
        /**
         * Отображение метабокса с данными гостя
         */
        public function render_booking_guest_metabox($post) {
            $first_name = get_post_meta($post->ID, '_booking_first_name', true);
            $last_name = get_post_meta($post->ID, '_booking_last_name', true);
            $middle_name = get_post_meta($post->ID, '_booking_middle_name', true);
            $email = get_post_meta($post->ID, '_booking_email', true);
            $phone = get_post_meta($post->ID, '_booking_phone', true);
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="booking_last_name">Фамилия</label></th>
                    <td>
                        <input type="text" name="booking_last_name" id="booking_last_name" value="<?php echo esc_attr($last_name); ?>" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_first_name">Имя</label></th>
                    <td>
                        <input type="text" name="booking_first_name" id="booking_first_name" value="<?php echo esc_attr($first_name); ?>" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_middle_name">Отчество</label></th>
                    <td>
                        <input type="text" name="booking_middle_name" id="booking_middle_name" value="<?php echo esc_attr($middle_name); ?>" />
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_email">Email</label></th>
                    <td>
                        <input type="email" name="booking_email" id="booking_email" value="<?php echo esc_attr($email); ?>" required />
                    </td>
                </tr>
                <tr>
                    <th><label for="booking_phone">Телефон</label></th>
                    <td>
                        <input type="text" name="booking_phone" id="booking_phone" value="<?php echo esc_attr($phone); ?>" required />
                    </td>
                </tr>
            </table>
            <?php
        }
        
        /**
         * Отображение метабокса с датами проживания
         */
        public function render_booking_dates_metabox($post) {
            $checkin_date = get_post_meta($post->ID, '_booking_checkin_date', true);
            $checkout_date = get_post_meta($post->ID, '_booking_checkout_date', true);
            
            // Преобразуем формат даты для отображения
            $checkin_formatted = $checkin_date ? date('Y-m-d', strtotime(str_replace('.', '-', $checkin_date))) : '';
            $checkout_formatted = $checkout_date ? date('Y-m-d', strtotime(str_replace('.', '-', $checkout_date))) : '';
            ?>
            <p>
                <label for="booking_checkin_date">Дата заезда:</label><br>
                <input type="date" name="booking_checkin_date" id="booking_checkin_date" value="<?php echo esc_attr($checkin_formatted); ?>" required style="width: 100%;" />
            </p>
            <p>
                <label for="booking_checkout_date">Дата выезда:</label><br>
                <input type="date" name="booking_checkout_date" id="booking_checkout_date" value="<?php echo esc_attr($checkout_formatted); ?>" required style="width: 100%;" />
            </p>
            <?php
            if ($checkin_date && $checkout_date) {
                $start = new DateTime(str_replace('.', '-', $checkin_date));
                $end = new DateTime(str_replace('.', '-', $checkout_date));
                $interval = $start->diff($end);
                $nights = $interval->days;
                echo '<p>Количество ночей: <strong>' . $nights . '</strong></p>';
            }
        }
        
        /**
         * Сохранение данных бронирования
         */
        public function save_booking_meta($post_id, $post) {
            // Проверка типа поста
            if (get_post_meta($post_id, '_skip_first_save', true) === '1') {
                delete_post_meta($post_id, '_skip_first_save');
                return;
            }
            
            // Проверка, не является ли это автосохранением или массовым обновлением
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (wp_is_post_revision($post_id)) return;
            
            // НОВАЯ ПРОВЕРКА: если пост был только что создан (revision_count = 0), 
            // и у него уже есть все необходимые метаполя, пропускаем обработку
            // $revision_count = wp_get_post_revisions($post_id);
            // if (empty($revision_count) && 
            //     get_post_meta($post_id, '_booking_apartament_id', true) && 
            //     get_post_meta($post_id, '_booking_checkin_date', true) && 
            //     get_post_meta($post_id, '_booking_checkout_date', true)) {
            //     return;
            // }
            
            // Проверка nonce
            if (!isset($_POST['booking_details_nonce']) || !wp_verify_nonce($_POST['booking_details_nonce'], basename(__FILE__))) {
                return;
            }
            
            // Проверка прав
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
            
            // Преобразование дат из формата Y-m-d в d.m.Y
            $checkin_formatted = isset($_POST['booking_checkin_date']) ? date('d.m.Y', strtotime($_POST['booking_checkin_date'])) : '';
            $checkout_formatted = isset($_POST['booking_checkout_date']) ? date('d.m.Y', strtotime($_POST['booking_checkout_date'])) : '';
            
            // Сохраняем метаданные
            if (isset($_POST['booking_apartament_id'])) {
                update_post_meta($post_id, '_booking_apartament_id', sanitize_text_field($_POST['booking_apartament_id']));
            }
            
            if (isset($_POST['booking_total_price'])) {
                update_post_meta($post_id, '_booking_total_price', sanitize_text_field($_POST['booking_total_price']));
            }
            
            if (isset($_POST['booking_payment_method'])) {
                update_post_meta($post_id, '_booking_payment_method', sanitize_text_field($_POST['booking_payment_method']));
            }
            
            if (isset($_POST['booking_guest_count'])) {
                update_post_meta($post_id, '_booking_guest_count', intval($_POST['booking_guest_count']));
            }
            
            if (isset($_POST['booking_children_count'])) {
                update_post_meta($post_id, '_booking_children_count', intval($_POST['booking_children_count']));
            }
            
            if (isset($_POST['booking_first_name'])) {
                update_post_meta($post_id, '_booking_first_name', sanitize_text_field($_POST['booking_first_name']));
            }
            
            if (isset($_POST['booking_last_name'])) {
                update_post_meta($post_id, '_booking_last_name', sanitize_text_field($_POST['booking_last_name']));
            }
            
            if (isset($_POST['booking_middle_name'])) {
                update_post_meta($post_id, '_booking_middle_name', sanitize_text_field($_POST['booking_middle_name']));
            }
            
            if (isset($_POST['booking_email'])) {
                update_post_meta($post_id, '_booking_email', sanitize_email($_POST['booking_email']));
            }
            
            if (isset($_POST['booking_phone'])) {
                update_post_meta($post_id, '_booking_phone', sanitize_text_field($_POST['booking_phone']));
            }
            
            // Обработка дат
            $old_checkin = get_post_meta($post_id, '_booking_checkin_date', true);
            $old_checkout = get_post_meta($post_id, '_booking_checkout_date', true);
            $old_apartament_id = get_post_meta($post_id, '_booking_apartament_id', true);
            
            // Обновляем даты в формате d.m.Y
            update_post_meta($post_id, '_booking_checkin_date', $checkin_formatted);
            update_post_meta($post_id, '_booking_checkout_date', $checkout_formatted);
            
            $new_apartament_id = isset($_POST['booking_apartament_id']) ? sanitize_text_field($_POST['booking_apartament_id']) : '';
            
            // Если даты или апартамент изменились, обновляем даты недоступности
            if (($old_checkin != $checkin_formatted || $old_checkout != $checkout_formatted || $old_apartament_id != $new_apartament_id) && 
                $checkin_formatted && $checkout_formatted && $new_apartament_id) {
                
                // Если был выбран другой апартамент, удаляем старые даты
                if ($old_apartament_id && $old_apartament_id != $new_apartament_id && $old_checkin && $old_checkout) {
                    $this->remove_booked_dates($old_apartament_id, $old_checkin, $old_checkout, $post_id);
                }
                
                // Если были выбраны другие даты для того же апартамента
                if ($old_apartament_id && $old_apartament_id == $new_apartament_id && 
                    ($old_checkin != $checkin_formatted || $old_checkout != $checkout_formatted) && 
                    $old_checkin && $old_checkout) {
                    $this->remove_booked_dates($old_apartament_id, $old_checkin, $old_checkout, $post_id);
                }
                
                // Добавляем новые даты
                $this->add_booked_dates($new_apartament_id, $checkin_formatted, $checkout_formatted, $post_id);
            }
            
            // Обновляем запись в старой системе бронирований для совместимости
            $this->update_legacy_booking($post_id, $new_apartament_id);
        }
        
        /**
         * Обновление бронирования в старой системе для совместимости
         */
        private function update_legacy_booking($booking_id, $apartament_id) {
            if (!$apartament_id) return;
            
            // Получаем данные бронирования
            $booking_number = get_the_title($booking_id);
            $first_name = get_post_meta($booking_id, '_booking_first_name', true);
            $last_name = get_post_meta($booking_id, '_booking_last_name', true);
            $middle_name = get_post_meta($booking_id, '_booking_middle_name', true);
            $email = get_post_meta($booking_id, '_booking_email', true);
            $phone = get_post_meta($booking_id, '_booking_phone', true);
            $checkin_date = get_post_meta($booking_id, '_booking_checkin_date', true);
            $checkout_date = get_post_meta($booking_id, '_booking_checkout_date', true);
            $payment_method = get_post_meta($booking_id, '_booking_payment_method', true);
            $total_price = get_post_meta($booking_id, '_booking_total_price', true);
            $created_at = get_post_meta($booking_id, '_booking_created_at', true) ?: get_the_date('Y-m-d H:i:s', $booking_id);
            
            // Получаем существующие бронирования
            $bookings = get_post_meta($apartament_id, '_apartament_bookings', true);
            $bookings = $bookings ? json_decode($bookings, true) : array();
            
            // ВАЖНО: Проверяем, существует ли уже бронирование с таким ID
            // Если существует и имеет связь с текущим booking_id, то пропускаем обновление
            if (isset($bookings[$booking_number]) && isset($bookings[$booking_number]['booking_id']) && 
                $bookings[$booking_number]['booking_id'] == $booking_id) {
                return true;
            }
            
            // Создаем/обновляем бронирование в старой системе
            $bookings[$booking_number] = array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'middle_name' => $middle_name,
                'email' => $email,
                'phone' => $phone,
                'checkin_date' => $checkin_date,
                'checkout_date' => $checkout_date,
                'payment_method' => $payment_method,
                'booking_date' => $created_at,
                'total_price' => $total_price,
                'status' => get_post_status($booking_id),
                'booking_id' => $booking_id // Связь со новой системой
            );
            
            // Обновляем метаполе
            update_post_meta($apartament_id, '_apartament_bookings', json_encode($bookings));
            
            return true;
        }
        
        /**
         * Добавление занятых дат в апартамент
         */
        public function add_booked_dates($apartament_id, $checkin_date, $checkout_date, $booking_id) {
            if (!$apartament_id || !$checkin_date || !$checkout_date) return;
            
            // Преобразуем даты в формат Y-m-d для работы с DateTime
            $checkin_formatted = str_replace('.', '-', $checkin_date);
            $checkout_formatted = str_replace('.', '-', $checkout_date);
            
            // Генерируем диапазон дат
            $start = new DateTime($checkin_formatted);
            $end = new DateTime($checkout_formatted);
            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod($start, $interval, $end);
            
            // Получаем существующие занятые даты
            $booked_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
            if (!is_array($booked_dates)) {
                $booked_dates = array();
            }
            
            // Добавляем даты с привязкой к ID бронирования
            foreach ($daterange as $date) {
                $date_string = $date->format('Y-m-d');
                $booked_dates[$date_string] = $booking_id;
            }
            
            // Сохраняем обновленные даты
            update_post_meta($apartament_id, '_apartament_booked_dates', $booked_dates);
            
            // Для обратной совместимости также обновляем старое поле _apartament_availability
            $old_availability = get_post_meta($apartament_id, '_apartament_availability', true);
            $old_availability = $old_availability ? json_decode($old_availability, true) : array();
            
            foreach ($daterange as $date) {
                $date_string = $date->format('Y-m-d');
                if (!in_array($date_string, $old_availability)) {
                    $old_availability[] = $date_string;
                }
            }
            
            update_post_meta($apartament_id, '_apartament_availability', json_encode(array_values($old_availability)));
            
            return true;
        }
        
        /**
         * Удаление занятых дат из апартамента
         */
        public function remove_booked_dates($apartament_id, $checkin_date, $checkout_date, $booking_id) {
            if (!$apartament_id || !$checkin_date || !$checkout_date) return;
            
            // Преобразуем даты в формат Y-m-d для работы с DateTime
            $checkin_formatted = str_replace('.', '-', $checkin_date);
            $checkout_formatted = str_replace('.', '-', $checkout_date);
            
            // Получаем существующие занятые даты
            $booked_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
            if (!is_array($booked_dates)) {
                $booked_dates = array();
            }
            
            // Генерируем диапазон дат
            $start = new DateTime($checkin_formatted);
            $end = new DateTime($checkout_formatted);
            
            // ВАЖНО: Создаем массив дат вручную, чтобы включить дату выезда
            $current = clone $start;
            $dates_to_remove = array();
            
            while ($current <= $end) {
                $date_string = $current->format('Y-m-d');
                
                // Проверяем, принадлежит ли дата текущему бронированию
                if (isset($booked_dates[$date_string]) && $booked_dates[$date_string] == $booking_id) {
                    unset($booked_dates[$date_string]);
                    $dates_to_remove[] = $date_string;
                }
                
                $current->modify('+1 day');
            }
            
            // Сохраняем обновленные даты
            update_post_meta($apartament_id, '_apartament_booked_dates', $booked_dates);
            
            // Для обратной совместимости также обновляем старое поле _apartament_availability
            $old_availability = get_post_meta($apartament_id, '_apartament_availability', true);
            if ($old_availability) {
                $old_availability = json_decode($old_availability, true);
                if (!is_array($old_availability)) {
                    $old_availability = array();
                }
                
                $old_availability = array_values(array_diff($old_availability, $dates_to_remove));
                update_post_meta($apartament_id, '_apartament_availability', json_encode($old_availability));
            }
            
            return true;
        }
        
        /**
         * Автоматическое удаление занятых дат при удалении бронирования
         */
        public function delete_booking_dates($post_id) {
            if (get_post_type($post_id) !== 'sun_booking') {
                return;
            }
            
            $apartament_id = get_post_meta($post_id, '_booking_apartament_id', true);
            $checkin_date = get_post_meta($post_id, '_booking_checkin_date', true);
            $checkout_date = get_post_meta($post_id, '_booking_checkout_date', true);
            
            if ($apartament_id && $checkin_date && $checkout_date) {
                $this->remove_booked_dates($apartament_id, $checkin_date, $checkout_date, $post_id);
                
                // Также удаляем из старой системы бронирований
                $this->remove_from_legacy_bookings($post_id, $apartament_id);
            }
        }
        
        /**
         * Удаление бронирования из старой системы
         */
        private function remove_from_legacy_bookings($booking_id, $apartament_id) {
            // Получаем номер бронирования
            $booking_number = get_the_title($booking_id);
            
            // Получаем существующие бронирования
            $bookings = get_post_meta($apartament_id, '_apartament_bookings', true);
            if (!$bookings) return;
            
            $bookings = json_decode($bookings, true);
            if (!is_array($bookings) || empty($bookings)) return;
            
            // Удаляем бронирование с данным номером
            if (isset($bookings[$booking_number])) {
                unset($bookings[$booking_number]);
                update_post_meta($apartament_id, '_apartament_bookings', json_encode($bookings));
            }
            
            return true;
        }
        
        /**
         * Добавление колонок в список бронирований в админке
         */
        public function add_booking_columns($columns) {
            $new_columns = array();
            foreach ($columns as $key => $value) {
                if ($key == 'title') {
                    $new_columns[$key] = 'Номер бронирования';
                    $new_columns['guest'] = 'Гость';
                    $new_columns['apartament'] = 'Апартамент';
                    $new_columns['dates'] = 'Даты проживания';
                    $new_columns['total_price'] = 'Стоимость';
                } else {
                    $new_columns[$key] = $value;
                }
            }
            return $new_columns;
        }
        
        /**
         * Вывод данных в колонки списка бронирований
         */
        public function populate_booking_columns($column, $post_id) {
            switch ($column) {
                case 'guest':
                    $first_name = get_post_meta($post_id, '_booking_first_name', true);
                    $last_name = get_post_meta($post_id, '_booking_last_name', true);
                    $email = get_post_meta($post_id, '_booking_email', true);
                    echo esc_html($last_name . ' ' . $first_name) . '<br>';
                    echo '<small>' . esc_html($email) . '</small>';
                    break;
                    
                case 'apartament':
                    $apartament_id = get_post_meta($post_id, '_booking_apartament_id', true);
                    if ($apartament_id) {
                        echo '<a href="' . get_edit_post_link($apartament_id) . '">' . esc_html(get_the_title($apartament_id)) . '</a>';
                    }
                    break;
                    
                case 'dates':
                    $checkin_date = get_post_meta($post_id, '_booking_checkin_date', true);
                    $checkout_date = get_post_meta($post_id, '_booking_checkout_date', true);
                    
                    if ($checkin_date && $checkout_date) {
                        echo esc_html($checkin_date) . ' — ' . esc_html($checkout_date);
                        
                        // Показываем количество ночей
                        $start = new DateTime(str_replace('.', '-', $checkin_date));
                        $end = new DateTime(str_replace('.', '-', $checkout_date));
                        $interval = $start->diff($end);
                        $nights = $interval->days;
                        echo '<br><small>' . $nights . ' ' . $this->pluralize_nights($nights) . '</small>';
                    }
                    break;
                    
                case 'total_price':
                    $total_price = get_post_meta($post_id, '_booking_total_price', true);
                    if ($total_price) {
                        echo number_format($total_price, 0, '.', ' ') . ' ₽';
                    }
                    break;
            }
        }
        
        /**
         * Сортировка колонок бронирований
         */
        public function make_booking_columns_sortable($columns) {
            $columns['guest'] = 'guest';
            $columns['apartament'] = 'apartament';
            $columns['dates'] = 'dates';
            $columns['total_price'] = 'total_price';
            return $columns;
        }
        
        /**
         * Функция склонения слова "ночь"
         */
        public function pluralize_nights($number) {
            $forms = array('ночь', 'ночи', 'ночей');
            $mod10 = $number % 10;
            $mod100 = $number % 100;

            if ($mod10 == 1 && $mod100 != 11) {
                return $forms[0];
            } elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
                return $forms[1];
            } else {
                return $forms[2];
            }
        }
        
        /**
         * Проверка доступности дат для бронирования
         *
         * @param int $apartament_id ID апартамента
         * @param string $checkin_date Дата заезда (d.m.Y)
         * @param string $checkout_date Дата выезда (d.m.Y)
         * @param int $exclude_booking_id ID бронирования для исключения при проверке
         * @return bool Доступны ли даты
         */
        public function check_dates_availability($apartament_id, $checkin_date, $checkout_date, $exclude_booking_id = null) {
            if (!$apartament_id || !$checkin_date || !$checkout_date) {
                return false;
            }
            
            // Преобразуем даты в формат Y-m-d для работы с DateTime
            $checkin_formatted = str_replace('.', '-', $checkin_date);
            $checkout_formatted = str_replace('.', '-', $checkout_date);
            
            // Получаем занятые даты
            $booked_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
            
            if (!is_array($booked_dates)) {
                $booked_dates = array();
            }
            
            // Генерируем диапазон дат для проверки
            $start = new DateTime($checkin_formatted);
            $end = new DateTime($checkout_formatted);
            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod($start, $interval, $end);
            
            // Проверяем каждую дату
            foreach ($daterange as $date) {
                $date_string = $date->format('Y-m-d');
                
                // Проверяем, занята ли дата другим бронированием
                if (isset($booked_dates[$date_string]) && 
                    ($exclude_booking_id === null || $booked_dates[$date_string] != $exclude_booking_id)) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Генерация уникального номера бронирования
         * Публичный метод для использования во фронтенде и в админке
         * 
         * @return string Номер бронирования
         */
        public function generate_booking_number() {
            $prefix = 'DC-';
            $date = date('Ymd');
            $random = strtoupper(substr(uniqid(), -4));
            
            return $prefix . $date . '-' . $random;
        }
        
        /**
         * Создание нового бронирования
         * Универсальный метод для использования как из админки, так и из фронтенда
         *
         * @param array $booking_data Данные бронирования
         * @param string $custom_booking_number Пользовательский номер бронирования (опционально)
         * @return int|WP_Error ID созданного бронирования или объект ошибки
         */
        public function create_booking($booking_data, $custom_booking_number = null) {
            // Проверка обязательных полей
            $required_fields = array('apartament_id', 'first_name', 'last_name', 'email', 'phone', 'checkin_date', 'checkout_date');
            
            foreach ($required_fields as $field) {
                if (empty($booking_data[$field])) {
                    return new WP_Error('missing_field', 'Поле ' . $field . ' обязательно для заполнения.');
                }
            }
            
            // Проверка дат
            $checkin_formatted = str_replace('.', '-', $booking_data['checkin_date']);
            $checkout_formatted = str_replace('.', '-', $booking_data['checkout_date']);
            
            $checkin = new DateTime($checkin_formatted);
            $checkout = new DateTime($checkout_formatted);
            
            if ($checkin >= $checkout) {
                return new WP_Error('invalid_dates', 'Дата выезда должна быть позже даты заезда.');
            }
            
            // Проверка доступности дат
            $is_available = $this->check_dates_availability(
                $booking_data['apartament_id'],
                $booking_data['checkin_date'],
                $booking_data['checkout_date']
            );
            
            if (!$is_available) {
                return new WP_Error('dates_not_available', 'Выбранные даты недоступны для бронирования.');
            }
            
            // Используем предоставленный номер бронирования или генерируем новый
            $booking_number = $custom_booking_number ? $custom_booking_number : $this->generate_booking_number();
            
            // ВАЖНО: Проверяем, не существует ли уже такой номер бронирования
            $existing_booking = get_posts(array(
                'post_type' => 'sun_booking',
                'post_title' => $booking_number,
                'posts_per_page' => 1,
                'post_status' => 'any'
            ));
            
            // Если такой номер уже существует и не был предоставлен пользователем, генерируем новый
            if (!empty($existing_booking) && $custom_booking_number === null) {
                $booking_number = $this->generate_booking_number();
            }
            
            // Создаем пост типа sun_booking
            $booking_id = wp_insert_post(array(
                'post_type' => 'sun_booking',
                'post_title' => $booking_number,
                'post_status' => isset($booking_data['status']) ? $booking_data['status'] : 'confirmed',
                'post_author' => get_current_user_id(),
            ));
            
            if (is_wp_error($booking_id)) {
                return $booking_id;
            }
            
            // Устанавливаем все мета-поля
            $meta_fields = array(
                'apartament_id' => '_booking_apartament_id',
                'first_name' => '_booking_first_name',
                'last_name' => '_booking_last_name',
                'middle_name' => '_booking_middle_name',
                'email' => '_booking_email',
                'phone' => '_booking_phone',
                'checkin_date' => '_booking_checkin_date',
                'checkout_date' => '_booking_checkout_date',
                'guest_count' => '_booking_guest_count',
                'children_count' => '_booking_children_count',
                'total_price' => '_booking_total_price',
                'payment_method' => '_booking_payment_method',
            );
            
            foreach ($meta_fields as $field => $meta_key) {
                if (isset($booking_data[$field])) {
                    update_post_meta($booking_id, $meta_key, $booking_data[$field]);
                }
            }
            
            // Добавляем служебные метаданные
            update_post_meta($booking_id, '_booking_created_at', current_time('mysql'));
            update_post_meta($booking_id, '_skip_first_save', '1');
            // Добавляем занятые даты
            $this->add_booked_dates(
                $booking_data['apartament_id'],
                $booking_data['checkin_date'],
                $booking_data['checkout_date'],
                $booking_id
            );
            
            // Для обратной совместимости добавляем бронирование в старую структуру
            $this->add_to_legacy_bookings($booking_data, $booking_number, $booking_id);
            
            return $booking_id;
        }
        
        /**
         * Добавление бронирования в старую структуру данных для обратной совместимости
         */
        private function add_to_legacy_bookings($booking_data, $booking_number, $booking_id) {
            $apartament_id = $booking_data['apartament_id'];
            
            // Получаем существующие бронирования
            $bookings = get_post_meta($apartament_id, '_apartament_bookings', true);
            $bookings = $bookings ? json_decode($bookings, true) : array();
            
            // Добавляем новое бронирование
            $bookings[$booking_number] = array(
                'first_name' => $booking_data['first_name'],
                'last_name' => $booking_data['last_name'],
                'middle_name' => isset($booking_data['middle_name']) ? $booking_data['middle_name'] : '',
                'email' => $booking_data['email'],
                'phone' => $booking_data['phone'],
                'checkin_date' => $booking_data['checkin_date'],
                'checkout_date' => $booking_data['checkout_date'],
                'payment_method' => isset($booking_data['payment_method']) ? $booking_data['payment_method'] : 'card',
                'booking_date' => current_time('mysql'),
                'total_price' => isset($booking_data['total_price']) ? $booking_data['total_price'] : 0,
                'status' => isset($booking_data['status']) ? $booking_data['status'] : 'confirmed',
                'booking_id' => $booking_id, // Добавляем ссылку на новое бронирование
            );
            
            // Сохраняем обновленные бронирования
            update_post_meta($apartament_id, '_apartament_bookings', json_encode($bookings));
            
            return true;
        }
        
        /**
         * Миграция существующих бронирований в новую структуру
         *
         * @param int $apartament_id ID апартамента для миграции бронирований
         * @return array Статистика миграции
         */
        public function migrate_bookings($apartament_id) {
            $stats = array(
                'total' => 0,
                'migrated' => 0,
                'errors' => 0,
            );
            
            // Получаем старые данные бронирований
            $old_bookings = get_post_meta($apartament_id, '_apartament_bookings', true);
            if (!$old_bookings) {
                return $stats;
            }
            
            $old_bookings = json_decode($old_bookings, true);
            if (!is_array($old_bookings) || empty($old_bookings)) {
                return $stats;
            }
            
            $stats['total'] = count($old_bookings);
            
            // Обрабатываем каждое бронирование
            foreach ($old_bookings as $booking_number => $booking_data) {
                // Пропускаем, если уже есть ссылка на новое бронирование
                if (isset($booking_data['booking_id']) && $booking_data['booking_id'] > 0) {
                    continue;
                }
                
                // Пропускаем, если нет обязательных данных
                if (empty($booking_data['checkin_date']) || empty($booking_data['checkout_date'])) {
                    $stats['errors']++;
                    continue;
                }
                
                // Проверяем существование бронирования с таким номером
                $existing_booking = get_posts(array(
                    'post_type' => 'sun_booking',
                    'post_title' => $booking_number,
                    'posts_per_page' => 1,
                    'post_status' => array('pending', 'confirmed', 'cancelled', 'completed', 'publish'),
                ));
                
                if (!empty($existing_booking)) {
                    // Бронирование уже существует, пропускаем
                    continue;
                }
                
                // Создаем новое бронирование
                $booking_id = wp_insert_post(array(
                    'post_type' => 'sun_booking',
                    'post_title' => $booking_number,
                    'post_status' => isset($booking_data['status']) ? $booking_data['status'] : 'confirmed',
                    'post_author' => get_current_user_id(),
                    'post_date' => isset($booking_data['booking_date']) ? $booking_data['booking_date'] : current_time('mysql'),
                ));
                
                if (is_wp_error($booking_id)) {
                    $stats['errors']++;
                    continue;
                }
                
                // Исправляем проблемы с кодировкой
                $booking_data = $this->fix_booking_data($booking_data);
                
                // Устанавливаем мета-поля
                update_post_meta($booking_id, '_booking_apartament_id', $apartament_id);
                update_post_meta($booking_id, '_booking_first_name', $booking_data['first_name']);
                update_post_meta($booking_id, '_booking_last_name', $booking_data['last_name']);
                update_post_meta($booking_id, '_booking_middle_name', isset($booking_data['middle_name']) ? $booking_data['middle_name'] : '');
                update_post_meta($booking_id, '_booking_email', $booking_data['email']);
                update_post_meta($booking_id, '_booking_phone', $booking_data['phone']);
                update_post_meta($booking_id, '_booking_checkin_date', $booking_data['checkin_date']);
                update_post_meta($booking_id, '_booking_checkout_date', $booking_data['checkout_date']);
                update_post_meta($booking_id, '_booking_total_price', isset($booking_data['total_price']) ? $booking_data['total_price'] : 0);
                update_post_meta($booking_id, '_booking_payment_method', isset($booking_data['payment_method']) ? $booking_data['payment_method'] : 'card');
                update_post_meta($booking_id, '_booking_created_at', isset($booking_data['booking_date']) ? $booking_data['booking_date'] : current_time('mysql'));
                
                // Обновляем ссылку в старой структуре
                $old_bookings[$booking_number]['booking_id'] = $booking_id;
                
                // Если статус активный, добавляем занятые даты
                if (!isset($booking_data['status']) || $booking_data['status'] != 'cancelled') {
                    $this->add_booked_dates(
                        $apartament_id,
                        $booking_data['checkin_date'],
                        $booking_data['checkout_date'],
                        $booking_id
                    );
                }
                
                $stats['migrated']++;
            }
            
            // Сохраняем обновленные бронирования с ссылками
            update_post_meta($apartament_id, '_apartament_bookings', json_encode($old_bookings));
            
            return $stats;
        }
        
        /**
         * Запуск миграции всех бронирований
         */
        public function migrate_all_bookings() {
            // Получаем все апартаменты
            $apartaments = get_posts(array(
                'post_type' => 'apartament',
                'posts_per_page' => -1,
            ));
            
            $total_stats = array(
                'total_apartaments' => count($apartaments),
                'processed_apartaments' => 0,
                'total_bookings' => 0,
                'migrated_bookings' => 0,
                'errors' => 0,
            );
            
            foreach ($apartaments as $apartament) {
                $migration_result = $this->migrate_bookings($apartament->ID);
                
                $total_stats['processed_apartaments']++;
                $total_stats['total_bookings'] += $migration_result['total'];
                $total_stats['migrated_bookings'] += $migration_result['migrated'];
                $total_stats['errors'] += $migration_result['errors'];
            }
            
            return $total_stats;
        }
    }
}

// Инициализация класса
if (class_exists('sunApartamentAvailability')) {
    $sunApartamentAvailability = new sunApartamentAvailability();
    $sunApartamentAvailability->register();
}