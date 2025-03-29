<?php
if (!class_exists('sunApartamentBookingForm')) {
    class sunApartamentBookingForm {
        public function register() {
            add_shortcode('sunapartament_booking_form', [$this, 'render_booking_form']);
            add_action('init', [$this, 'handle_booking_form']);
            
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
                        <div class="col-12 col-xl-6 mb-3">
                                <label class="detail-info__text mb-1" for="checkin_date">Заезд</label>
                                <input class="form-input date-range-input" type="text" id="checkin_date" name="checkin_date" placeholder="Выберите дату" required>
                            </div>
                            
                            <div class="col-12 col-xl-6 mb-3">
                                <label class="detail-info__text mb-1" for="checkout_date">Выезд</label>
                                <input class="form-input date-range-input flatpickr-input" type="text" id="checkout_date" name="checkout_date" placeholder="Выберите дату" required readonly>
                            </div>
                        </div>
                            
                        <div class="row">
                            <div class="col-12 col-xl-6 mb-3">
                            <label class="detail-info__text mb-1" for="guest_count">Взрослые</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-minus" data-target="guest_count">-</button>
                                <input class="form-input text-center" type="number" id="guest_count" name="guest_count" min="1" value="1" required readonly>
                                <button type="button" class="btn btn-plus" data-target="guest_count">+</button>
                            </div>
                        </div>
                        
                        <div class="col-12 col-xl-6 mb-3">
                            <label class="detail-info__text mb-1" for="children_count">Дети</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-minus" data-target="children_count">-</button>
                                <input class="form-input text-center" type="number" id="children_count" name="children_count" min="0" value="0" required readonly>
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

            <style>
                
            </style>

            
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
                                    <label class="detail-info__text mb-1" for="checkin_date">Заезд</label>
                                    <input class="form-input date-range-input" type="text" id="checkin_date" name="checkin_date" placeholder="Дата заезда" required>
                                </div>
                                
                                <div class="col mb-3">
                                    <label class="detail-info__text mb-1" for="checkout_date">Выезд</label>
                                    <input class="form-input date-range-input flatpickr-input" type="text" id="checkout_date" name="checkout_date" placeholder="Дата выезда" required readonly>
                                </div>
                           
                           
                            <div class="col mb-3">
                                <label class="detail-info__text mb-1" for="guest_count">Взрослые</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-minus" data-target="guest_count">-</button>
                                    <input class="form-input text-center" type="number" id="guest_count" name="guest_count" min="1" value="1" required readonly>
                                    <button type="button" class="btn btn-plus" data-target="guest_count">+</button>
                                </div>
                            </div>
                            
                            <div class="col mb-3">
                                <label class="detail-info__text mb-1" for="children_count">Дети</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-minus" data-target="children_count">-</button>
                                    <input class="form-input text-center" type="number" id="children_count" name="children_count" min="0" value="0" required readonly>
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
                exit; // Добавьте эту строку!
            }
        }
        
    }
}

if (class_exists('sunApartamentBookingForm')) {
    $sunApartamentBookingForm = new sunApartamentBookingForm();
    $sunApartamentBookingForm->register();
}