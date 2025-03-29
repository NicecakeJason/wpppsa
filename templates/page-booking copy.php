<?php
/*
Template Name: Booking Page
*/
get_header('flat');
?>

<style>
/* Стиль в духе Домклик/Посуточно.ру */
:root {
    --primary-color: #0085ff;
    --primary-hover: #0068cc;
    --secondary-color: #f2f7fd;
    --success-color: #34c759;
    --danger-color: #ff3b30;
    --warning-color: #ffb547;
    --text-color: #333333;
    --text-secondary: #717171;
    --bg-color: #f5f7fa;
    --border-color: #e6e9ec;
    --container-shadow: 0 2px 14px rgba(0, 0, 0, 0.08);
    --border-radius: 8px;
}

body {
    background-color: var(--bg-color);
    font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    color: var(--text-color);
    -webkit-font-smoothing: antialiased;
}

.booking-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 16px 40px;
}

.booking-container {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
}

@media (max-width: 992px) {
    .booking-container {
        grid-template-columns: 1fr;
    }
}

.booking-title {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 24px;
    color: var(--text-color);
}

.booking-block {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--container-shadow);
    overflow: hidden;
    margin-bottom: 20px;
}

.block-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
}

.block-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.block-content {
    padding: 20px;
}

.property-info {
    display: flex;
    margin-bottom: 24px;
}

.property-image {
    width: 160px;
    height: 120px;
    border-radius: var(--border-radius);
    object-fit: cover;
    margin-right: 20px;
}

.property-details {
    flex: 1;
}

.property-name {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 8px;
}

.property-address {
    font-size: 14px;
    color: var(--text-secondary);
    margin: 0 0 12px;
}

.property-features {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 12px;
}

.feature-item {
    display: flex;
    align-items: center;
    padding: 6px 12px;
    background-color: var(--secondary-color);
    border-radius: 20px;
    font-size: 14px;
}

.feature-icon {
    margin-right: 6px;
    width: 16px;
    height: 16px;
}

.form-block {
    margin-bottom: 24px;
}

.form-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 16px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.form-field {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    height: 48px;
    padding: 0 16px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 16px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
}

.form-control:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 40px;
}

.booking-btn {
    display: block;
    width: 100%;
    height: 48px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: var(--border-radius);
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.booking-btn:hover {
    background-color: var(--primary-hover);
}

.date-row {
    display: flex;
    margin-bottom: 20px;
}

.date-box {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
}

.date-box:first-child {
    margin-right: 16px;
}

.date-label {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.date-value {
    font-size: 16px;
    font-weight: 500;
}

.price-info {
    margin-bottom: 20px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 14px;
}

.price-total {
    display: flex;
    justify-content: space-between;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 1px solid var(--border-color);
}

.total-label {
    font-size: 16px;
    font-weight: 600;
}

.total-value {
    font-size: 20px;
    font-weight: 600;
    color: var(--primary-color);
}

.booking-info {
    background-color: var(--secondary-color);
    padding: 16px;
    border-radius: var(--border-radius);
    margin-top: 20px;
    font-size: 14px;
    line-height: 1.5;
}

.booking-success {
    background-color: #f1fbf3;
    border: 1px solid #d6ebd9;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 30px;
}

.success-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--success-color);
    margin: 0 0 16px;
    display: flex;
    align-items: center;
}

.success-title svg {
    margin-right: 10px;
}

