<?php
if (!class_exists('sunApartamentBookingForm')) {
    class sunApartamentBookingForm {
        public function register() {
            add_shortcode('sunapartament_booking_form', [$this, 'render_booking_form']);
            add_action('init', [$this, 'handle_booking_form']);
            // Добавляем скрипты и стили
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        }

        public function enqueue_scripts() {
            
            
            // Передаем локализованные сообщения в JavaScript
            wp_localize_script('sunapartament-booking-form', 'sunApartamentL10n', array(
                'minStayError' => 'Минимальный срок бронирования - 7 ночей.',
                'selectDatesError' => 'Пожалуйста, выберите даты заезда и выезда.',
                'minGuestsError' => 'Должен быть выбран хотя бы один взрослый.',
                'calendarNotLoaded' => 'Календарь не загружен. Пожалуйста, обновите страницу.'
            ));
            
            // Добавляем meta viewport для корректного отображения на мобильных устройствах
            add_action('wp_head', function() {
                echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
            });
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
                <form class="" id="sunapartament-booking-form" method="post">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="row">
                                <!-- Скрытые поля для хранения реальных значений -->
                                <input type="hidden" id="checkin_date" name="checkin_date" value="">
                                <input type="hidden" id="checkout_date" name="checkout_date" value="">
                                <input type="hidden" id="guest_count" name="guest_count" value="1">
                                <input type="hidden" id="children_count" name="children_count" value="0">
                                
                                <div class="col-12 col-xl-6 mb-3">
                                    <label class="detail-info__text mb-1" for="checkin_date_display">Заезд</label>
                                    <div id="checkin_date_display" class="form-input date-display" data-target="checkin_date">
                                        <span class="date-placeholder">Выберите дату</span>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-xl-6 mb-3">
                                    <label class="detail-info__text mb-1" for="checkout_date_display">Выезд</label>
                                    <div id="checkout_date_display" class="form-input date-display" data-target="checkout_date">
                                        <span class="date-placeholder">Выберите дату</span>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="row">
                                <div class="col-12 col-xl-6 mb-3">
                                    <label class="detail-info__text mb-1">Взрослые</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-minus" data-target="guest_count">-</button>
                                        <div id="guest_count_display" class="form-input counter-display text-center">1</div>
                                        <button type="button" class="btn btn-plus" data-target="guest_count">+</button>
                                    </div>
                                </div>
                                
                                <div class="col-12 col-xl-6 mb-3">
                                    <label class="detail-info__text mb-1">Дети</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-minus" data-target="children_count">-</button>
                                        <div id="children_count_display" class="form-input counter-display text-center">0</div>
                                        <button type="button" class="btn btn-plus" data-target="children_count">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-12 mt-2 align-self-center d-flex justify-content-center">
                            <button class="col-sm book-btn" type="submit" name="sunapartament_booking_submit">Показать свободные даты</button>  
                        </div>
                    </div>
                </form>
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
                                <!-- Скрытые поля для хранения реальных значений -->
                                <input type="hidden" id="checkin_date" name="checkin_date" value="">
                                <input type="hidden" id="checkout_date" name="checkout_date" value="">
                                <input type="hidden" id="guest_count" name="guest_count" value="1">
                                <input type="hidden" id="children_count" name="children_count" value="0">
                                
                                <div class="col mb-3">
                                    <label class="detail-info__text mb-1" for="checkin_date_display">Заезд</label>
                                    <div id="checkin_date_display" class="form-input date-display" data-target="checkin_date">
                                        <span class="date-placeholder">Дата заезда</span>
                                    </div>
                                </div>
                                
                                <div class="col mb-3">
                                    <label class="detail-info__text mb-1" for="checkout_date_display">Выезд</label>
                                    <div id="checkout_date_display" class="form-input date-display" data-target="checkout_date">
                                        <span class="date-placeholder">Дата выезда</span>
                                    </div>
                                </div>
                                
                                <div class="col mb-3">
                                    <label class="detail-info__text mb-1">Взрослые</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-minus" data-target="guest_count">-</button>
                                        <div id="guest_count_display" class="form-input counter-display text-center">1</div>
                                        <button type="button" class="btn btn-plus" data-target="guest_count">+</button>
                                    </div>
                                </div>
                                
                                <div class="col mb-3">
                                    <label class="detail-info__text mb-1">Дети</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-minus" data-target="children_count">-</button>
                                        <div id="children_count_display" class="form-input counter-display text-center">0</div>
                                        <button type="button" class="btn btn-plus" data-target="children_count">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-2 mt-2 align-self-center d-flex justify-content-center">
                            <button class="col-12 card-btn" type="submit" name="sunapartament_booking_submit">Показать</button>  
                        </div>
                    </div>
                </form>
            </div>
            <?php
        }

        // Остальные методы класса без изменений
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
                exit;
            }
        }
    }
}

if (class_exists('sunApartamentBookingForm')) {
    $sunApartamentBookingForm = new sunApartamentBookingForm();
    $sunApartamentBookingForm->register();
}