<?php
if (!class_exists('sunApartamentBookingForm')) {
    class sunApartamentBookingForm {
        public function register() {
            add_shortcode('sunapartament_booking_form', [$this, 'render_booking_form']);
            add_action('init', [$this, 'handle_booking_form']);
            add_action('template_redirect', [$this, 'handle_booking_submission']);
        }

        public function render_booking_form() {
            ob_start();
            
            if (is_front_page() || is_archive()) {
                $this->render_full_form();
            } elseif (is_singular()) {
                $this->render_compact_form();
            }
    
            return ob_get_clean();
        }

        private function render_compact_form() {
            ?> 
            <div class="compact-booking-form">
                <form class="form-group" id="sunapartament-booking-form" method="post">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="col-12 mb-3">
                            <label class="detail-info__text mb-1" for="checkin_date">Дата заезда:</label>
                            <input class="form-input" type="text" id="checkin_date" name="checkin_date" placeholder="Выберите дату" required>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="detail-info__text mb-1" for="checkout_date">Дата выезда:</label>
                            <input class="form-input" type="text" id="checkout_date" name="checkout_date" placeholder="Выберите дату" required>
                        </div>
                       
                        <div class="col-12 mb-3">
                            <label class="detail-info__text mb-1" for="guest_count">Количество взрослых:</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-minus" data-target="guest_count">-</button>
                                <input class="form-input text-center" type="number" id="guest_count" name="guest_count" min="1" value="1" required>
                                <button type="button" class="btn btn-plus" data-target="guest_count">+</button>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="detail-info__text mb-1" for="children_count">Количество детей:</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-minus" data-target="children_count">-</button>
                                <input class="form-input text-center" type="number" id="children_count" name="children_count" min="0" value="0" required>
                                <button type="button" class="btn btn-plus" data-target="children_count">+</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-12 mt-2 align-self-center d-flex justify-content-center">
                        <button class="col-sm card-btn" type="submit" name="sunapartament_booking_submit">Узнать свободные даты</button>  
                    </div>
                </div>
            </form>

            <style>
                .input-group .btn {
                    position: absolute;
                    color: #fff;
                    top: 50%;
                    transform: translateY(-50%);
                    background-color: #305261;
                    border: none;
                    cursor: pointer;
                    font-size: 16px;
                }
                .input-group .btn:hover { color: #fff; }
                .input-group .btn-minus { left: 0; }
                .input-group .btn-plus { right: 0; }
                .input-group .btn:hover { background-color: #26424e; }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    flatpickr.localize(flatpickr.l10ns.ru);
                    const checkinDate = flatpickr("#checkin_date", {
                        minDate: "today",
                        dateFormat: "d.m.Y",
                        locale: "ru",
                        onChange: function(selectedDates) {
                            const minCheckoutDate = new Date(selectedDates[0]);
                            minCheckoutDate.setDate(minCheckoutDate.getDate() + 6);
                            
                            checkoutDate.set('minDate', minCheckoutDate);
                            if (checkoutDate.selectedDates[0] < minCheckoutDate) {
                                checkoutDate.setDate(minCheckoutDate);
                            }
                        }
                    });

                    const checkoutDate = flatpickr("#checkout_date", {
                        minDate: new Date().fp_incr(6),
                        dateFormat: "d.m.Y",
                        locale: "ru",
                    });

                    document.querySelectorAll('.btn-minus, .btn-plus').forEach(button => {
                        button.addEventListener('click', function() {
                            const targetId = this.getAttribute('data-target');
                            const input = document.getElementById(targetId);
                            let value = parseInt(input.value, 10);
                            value = this.classList.contains('btn-minus') 
                                ? Math.max(value - 1, parseInt(input.min, 10)) 
                                : value + 1;
                            input.value = value;
                        });
                    });
                });
            </script>
            </div>
            <?php
        }
    
        private function render_full_form() {
            ?> 
            <div class="full-booking-form">
            <form class="form-group" id="sunapartament-booking-form" method="post">
                <div class="row">
                    <div class="col-xl-10">
                        <div class="row row-cols-1 row-cols-xl-4 row-cols-lg-4 row-cols-md-2 section-grid">
                            <div class="col mb-3">
                                <label class="detail-info__text mb-1" for="checkin_date">Дата заезда:</label>
                                <input class="form-input" type="text" id="checkin_date" name="checkin_date" placeholder="Выберите дату" required>
                            </div>
                            
                            <div class="col mb-3">
                                <label class="detail-info__text mb-1" for="checkout_date">Дата выезда:</label>
                                <input class="form-input" type="text" id="checkout_date" name="checkout_date" placeholder="Выберите дату" required>
                            </div>
                           
                            <div class="col mb-3">
                                <label class="detail-info__text mb-1" for="guest_count">Количество взрослых:</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-minus" data-target="guest_count">-</button>
                                    <input class="form-input text-center" type="number" id="guest_count" name="guest_count" min="1" value="1" required>
                                    <button type="button" class="btn btn-plus" data-target="guest_count">+</button>
                                </div>
                            </div>
                            
                            <div class="col mb-3">
                                <label class="detail-info__text mb-1" for="children_count">Количество детей:</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-minus" data-target="children_count">-</button>
                                    <input class="form-input text-center" type="number" id="children_count" name="children_count" min="0" value="0" required>
                                    <button type="button" class="btn btn-plus" data-target="children_count">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 mt-2 align-self-center d-flex justify-content-center">
                        <button class="col-sm card-btn" type="submit" name="sunapartament_booking_submit">Показать</button>  
                    </div>
                </div>
            </form>

            <style>
                .input-group .btn {
                    position: absolute;
                    color: #fff;
                    top: 50%;
                    transform: translateY(-50%);
                    background-color: #305261;
                    border: none;
                    cursor: pointer;
                    font-size: 16px;
                }
                .input-group .btn:hover { color: #fff; }
                .input-group .btn-minus { left: 0; }
                .input-group .btn-plus { right: 0; }
                .input-group .btn:hover { background-color: #26424e; }
            </style>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    flatpickr.localize(flatpickr.l10ns.ru);
                    const checkinDate = flatpickr("#checkin_date", {
                        minDate: "today",
                        dateFormat: "d.m.Y",
                        locale: "ru",
                        onChange: function(selectedDates) {
                            const minCheckoutDate = new Date(selectedDates[0]);
                            minCheckoutDate.setDate(minCheckoutDate.getDate() + 6);
                            
                            checkoutDate.set('minDate', minCheckoutDate);
                            if (checkoutDate.selectedDates[0] < minCheckoutDate) {
                                checkoutDate.setDate(minCheckoutDate);
                            }
                        }
                    });

                    const checkoutDate = flatpickr("#checkout_date", {
                        minDate: new Date().fp_incr(6),
                        dateFormat: "d.m.Y",
                        locale: "ru",
                    });

                    document.querySelectorAll('.btn-minus, .btn-plus').forEach(button => {
                        button.addEventListener('click', function() {
                            const targetId = this.getAttribute('data-target');
                            const input = document.getElementById(targetId);
                            let value = parseInt(input.value, 10);
                            value = this.classList.contains('btn-minus') 
                                ? Math.max(value - 1, parseInt(input.min, 10)) 
                                : value + 1;
                            input.value = value;
                        });
                    });
                });
            </script>
            </div>
            <?php
        }

        public function handle_booking_form() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sunapartament_booking_submit'])) {
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Pragma: no-cache");
                header("Expires: 0");
        
                $checkin_date = sanitize_text_field($_POST['checkin_date'] ?? '');
                $checkout_date = sanitize_text_field($_POST['checkout_date'] ?? '');
                $guest_count = intval($_POST['guest_count'] ?? 0);
                $children_count = intval($_POST['children_count'] ?? 0);
        
                // Проверка дат
                $checkin = DateTime::createFromFormat('d.m.Y', $checkin_date);
                $checkout = DateTime::createFromFormat('d.m.Y', $checkout_date);

                // Проверим, что объекты даты созданы успешно
                if ($checkin === false || $checkout === false) {
                    // Обработка ошибки формата даты
                    wp_die('Неверный формат даты. Пожалуйста, используйте формат дд.мм.гггг');
                    return;
                }

                $interval = $checkin->diff($checkout);

                if ($interval->days < 6) {
                    // Вместо прямого вывода, используйте wp_die или установите сообщение в сессию
                    wp_die('Минимальный срок бронирования - 7 ночей.');
                    return;
                }

                if ($checkin < new DateTime(current_time('d.m.Y'))) {
                    wp_die('Дата заезда не может быть раньше текущей даты.');
                    return;
                }
                
                // Остальные проверки и редирект
                $results_page_url = add_query_arg([
                    'checkin_date' => $checkin_date,
                    'checkout_date' => $checkout_date,
                    'guest_count' => $guest_count,
                    'children_count' => $children_count,
                ], home_url('/results'));
                
                wp_redirect($results_page_url);
                exit; // Добавьте эту строку!
            }
        }
        
        /**
         * Обработка отправки формы бронирования на странице бронирования
         */
        public function handle_booking_submission() {
            // Проверяем, что это POST запрос с данными бронирования
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['apartament_id'])) {
                return;
            }
            
            // Получаем данные формы
            $apartament_id = intval($_POST['apartament_id']);
            $checkin_date = sanitize_text_field($_POST['checkin_date']);
            $checkout_date = sanitize_text_field($_POST['checkout_date']);
            $total_price = floatval($_POST['total_price']);
            $guest_count = isset($_POST['guest_count']) ? intval($_POST['guest_count']) : 1;
            $children_count = isset($_POST['children_count']) ? intval($_POST['children_count']) : 0;
            
            // Собираем данные клиента
            $booking_data = [
                'apartament_id' => $apartament_id,
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'middle_name' => sanitize_text_field($_POST['middle_name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'checkin_date' => $checkin_date,
                'checkout_date' => $checkout_date,
                'guest_count' => $guest_count,
                'children_count' => $children_count,
                'payment_method' => sanitize_text_field($_POST['payment_method']),
                'total_price' => $total_price,
                'status' => 'confirmed' // статус по умолчанию
            ];
            
            // Проверяем существование класса управления бронированиями
            global $sunApartamentAvailability;
            if (!isset($sunApartamentAvailability) || !is_object($sunApartamentAvailability)) {
                // Если класс не инициализирован, используем старый метод сохранения
                $this->legacy_booking_submission($booking_data);
                return;
            }
            
            // Создаем новое бронирование через класс sunApartamentAvailability
            $booking_id = $sunApartamentAvailability->create_booking($booking_data);
            
            // Обрабатываем ошибки
            if (is_wp_error($booking_id)) {
                // Выводим сообщение об ошибке
                $error_message = $booking_id->get_error_message();
                wp_die($error_message);
                return;
            }
            
            // Добавляем данные бронирования в глобальный объект для шаблона
            $booking = $this->get_booking_data($booking_id);
            $GLOBALS['current_booking'] = $booking;
            
            // Отправляем уведомление о бронировании
            $this->send_booking_notification($booking);
        }
        
        /**
         * Получение данных бронирования для отображения
         */
        private function get_booking_data($booking_id) {
            // Получаем основные данные поста
            $booking_post = get_post($booking_id);
            
            // Формируем массив данных
            $booking = array(
                'id' => $booking_id,
                'booking_number' => $booking_post->post_title,
                'apartament_id' => get_post_meta($booking_id, '_booking_apartament_id', true),
                'apartament_title' => get_the_title(get_post_meta($booking_id, '_booking_apartament_id', true)),
                'first_name' => get_post_meta($booking_id, '_booking_first_name', true),
                'last_name' => get_post_meta($booking_id, '_booking_last_name', true),
                'middle_name' => get_post_meta($booking_id, '_booking_middle_name', true),
                'email' => get_post_meta($booking_id, '_booking_email', true),
                'phone' => get_post_meta($booking_id, '_booking_phone', true),
                'checkin_date' => get_post_meta($booking_id, '_booking_checkin_date', true),
                'checkout_date' => get_post_meta($booking_id, '_booking_checkout_date', true),
                'guest_count' => get_post_meta($booking_id, '_booking_guest_count', true),
                'children_count' => get_post_meta($booking_id, '_booking_children_count', true),
                'total_price' => get_post_meta($booking_id, '_booking_total_price', true),
                'payment_method' => get_post_meta($booking_id, '_booking_payment_method', true),
                'status' => $booking_post->post_status,
                'created_at' => $booking_post->post_date,
            );
            
            return $booking;
        }
        
        /**
         * Отправка уведомления о бронировании
         */
        private function send_booking_notification($booking) {
            // Отправка письма администратору
            $admin_email = get_option('admin_email');
            $site_name = get_bloginfo('name');
            
            $subject = 'Новое бронирование #' . $booking['booking_number'] . ' на сайте ' . $site_name;
            
            $message = "Поступило новое бронирование #" . $booking['booking_number'] . ".\n\n";
            $message .= "Апартамент: " . $booking['apartament_title'] . "\n";
            $message .= "Даты: " . $booking['checkin_date'] . " — " . $booking['checkout_date'] . "\n";
            $message .= "Гость: " . $booking['last_name'] . " " . $booking['first_name'] . " " . $booking['middle_name'] . "\n";
            $message .= "Email: " . $booking['email'] . "\n";
            $message .= "Телефон: " . $booking['phone'] . "\n";
            $message .= "Гости: " . $booking['guest_count'] . " взрослых, " . $booking['children_count'] . " детей\n";
            $message .= "Способ оплаты: " . $this->get_payment_method_label($booking['payment_method']) . "\n";
            $message .= "Сумма: " . number_format($booking['total_price'], 0, '.', ' ') . " ₽\n\n";
            $message .= "Ссылка для управления: " . admin_url('post.php?post=' . $booking['id'] . '&action=edit') . "\n";
            
            wp_mail($admin_email, $subject, $message);
            
            // Отправка письма клиенту
            $guest_subject = 'Подтверждение бронирования #' . $booking['booking_number'] . ' на сайте ' . $site_name;
            
            $guest_message = "Уважаемый(ая) " . $booking['first_name'] . " " . $booking['last_name'] . "!\n\n";
            $guest_message .= "Ваше бронирование успешно создано.\n\n";
            $guest_message .= "Номер бронирования: " . $booking['booking_number'] . "\n";
            $guest_message .= "Апартамент: " . $booking['apartament_title'] . "\n";
            $guest_message .= "Даты проживания: " . $booking['checkin_date'] . " — " . $booking['checkout_date'] . "\n";
            $guest_message .= "Гости: " . $booking['guest_count'] . " взрослых, " . $booking['children_count'] . " детей\n";
            $guest_message .= "Способ оплаты: " . $this->get_payment_method_label($booking['payment_method']) . "\n";
            $guest_message .= "Сумма к оплате: " . number_format($booking['total_price'], 0, '.', ' ') . " ₽\n\n";
            $guest_message .= "Если у вас возникли вопросы, пожалуйста, свяжитесь с нами.\n\n";
            $guest_message .= "С уважением,\nКоманда " . $site_name;
            
            wp_mail($booking['email'], $guest_subject, $guest_message);
        }
        
        /**
         * Получение названия способа оплаты
         */
        private function get_payment_method_label($payment_method) {
            $payment_methods = [
                'card' => 'Банковская карта',
                'cash' => 'Наличными при заселении',
                'transfer' => 'Банковский перевод'
            ];
            
            return isset($payment_methods[$payment_method]) ? $payment_methods[$payment_method] : $payment_method;
        }
        
        /**
         * Устаревший метод обработки бронирования (для обратной совместимости)
         */
        private function legacy_booking_submission($booking_data) {
            $apartament_id = $booking_data['apartament_id'];
            $checkin_date = $booking_data['checkin_date'];
            $checkout_date = $booking_data['checkout_date'];
            
            // Генерация диапазона дат
            $start = DateTime::createFromFormat('d.m.Y', $checkin_date);
            $end = DateTime::createFromFormat('d.m.Y', $checkout_date);
            $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $date_range = new DatePeriod($start, $interval, $end);

            $booked_dates = [];
            foreach ($date_range as $date) {
                $booked_dates[] = $date->format('Y-m-d');
            }

            // Обновление метаполя для дат
            if ($apartament_id > 0 && !empty($booked_dates)) {
                $existing_dates = get_post_meta($apartament_id, '_apartament_availability', true);
                $existing_dates = $existing_dates ? json_decode($existing_dates, true) : [];
                $updated_dates = array_unique(array_merge($existing_dates, $booked_dates));
                update_post_meta($apartament_id, '_apartament_availability', json_encode(array_values($updated_dates)));
            }

            // Генерируем уникальный ID для бронирования
            $booking_id = 'DC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            
            // Сохраняем информацию о бронировании
            $bookings = get_post_meta($apartament_id, '_apartament_bookings', true);
            $bookings = $bookings ? json_decode($bookings, true) : [];
            $bookings[$booking_id] = $booking_data;
            
            update_post_meta($apartament_id, '_apartament_bookings', json_encode($bookings));
            
            // Устанавливаем глобальную переменную для шаблона
            $booking_data['booking_number'] = $booking_id;
            $booking_data['apartament_title'] = get_the_title($apartament_id);
            $GLOBALS['current_booking'] = $booking_data;
            
            // Отправляем уведомление
            $this->send_booking_notification($booking_data);
        }
    }
}

if (class_exists('sunApartamentBookingForm')) {
    $sunApartamentBookingForm = new sunApartamentBookingForm();
    $sunApartamentBookingForm->register();
}