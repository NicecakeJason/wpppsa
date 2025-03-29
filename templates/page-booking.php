<?php
/*
Template Name: Booking Page
*/
get_header('flat');
?>

<!-- Стили остаются без изменений -->
<style>
    
</style>

<div class="booking-page">
    <h1 class="booking-title">Бронирование апартамента</h1>

    <?php
    // Проверяем и создаем таблицы, если они не существуют
    global $wpdb;
    
    $personal_data_table = $wpdb->prefix . 'sun_personal_data';
    $bookings_table = $wpdb->prefix . 'sun_bookings';
    
    $personal_data_exists = $wpdb->get_var("SHOW TABLES LIKE '$personal_data_table'") === $personal_data_table;
    $bookings_exists = $wpdb->get_var("SHOW TABLES LIKE '$bookings_table'") === $bookings_table;
    
    if (!$personal_data_exists || !$bookings_exists) {
        if (function_exists('create_booking_tables')) {
            create_booking_tables();
            error_log('Таблицы созданы на странице бронирования: personal_data=' . ($personal_data_exists ? 'да' : 'нет') . ', bookings=' . ($bookings_exists ? 'да' : 'нет'));
        } else {
            error_log('Функция create_booking_tables не найдена');
        }
    }
    
    if (isset($_GET['apartament_id'])) {
        $apartament_id = intval($_GET['apartament_id']);
        $checkin_date = isset($_GET['checkin_date']) ? sanitize_text_field($_GET['checkin_date']) : '';
        $checkout_date = isset($_GET['checkout_date']) ? sanitize_text_field($_GET['checkout_date']) : '';
        $guest_count = isset($_GET['guest_count']) ? intval($_GET['guest_count']) : 1;
        $children_count = isset($_GET['children_count']) ? intval($_GET['children_count']) : 0;
        $total_guests = $guest_count + $children_count;

        // Получаем информацию об апартаменте
        $gallery_images = get_post_meta($apartament_id, 'sunapartament_gallery', true);
        $first_image_url = $gallery_images ? wp_get_attachment_image_src($gallery_images[0], 'large')[0] : '';
        $title = get_the_title($apartament_id);
        $square_footage = get_post_meta($apartament_id, 'sunapartament_square_footage', true);
        $guest_count_max = get_post_meta($apartament_id, 'sunapartament_guest_count', true);
        $floor_count = get_post_meta($apartament_id, 'sunapartament_floor_count', true);

        // Получаем пользовательские иконки для удобств
        $square_footage_icon = get_post_meta($apartament_id, 'sunapartament_square_footage_icon', true);
        $guest_count_icon = get_post_meta($apartament_id, 'sunapartament_guest_count_icon', true);
        $floor_count_icon = get_post_meta($apartament_id, 'sunapartament_floor_count_icon', true);

        // Расчет стоимости бронирования
        $total_price = 0;
        $nights_count = 0;
        $daily_prices = [];

        if ($checkin_date && $checkout_date) {
            // Создаем экземпляр класса цен
            if(class_exists('sunApartamentPrice')) {
                $sunApartamentPrice = new sunApartamentPrice();

                // Получаем цены на каждый день выбранного периода
                $period_prices_data = $sunApartamentPrice->get_prices_for_period($apartament_id, $checkin_date, $checkout_date);
                
                // ИСПРАВЛЕНО: Правильно получаем количество ночей и другие данные
                $nights_count = $period_prices_data['nights'];
                $total_price = $period_prices_data['total_price'];
                $daily_prices = $period_prices_data['daily_prices'];

                // Итоговая сумма (без сервисного сбора)
                $final_price = $total_price;
            } else {
                // Если класс не найден, используем фиктивные данные для тестирования
                $nights_count = 1;
                $total_price = 5000;
                $final_price = $total_price;
                error_log('Класс sunApartamentPrice не найден, используются фиктивные данные');
            }
        }

        // Функция для склонения слова "ночь" (без изменений)
        if (!function_exists('pluralize_nights')) {
            function pluralize_nights($number) {
                if ($number % 10 == 1 && $number % 100 != 11) {
                    return 'ночь';
                } elseif (($number % 10 >= 2 && $number % 10 <= 4) && ($number % 100 < 10 || $number % 100 >= 20)) {
                    return 'ночи';
                } else {
                    return 'ночей';
                }
            }
        }

        // Обработка отправки формы бронирования
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $apartament_id = intval($_POST['apartament_id']);
            $checkin_date = sanitize_text_field($_POST['checkin_date']);
            $checkout_date = sanitize_text_field($_POST['checkout_date']);
            $total_price = floatval($_POST['total_price']);
            $guest_count = intval($_POST['guest_count'] ?? 1);
            $children_count = intval($_POST['children_count'] ?? 0);
            
            // Проверяем, что пользователь подтвердил условия
            $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;
            
            // Если пользователь не подтвердил условия, отображаем ошибку
            if (!$terms_accepted) {
                echo '<div class="booking-block" style="background-color: #fff3f3; border: 1px solid #ffcaca; margin-bottom: 20px;">
                    <div class="block-content">
                        <p style="color: var(--danger-color); font-weight: 500; margin: 0;">Для продолжения необходимо подтвердить согласие с правилами бронирования и условиями проживания.</p>
                    </div>
                </div>';
            } else {
                $booking_created = false;
                $booking_id = 'DC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                $post_id = 0;
                
                // Проверка существования таблиц в базе данных
                global $wpdb;
                $personal_data_table = $wpdb->prefix . 'sun_personal_data';
                $bookings_table = $wpdb->prefix . 'sun_bookings';
                
                // Включаем отображение ошибок SQL для отладки
                $wpdb->show_errors();
                
                // Проверяем существование таблиц
                $personal_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$personal_data_table'") === $personal_data_table;
                $bookings_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$bookings_table'") === $bookings_table;
                
                // Логируем результаты проверки
                error_log("Проверка таблиц перед сохранением: personal_data=" . ($personal_table_exists ? 'да' : 'нет') . ", bookings=" . ($bookings_table_exists ? 'да' : 'нет'));
                
                // Если таблицы существуют, сохраняем данные
                if ($personal_table_exists && $bookings_table_exists) {
                    try {
                        // Сохраняем данные в таблицу персональных данных
                        $personal_result = $wpdb->insert(
                            $personal_data_table,
                            [
                                'first_name' => sanitize_text_field($_POST['first_name']),
                                'last_name' => sanitize_text_field($_POST['last_name']),
                                'middle_name' => sanitize_text_field($_POST['middle_name'] ?? ''),
                                'email' => sanitize_email($_POST['email']),
                                'phone' => sanitize_text_field($_POST['phone']),
                                'created_at' => current_time('mysql')
                            ],
                            [
                                '%s', '%s', '%s', '%s', '%s', '%s'
                            ]
                        );
                        
                        if ($personal_result === false) {
                            error_log('Ошибка при добавлении персональных данных: ' . $wpdb->last_error);
                        }
                        
                        $personal_data_id = $wpdb->insert_id;
                        
                        if ($personal_data_id) {
                            // Сохраняем данные в таблицу бронирований
                            $booking_result = $wpdb->insert(
                                $bookings_table,
                                [
                                    'booking_id' => $booking_id,
                                    'personal_data_id' => $personal_data_id,
                                    'apartament_id' => $apartament_id,
                                    'checkin_date' => date('Y-m-d', strtotime($checkin_date)),
                                    'checkout_date' => date('Y-m-d', strtotime($checkout_date)),
                                    'total_price' => $total_price,
                                    'payment_method' => sanitize_text_field($_POST['payment_method']),
                                    'status' => 'confirmed',
                                    'terms_accepted' => $terms_accepted,
                                    'created_at' => current_time('mysql')
                                ],
                                [
                                    '%s', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%d', '%s'
                                ]
                            );
                            
                            if ($booking_result === false) {
                                error_log('Ошибка при добавлении бронирования: ' . $wpdb->last_error);
                            } else {
                                $booking_created = true;
                                error_log('Бронирование успешно сохранено в базу данных: personal_id=' . $personal_data_id . ', booking_id=' . $booking_id);
                                $notification_data = [
                                    'booking_id' => $booking_id,
                                    'apartament_id' => $apartament_id,
                                    'first_name' => sanitize_text_field($_POST['first_name']),
                                    'last_name' => sanitize_text_field($_POST['last_name']),
                                    'middle_name' => sanitize_text_field($_POST['middle_name'] ?? ''),
                                    'email' => sanitize_email($_POST['email']),
                                    'phone' => sanitize_text_field($_POST['phone']),
                                    'checkin_date' => $checkin_date,
                                    'checkout_date' => $checkout_date,
                                    'total_price' => $total_price,
                                    'payment_method' => sanitize_text_field($_POST['payment_method']),
                                    'guest_count' => $guest_count,
                                    'children_count' => $children_count,
                                    'created_at' => current_time('mysql')
                                ];
                                
                                // Отправляем уведомления
                                $notification_results = send_booking_notifications($notification_data);
                                
                                // Логируем результаты отправки
                                error_log('Результаты отправки уведомлений: ' . print_r($notification_results, true));
                            }
                            
                        }

                    } catch (Exception $e) {
                        error_log('Исключение при сохранении данных: ' . $e->getMessage());
                    }
                } else {
                    // Если таблиц нет, создаем их и затем пытаемся сохранить данные
                    if (function_exists('create_booking_tables')) {
                        create_booking_tables();
                        error_log('Попытка создать таблицы на лету');
                        
                        // Повторно пробуем сохранить данные
                        try {
                            // Сохраняем данные в таблицу персональных данных
                            $personal_result = $wpdb->insert(
                                $personal_data_table,
                                [
                                    'first_name' => sanitize_text_field($_POST['first_name']),
                                    'last_name' => sanitize_text_field($_POST['last_name']),
                                    'middle_name' => sanitize_text_field($_POST['middle_name'] ?? ''),
                                    'email' => sanitize_email($_POST['email']),
                                    'phone' => sanitize_text_field($_POST['phone']),
                                    'created_at' => current_time('mysql')
                                ]
                            );
                            
                            $personal_data_id = $wpdb->insert_id;
                            
                            if ($personal_data_id) {
                                // Сохраняем данные в таблицу бронирований
                                $booking_result = $wpdb->insert(
                                    $bookings_table,
                                    [
                                        'booking_id' => $booking_id,
                                        'personal_data_id' => $personal_data_id,
                                        'apartament_id' => $apartament_id,
                                        'checkin_date' => date('Y-m-d', strtotime($checkin_date)),
                                        'checkout_date' => date('Y-m-d', strtotime($checkout_date)),
                                        'total_price' => $total_price,
                                        'payment_method' => sanitize_text_field($_POST['payment_method']),
                                        'status' => 'confirmed',
                                        'terms_accepted' => $terms_accepted,
                                        'created_at' => current_time('mysql')
                                    ]
                                );
                                
                                if ($booking_result !== false) {
                                    $booking_created = true;
                                    error_log('Бронирование успешно сохранено после создания таблиц: personal_id=' . $personal_data_id . ', booking_id=' . $booking_id);
                                }
                            }
                        } catch (Exception $e) {
                            error_log('Исключение при повторном сохранении данных: ' . $e->getMessage());
                        }
                    }
                }
                
                // Также сохраняем в стандартную структуру WordPress (для обратной совместимости)
                // Проверка: не существует ли уже бронирования с такими же данными
                $existing_booking = get_posts([
                    'post_type' => 'sun_booking',
                    'meta_query' => [
                        'relation' => 'AND',
                        [
                            'key' => '_booking_apartament_id',
                            'value' => $apartament_id
                        ],
                        [
                            'key' => '_booking_email',
                            'value' => sanitize_email($_POST['email'])
                        ],
                        [
                            'key' => '_booking_checkin_date',
                            'value' => date('d.m.Y', strtotime($checkin_date))
                        ],
                        [
                            'key' => '_booking_checkout_date',
                            'value' => date('d.m.Y', strtotime($checkout_date))
                        ]
                    ],
                    'posts_per_page' => 1,
                    'post_status' => 'any'
                ]);

                // Если бронирование с такими данными уже существует, используем его
                if (!empty($existing_booking)) {
                    $post_id = $existing_booking[0]->ID;
                    $booking_id = $existing_booking[0]->post_title;
                    error_log('Найдено существующее бронирование: ' . $post_id);
                } else {
                    // ОЧЕНЬ ВАЖНО: Полностью отключаем хуки save_post
                    global $sunApartamentAvailability;

                    // Запоминаем все действия хука save_post
                    global $wp_filter;
                    $save_post_actions = isset($wp_filter['save_post']) ? $wp_filter['save_post'] : null;

                    // Очищаем все действия хука save_post
                    $wp_filter['save_post'] = new WP_Hook();

                    // Создаем запись с отключенными хуками
                    $post_args = [
                        'post_type' => 'sun_booking',
                        'post_title' => $booking_id,
                        'post_status' => 'confirmed',
                        'post_author' => get_current_user_id(),
                    ];

                    // Отключаем запуск хука wp_insert_post
                    add_filter('wp_insert_post_empty_content', '__return_false', 999);

                    // Создаем запись
                    $post_id = wp_insert_post($post_args, true);

                    // Удаляем фильтр
                    remove_filter('wp_insert_post_empty_content', '__return_false', 999);

                    // Восстанавливаем действия хука save_post
                    if ($save_post_actions) {
                        $wp_filter['save_post'] = $save_post_actions;
                    }

                    if (!is_wp_error($post_id)) {
                        error_log('Создано новое бронирование в WordPress: ' . $post_id);
                        
                        // Устанавливаем метаполя вручную
                        update_post_meta($post_id, '_booking_apartament_id', $apartament_id);
                        update_post_meta($post_id, '_booking_first_name', sanitize_text_field($_POST['first_name']));
                        update_post_meta($post_id, '_booking_last_name', sanitize_text_field($_POST['last_name']));
                        update_post_meta($post_id, '_booking_middle_name', sanitize_text_field($_POST['middle_name']));
                        update_post_meta($post_id, '_booking_email', sanitize_email($_POST['email']));
                        update_post_meta($post_id, '_booking_phone', sanitize_text_field($_POST['phone']));
                        update_post_meta($post_id, '_booking_terms_accepted', $terms_accepted);
                        update_post_meta($post_id, '_booking_guest_count', $guest_count);
                        update_post_meta($post_id, '_booking_children_count', $children_count);

                        // Форматируем даты в нужный формат d.m.Y
                        update_post_meta($post_id, '_booking_checkin_date', date('d.m.Y', strtotime($checkin_date)));
                        update_post_meta($post_id, '_booking_checkout_date', date('d.m.Y', strtotime($checkout_date)));

                        update_post_meta($post_id, '_booking_total_price', $total_price);
                        update_post_meta($post_id, '_booking_payment_method', sanitize_text_field($_POST['payment_method']));
                        update_post_meta($post_id, '_booking_created_at', current_time('mysql'));

                        // Вручную добавляем даты недоступности
                        // Для нового формата _apartament_booked_dates
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

                        // Для старого формата _apartament_availability
                        $existing_dates = get_post_meta($apartament_id, '_apartament_availability', true);
                        $existing_dates = $existing_dates ? json_decode($existing_dates, true) : [];
                        $dates_to_add = [];

                        foreach ($daterange as $date) {
                            $dates_to_add[] = $date->format('Y-m-d');
                        }

                        $updated_dates = array_unique(array_merge($existing_dates, $dates_to_add));
                        update_post_meta($apartament_id, '_apartament_availability', json_encode(array_values($updated_dates)));

                        // Обновляем старые метаданные для совместимости
                        $bookings = get_post_meta($apartament_id, '_apartament_bookings', true);
                        $bookings = $bookings ? json_decode($bookings, true) : [];

                        // Добавляем в старую структуру только если еще нет
                        if (!isset($bookings[$booking_id])) {
                            $bookings[$booking_id] = [
                                'first_name' => sanitize_text_field($_POST['first_name']),
                                'last_name' => sanitize_text_field($_POST['last_name']),
                                'middle_name' => sanitize_text_field($_POST['middle_name']),
                                'email' => sanitize_email($_POST['email']),
                                'phone' => sanitize_text_field($_POST['phone']),
                                'checkin_date' => date('d.m.Y', strtotime($checkin_date)),
                                'checkout_date' => date('d.m.Y', strtotime($checkout_date)),
                                'payment_method' => sanitize_text_field($_POST['payment_method']),
                                'booking_date' => current_time('mysql'),
                                'total_price' => $total_price,
                                'status' => 'confirmed',
                                'booking_id' => $booking_id,
                                'guest_count' => $guest_count,
                                'children_count' => $children_count,
                                'terms_accepted' => $terms_accepted
                            ];
                            update_post_meta($apartament_id, '_apartament_bookings', json_encode($bookings));
                            
                            $booking_created = true;
                        }
                    } else {
                        error_log('Ошибка при создании бронирования в WordPress: ' . print_r($post_id, true));
                    }
                }

                // Выводим сообщение об успешном бронировании
                ?>
                <div class="booking-success">
                    <h2 class="success-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Ваше бронирование подтверждено
                    </h2>

                    <p>Бронирование апартамента "<?php echo esc_html(get_the_title($apartament_id)); ?>" успешно оформлено.
                        Информация о бронировании отправлена на указанный email: <?php echo esc_html($_POST['email']); ?></p>

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
                            <div class="detail-label">Количество гостей:</div>
                            <div class="detail-value">
                                <?php echo $guest_count; ?> взрослых<?php echo $children_count > 0 ? ', ' . $children_count . ' детей' : ''; ?>
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
                        <?php if (isset($notification_results)): ?>
    <div class="notification-status">
        <p>
            <?php if ($notification_results['email_client']): ?>
                <span class="status-item success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Письмо с деталями бронирования отправлено на ваш email
                </span>
            <?php else: ?>
                <span class="status-item error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Возникла проблема при отправке письма. Пожалуйста, сохраните номер бронирования.
                </span>
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>
                    </div>

                    
                    <?php if (!$booking_created): ?>
                    <div style="margin-top: 15px; padding: 10px; background-color: #fff3f3; border: 1px solid #ffcaca; border-radius: var(--border-radius);">
                        <p style="color: var(--danger-color); margin: 0;">Внимание: Возникли технические проблемы при сохранении данных в базу. Пожалуйста, свяжитесь с администратором сайта.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php
            }
        } else {
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
                                <img class="property-image" src="<?php echo esc_url($first_image_url); ?>"
                                    alt="<?php echo esc_attr($title); ?>">

                                <div class="property-details">
                                    <h3 class="property-name"><?php echo esc_html($title); ?></h3>

                                    <div class="property-features">
                                        <?php if ($square_footage): ?>
                                            <div class="feature-item">
                                                <?php if ($square_footage_icon): ?>
                                                    <img class="feature-icon" src="<?php echo esc_url($square_footage_icon); ?>"
                                                        alt="Площадь">
                                                <?php endif; ?>
                                                <?php echo esc_html($square_footage); ?> м²
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($floor_count): ?>
                                            <div class="feature-item">
                                                <?php if ($floor_count_icon): ?>
                                                    <img class="feature-icon" src="<?php echo esc_url($floor_count_icon); ?>"
                                                        alt="Этаж">
                                                <?php endif; ?>
                                                <?php echo esc_html($floor_count); ?> этаж
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($guest_count_max): ?>
                                            <div class="feature-item">
                                                <?php if ($guest_count_icon): ?>
                                                    <img class="feature-icon" src="<?php echo esc_url($guest_count_icon); ?>"
                                                        alt="Гости">
                                                <?php endif; ?>
                                                До <?php echo esc_html($guest_count_max); ?> гостей
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
                                
                                <div class="guest-row">
                                    <div class="guest-box">
                                        <div class="guest-label">Гости</div>
                                        <div class="guest-value">
                                            <?php echo $guest_count; ?> взрослых<?php echo $children_count > 0 ? ', ' . $children_count . ' детей' : ''; ?>
                                        </div>
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
                                <input type="hidden" name="total_price"
                                    value="<?php echo isset($final_price) ? $final_price : 0; ?>">
                                <input type="hidden" name="guest_count" value="<?php echo esc_attr($guest_count); ?>">
                                <input type="hidden" name="children_count" value="<?php echo esc_attr($children_count); ?>">

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
                                
                                <!-- Добавляем чекбокс для подтверждения бронирования -->
                                <div class="checkbox-field">
                                    <div class="checkbox-container">
                                        <input type="checkbox" id="terms_accepted" name="terms_accepted" class="checkbox-input" required>
                                        <label for="terms_accepted" class="checkbox-label">
                                            Я подтверждаю своё согласие с <a href="#">правилами бронирования</a> и <a href="#">условиями проживания</a>. 
                                            Я согласен на обработку моих персональных данных в соответствии с <a href="#">политикой конфиденциальности</a>.
                                        </label>
                                    </div>
                                </div>

                                <div class="form-field">
                                    <button type="submit" class="booking-btn">Забронировать</button>
                                </div>

                                <div class="booking-info">
                                    <p>После бронирования вам на почту будет отправлена вся необходимая информация. При возникновении вопросов, свяжитесь с нами по телефону: <strong>+7 (XXX) XXX-XX-XX</strong>.</p>
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

                                        <?php if (isset($daily_prices) && count(array_unique($daily_prices)) > 1): ?>
                                            <span class="price-per-night">(цена меняется в зависимости от дат)</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="guest-info-row">
                                        <div>Гости</div>
                                        <div><?php echo $guest_count; ?> взрослых<?php echo $children_count > 0 ? ', ' . $children_count . ' детей' : ''; ?></div>
                                    </div>

                                    <div class="price-total">
                                        <div class="total-label">Итого</div>
                                        <div class="total-value"><?php echo number_format($final_price, 0, '.', ' '); ?> ₽</div>
                                    </div>
                                </div>

                                <div class="booking-info">
                                    <p>Бесплатная отмена бронирования за 48 часов до заезда. После этого срока удерживается
                                        стоимость первых суток проживания.</p>
                                </div>
                            <?php else: ?>
                                <p>Для расчета стоимости выберите даты заезда и выезда.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        // Если апартамент не выбран
        ?>
        <div class="booking-block">
            <div class="block-header">
                <h2 class="block-title">Выберите апартамент</h2>
            </div>

            <div class="block-content" style="text-align: center; padding: 40px 20px;">
                <p style="margin-bottom: 20px;">Для оформления бронирования необходимо выбрать апартамент.</p>

                <a href="<?php echo home_url('/apartaments/'); ?>"
                    style="display: inline-block; padding: 12px 24px; background-color: var(--primary-color); color: white; text-decoration: none; border-radius: var(--border-radius); font-weight: 500; transition: background-color 0.2s;">
                    Перейти к выбору апартаментов
                </a>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php get_footer(); ?>