.success-details {
    margin-top: 20px;
    padding: 16px;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.detail-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.detail-label {
    color: var(--text-secondary);
}

.detail-value {
    font-weight: 500;
}

.detail-value.highlight {
    color: var(--primary-color);
    font-weight: 600;
}

.price-breakdown {
    font-size: 13px;
    color: var(--text-secondary);
    margin-top: 10px;
}

.price-per-night {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    color: var(--text-secondary);
}

.price-per-night span {
    font-weight: 500;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .property-info {
        flex-direction: column;
    }
    
    .property-image {
        width: 100%;
        height: auto;
        margin-right: 0;
        margin-bottom: 16px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .date-row {
        flex-direction: column;
    }
    
    .date-box:first-child {
        margin-right: 0;
        margin-bottom: 16px;
    }
}

</style>

<div class="booking-page">
    <h1 class="booking-title">Бронирование апартамента</h1>
    
    <?php
    if (isset($_GET['apartament_id'])) {
        $apartament_id = intval($_GET['apartament_id']);
        $checkin_date = isset($_GET['checkin_date']) ? sanitize_text_field($_GET['checkin_date']) : '';
        $checkout_date = isset($_GET['checkout_date']) ? sanitize_text_field($_GET['checkout_date']) : '';
        
        // Получаем информацию об апартаменте
        $gallery_images = get_post_meta($apartament_id, 'sunapartament_gallery', true);
        $first_image_url = $gallery_images ? wp_get_attachment_image_src($gallery_images[0], 'large')[0] : '';
        $title = get_the_title($apartament_id);
        $square_footage = get_post_meta($apartament_id, 'sunapartament_square_footage', true);
        $guest_count = get_post_meta($apartament_id, 'sunapartament_guest_count', true);
        $floor_count = get_post_meta($apartament_id, 'sunapartament_floor_count', true);
        
        // Расчет стоимости бронирования
        $total_price = 0;
        $days_count = 0;
        $nights_count = 0;
        $daily_prices = [];
        
        if ($checkin_date && $checkout_date) {
            // Создаем экземпляр класса цен
            $sunApartamentPrice = new sunApartamentPrice();
            
            // Получаем цены на каждый день выбранного периода
            $period_prices = $sunApartamentPrice->get_prices_for_period($apartament_id, $checkin_date, $checkout_date);
            
            // Считаем общую стоимость и количество дней
            $days_count = count($period_prices);
            $nights_count = $days_count - 1;
            
            foreach ($period_prices as $date => $price) {
                // Если цена не указана или не числовая, используем цену по умолчанию
                if ($price === 'Цена не указана' || !is_numeric($price)) {
                    $default_price = get_post_meta($apartament_id, 'sunapartament_default_price', true);
                    $price = $default_price ? $default_price : 0;
                }
                
                $price = floatval($price);
                $total_price += $price;
                $daily_prices[$date] = $price;
            }
            
            // Сервисный сбор (5% от общей стоимости)
            $service_fee = round($total_price * 0.05);
            // Итоговая сумма
            $final_price = $total_price + $service_fee;
        }
        
        // Функция для склонения слова "ночь"
        function pluralize_nights($number) {
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
        
        // Обработка отправки формы бронирования
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $apartament_id = intval($_POST['apartament_id']);
            $checkin_date = sanitize_text_field($_POST['checkin_date']);
            $checkout_date = sanitize_text_field($_POST['checkout_date']);
            $total_price = floatval($_POST['total_price']);
            
            // Генерация диапазона дат
            $start = new DateTime($checkin_date);
            $end = new DateTime($checkout_date);
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

            // Собираем данные клиента
            $client_data = [
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'middle_name' => sanitize_text_field($_POST['middle_name']),
                'email' => sanitize_email($_POST['email']),
                'phone' => sanitize_text_field($_POST['phone']),
                'checkin_date' => $checkin_date,
                'checkout_date' => $checkout_date,
                'payment_method' => sanitize_text_field($_POST['payment_method']),
                'booking_date' => current_time('mysql'),
                'total_price' => $total_price,
                'status' => 'confirmed' // статус по умолчанию
            ];

            // Генерируем уникальный ID для бронирования
           // Генерируем уникальный ID для бронирования
$booking_id = 'DC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

// Собираем данные клиента
$client_data = [
    'first_name' => sanitize_text_field($_POST['first_name']),
    'last_name' => sanitize_text_field($_POST['last_name']),
    'middle_name' => sanitize_text_field($_POST['middle_name']),
    'email' => sanitize_email($_POST['email']),
    'phone' => sanitize_text_field($_POST['phone']),
    'checkin_date' => $checkin_date,
    'checkout_date' => $checkout_date,
    'payment_method' => sanitize_text_field($_POST['payment_method']),
    'booking_date' => current_time('mysql'),
    'total_price' => $total_price,
    'status' => 'confirmed'
];

// МЕТОД 1: Прямое создание записи - используем ТОЛЬКО этот метод
// Создаем запись Sun Booking с заданным ID
$post_id = wp_insert_post([
    'post_type' => 'sun_booking',
    'post_title' => $booking_id,
    'post_status' => 'confirmed',
    'post_author' => get_current_user_id(),
]);

if (!is_wp_error($post_id)) {
    // Устанавливаем все необходимые мета-поля
    update_post_meta($post_id, '_booking_apartament_id', $apartament_id);
    update_post_meta($post_id, '_booking_first_name', $client_data['first_name']);
    update_post_meta($post_id, '_booking_last_name', $client_data['last_name']);
    update_post_meta($post_id, '_booking_middle_name', $client_data['middle_name']);
    update_post_meta($post_id, '_booking_email', $client_data['email']);
    update_post_meta($post_id, '_booking_phone', $client_data['phone']);
    update_post_meta($post_id, '_booking_checkin_date', $client_data['checkin_date']);
    update_post_meta($post_id, '_booking_checkout_date', $client_data['checkout_date']);
    update_post_meta($post_id, '_booking_total_price', $client_data['total_price']);
    update_post_meta($post_id, '_booking_payment_method', $client_data['payment_method']);
    update_post_meta($post_id, '_booking_created_at', current_time('mysql'));
    
    // Также добавляем бронирование в старую структуру
    $bookings = get_post_meta($apartament_id, '_apartament_bookings', true);
    $bookings = $bookings ? json_decode($bookings, true) : [];
    $bookings[$booking_id] = $client_data;
    $bookings[$booking_id]['booking_id'] = $post_id; // Добавляем ссылку
    update_post_meta($apartament_id, '_apartament_bookings', json_encode($bookings));
    
    // Добавляем занятые даты в _apartament_booked_dates
    $booked_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
    if (!is_array($booked_dates)) {
        $booked_dates = [];
    }
    
    $start = new DateTime($checkin_date);
    $end = new DateTime($checkout_date);
    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($start, $interval, $end);
    
    foreach ($daterange as $date) {
        $date_string = $date->format('Y-m-d');
        $booked_dates[$date_string] = $post_id;
    }
    update_post_meta($apartament_id, '_apartament_booked_dates', $booked_dates);
    
    // НЕ ИСПОЛЬЗУЕМ ЭТОТ МЕТОД - метод API класса
    // if (class_exists('sunApartamentAvailability')) {
    //    $booking_manager = new sunApartamentAvailability();
    //    $booking_result = $booking_manager->create_booking(...);
    // }
}
            
           
            
            // Выводим сообщение об успешном бронировании
            ?>
            <div class="booking-success">
                <h2 class="success-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Ваше бронирование подтверждено
                </h2>
                
                <p>Бронирование апартамента "<?php echo esc_html(get_the_title($apartament_id)); ?>" успешно оформлено. Информация о бронировании отправлена на указанный email: <?php echo esc_html($_POST['email']); ?></p>
                
                <div class="success-details">
                    <div class="detail-item">
                        <div class="detail-label">Номер бронирования:</div>
                        <div class="detail-value"><?php echo $booking_id; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Даты проживания:</div>
                        <div class="detail-value">
                            <?php echo date('d.m.Y', strtotime($checkin_date)); ?> — 
                            <?php echo date('d.m.Y', strtotime($checkout_date)); ?> 
                            (<?php echo $nights_count; ?> <?php echo pluralize_nights($nights_count); ?>)
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Способ оплаты:</div>
                        <div class="detail-value">
                            <?php 
                            $payment_methods = [
                                'card' => 'Банковская карта',
                                'cash' => 'Наличными при заселении',
                                'transfer' => 'Банковский перевод'
                            ];
                            echo isset($payment_methods[$_POST['payment_method']]) ? $payment_methods[$_POST['payment_method']] : $_POST['payment_method'];
                            ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Итоговая стоимость:</div>
                        <div class="detail-value highlight"><?php echo number_format($total_price, 0, '.', ' '); ?> ₽</div>
                    </div>
                </div>
            </div>
            <?php
        }
        
        // Отображаем форму бронирования
        ?>
        <div class="booking-container">
            <div class="booking-left">
                <div class="booking-block">
                    <div class="block-header">
                        <h2 class="block-title">Информация о бронировании</h2>
                    </div>
                    
                    <div class="block-content">
                        <div class="property-info">
                            <img class="property-image" src="<?php echo esc_url($first_image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                            
                            <div class="property-details">
                                <h3 class="property-name"><?php echo esc_html($title); ?></h3>
                                
                                <div class="property-features">
                                    <?php if ($square_footage): ?>
                                        <div class="feature-item">
                                            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                                            <?php echo esc_html($square_footage); ?> м²
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($floor_count): ?>
                                        <div class="feature-item">
                                            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 20V8M2 8v12M6 16v4M18 16v4M14 16v4M10 16v4M2 12h20M14 8V6c0-1.1-.9-2-2-2H8c-1.1 0-2 .9-2 2v2h8z"/></svg>
                                            <?php echo esc_html($floor_count); ?> этаж
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($guest_count): ?>
                                        <div class="feature-item">
                                            <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                            До <?php echo esc_html($guest_count); ?> гостей
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($checkin_date && $checkout_date): ?>
                            <div class="date-row">
                                <div class="date-box">
                                    <div class="date-label">Заезд</div>
                                    <div class="date-value"><?php echo date('d.m.Y', strtotime($checkin_date)); ?></div>
                                </div>
                                
                                <div class="date-box">
                                    <div class="date-label">Выезд</div>
                                    <div class="date-value"><?php echo date('d.m.Y', strtotime($checkout_date)); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="booking-block">
                    <div class="block-header">
                        <h2 class="block-title">Данные гостя</h2>
                    </div>
                    
                    <div class="block-content">
                        <form id="booking-form" method="post">
                            <input type="hidden" name="apartament_id" value="<?php echo $apartament_id; ?>">
                            <input type="hidden" name="checkin_date" value="<?php echo esc_attr($checkin_date); ?>">
                            <input type="hidden" name="checkout_date" value="<?php echo esc_attr($checkout_date); ?>">
                            <input type="hidden" name="total_price" value="<?php echo isset($final_price) ? $final_price : 0; ?>">
                            
                            <div class="form-block">
                                <h3 class="form-title">Персональные данные</h3>
                                
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label class="form-label" for="last_name">Фамилия</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="first_name">Имя</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="middle_name">Отчество</label>
                                        <input type="text" id="middle_name" name="middle_name" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-block">
                                <h3 class="form-title">Контактная информация</h3>
                                
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-field">
                                        <label class="form-label" for="phone">Телефон</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-block">
                                <h3 class="form-title">Способ оплаты</h3>
                                
                                <div class="form-field">
                                    <select id="payment_method" name="payment_method" class="form-control" required>
                                        <option value="card">Банковская карта</option>
                                        <option value="cash">Наличными при заезде</option>
                                        <option value="transfer">Банковский перевод</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-field">
                                <button type="submit" class="booking-btn">Забронировать</button>
                            </div>
                            
                            <div class="booking-info">
                                <p>Нажимая кнопку "Забронировать", вы соглашаетесь с <a href="#">правилами бронирования</a> и <a href="#">условиями проживания</a>.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="booking-right">
                <div class="booking-block" style="position: sticky; top: 20px;">
                    <div class="block-header">
                        <h2 class="block-title">Детали бронирования</h2>
                    </div>
                    
                    <div class="block-content">
                        <?php if ($checkin_date && $checkout_date): ?>
                            <div class="price-info">
                                <div class="price-row">
                                    <div>Проживание</div>
                                    <div><?php echo number_format($total_price, 0, '.', ' '); ?> ₽</div>
                                </div>
                                
                                <div class="price-breakdown">
                                    <?php echo $nights_count; ?> <?php echo pluralize_nights($nights_count); ?> × 
                                    <?php echo number_format($total_price / $nights_count, 0, '.', ' '); ?> ₽
                                    
                                    <?php if (count(array_unique($daily_prices)) > 1): ?>
                                        <span class="price-per-night">(цена меняется в зависимости от дат)</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="price-row">
                                    <div>Сервисный сбор</div>
                                    <div><?php echo number_format($service_fee, 0, '.', ' '); ?> ₽</div>
                                </div>
                                
                                <div class="price-total">
                                    <div class="total-label">Итого</div>
                                    <div class="total-value"><?php echo number_format($final_price, 0, '.', ' '); ?> ₽</div>
                                </div>
                            </div>
                            
                            <div class="booking-info">
                                <p>Бесплатная отмена бронирования за 48 часов до заезда. После этого срока удерживается стоимость первых суток проживания.</p>
                            </div>
                        <?php else: ?>
                            <p>Для расчета стоимости выберите даты заезда и выезда.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        // Если апартамент не выбран
        ?>
        <div class="booking-block">
            <div class="block-header">
                <h2 class="block-title">Выберите апартамент</h2>
            </div>
            
            <div class="block-content" style="text-align: center; padding: 40px 20px;">
                <p style="margin-bottom: 20px;">Для оформления бронирования необходимо выбрать апартамент.</p>
                
                <a href="<?php echo home_url('/apartaments/'); ?>" style="display: inline-block; padding: 12px 24px; background-color: var(--primary-color); color: white; text-decoration: none; border-radius: var(--border-radius); font-weight: 500; transition: background-color 0.2s;">
                    Перейти к выбору апартаментов
                </a>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php get_footer(); ?>