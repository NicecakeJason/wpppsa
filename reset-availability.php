<?php
/**
 * Файл для сброса метаданных доступности апартаментов и очистки осиротевших бронирований
 * 
 * Этот файл следует разместить в корне вашей темы или плагина
 */

// Проверка безопасности
if (!defined('ABSPATH')) {
    exit;
}

// Добавляем страницу инструментов
add_action('admin_menu', 'sun_add_reset_availability_page');

function sun_add_reset_availability_page() {
    add_submenu_page(
        'edit.php?post_type=sun_booking',
        'Инструменты доступности',
        'Инструменты доступности',
        'manage_options',
        'sun-reset-availability',
        'sun_render_reset_availability_page'
    );
}

// Отображение страницы инструментов
function sun_render_reset_availability_page() {
    // Обработка формы сброса доступности
    if (isset($_POST['reset_availability']) && isset($_POST['apartament_id']) && current_user_can('manage_options')) {
        check_admin_referer('sun_reset_availability', 'security');
        
        $apartament_id = intval($_POST['apartament_id']);
        $result = sun_reset_apartament_availability($apartament_id);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>Данные о доступности апартамента успешно сброшены!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Произошла ошибка при сбросе данных о доступности.</p></div>';
        }
    }
    
    // Обработка формы очистки осиротевших бронирований
    if (isset($_POST['cleanup_orphaned']) && current_user_can('manage_options')) {
        check_admin_referer('sun_cleanup_orphaned', 'security_cleanup');
        
        $result = sun_cleanup_orphaned_bookings();
        
        if ($result && !empty($result)) {
            echo '<div class="notice notice-success"><p>Очистка осиротевших бронирований завершена!</p>';
            echo '<ul>';
            foreach ($result as $apartament_id => $info) {
                $title = get_the_title($apartament_id);
                echo '<li><strong>' . esc_html($title) . '</strong>: ';
                echo 'Удалено ' . $info['removed_dates'] . ' дат и ' . $info['removed_bookings'] . ' бронирований';
                echo '</li>';
            }
            echo '</ul></div>';
        } elseif ($result === array()) {
            echo '<div class="notice notice-success"><p>Осиротевших бронирований не найдено.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Произошла ошибка при очистке осиротевших бронирований.</p></div>';
        }
    }
    
    // Получаем список апартаментов
    $apartaments = get_posts(array(
        'post_type' => 'apartament',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    ?>
    <div class="wrap">
        <h1>Инструменты для управления доступностью апартаментов</h1>
        
        <div class="postbox">
            <div class="inside">
                <h2>Сброс данных о доступности апартамента</h2>
                
                <div class="notice notice-warning" style="margin: 10px 0;">
                    <p><strong>Внимание!</strong> Используйте этот инструмент только если у вас возникли проблемы с доступностью апартаментов после удаления бронирований.</p>
                    <p>Сброс доступности пометит все даты апартамента как свободные.</p>
                </div>
                
                <form method="post" action="">
                    <?php wp_nonce_field('sun_reset_availability', 'security'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="apartament_id">Выберите апартамент:</label></th>
                            <td>
                                <select name="apartament_id" id="apartament_id" required>
                                    <option value="">- Выберите апартамент -</option>
                                    <?php foreach ($apartaments as $apartament) : ?>
                                        <option value="<?php echo $apartament->ID; ?>">
                                            <?php echo esc_html($apartament->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="reset_availability" class="button button-primary" value="Сбросить доступность">
                    </p>
                </form>
            </div>
        </div>
        
        <div class="postbox" style="margin-top: 20px;">
            <div class="inside">
                <h2>Очистка осиротевших бронирований</h2>
                
                <div class="notice notice-info" style="margin: 10px 0;">
                    <p>Этот инструмент проверит все апартаменты и удалит "осиротевшие" данные о бронированиях - записи, которые остались в метаданных, но соответствующие бронирования уже не существуют.</p>
                    <p>Рекомендуется выполнять эту операцию после массового удаления бронирований или при проблемах с отображением доступности.</p>
                </div>
                
                <form method="post" action="">
                    <?php wp_nonce_field('sun_cleanup_orphaned', 'security_cleanup'); ?>
                    
                    <p class="submit">
                        <input type="submit" name="cleanup_orphaned" class="button button-primary" value="Очистить осиротевшие бронирования">
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Функция для сброса данных о доступности апартамента
 */
function sun_reset_apartament_availability($apartament_id) {
    if (!$apartament_id) return false;
    
    // Очищаем новые данные о бронированиях (ассоциативный массив)
    delete_post_meta($apartament_id, '_apartament_booked_dates');
    update_post_meta($apartament_id, '_apartament_booked_dates', array());
    
    // Очищаем старые данные о бронированиях (для обратной совместимости)
    delete_post_meta($apartament_id, '_apartament_availability');
    update_post_meta($apartament_id, '_apartament_availability', json_encode(array()));
    
    return true;
}

/**
 * Функция для очистки осиротевших бронирований
 */
function sun_cleanup_orphaned_bookings() {
    // Получаем все апартаменты
    $apartaments = get_posts(array(
        'post_type' => 'apartament',
        'posts_per_page' => -1,
    ));
    
    if (empty($apartaments)) {
        return false;
    }
    
    // Получаем все существующие бронирования
    $existing_bookings = array();
    $bookings = get_posts(array(
        'post_type' => 'sun_booking',
        'posts_per_page' => -1,
        'post_status' => array('pending', 'confirmed', 'completed', 'cancelled', 'publish', 'draft'),
    ));
    
    foreach ($bookings as $booking) {
        $existing_bookings[$booking->ID] = $booking->post_title;
    }
    
    $results = array();
    
    // Проверяем каждый апартамент
    foreach ($apartaments as $apartament) {
        $apartament_id = $apartament->ID;
        $changes_made = false;
        $removed_dates = 0;
        $removed_bookings = 0;
        
        // 1. Проверяем и очищаем _apartament_booked_dates
        $booked_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
        if (is_array($booked_dates) && !empty($booked_dates)) {
            $cleaned_dates = array();
            
            foreach ($booked_dates as $date => $booking_id) {
                if (isset($existing_bookings[$booking_id])) {
                    // Бронирование существует, оставляем запись
                    $cleaned_dates[$date] = $booking_id;
                } else {
                    // Бронирование не существует, удаляем запись
                    $removed_dates++;
                    $changes_made = true;
                }
            }
            
            if ($changes_made) {
                update_post_meta($apartament_id, '_apartament_booked_dates', $cleaned_dates);
            }
        }
        
        // 2. Проверяем и очищаем _apartament_bookings (старые данные)
        $old_bookings_json = get_post_meta($apartament_id, '_apartament_bookings', true);
        if ($old_bookings_json) {
            $old_bookings = json_decode($old_bookings_json, true);
            
            if (is_array($old_bookings) && !empty($old_bookings)) {
                $cleaned_bookings = array();
                
                foreach ($old_bookings as $booking_number => $booking_data) {
                    $keep_booking = false;
                    
                    // Проверяем, существует ли связанное бронирование
                    if (isset($booking_data['booking_id']) && $booking_data['booking_id'] > 0) {
                        if (isset($existing_bookings[$booking_data['booking_id']])) {
                            $keep_booking = true;
                        }
                    } else {
                        // Для старых бронирований без ID проверяем по номеру
                        foreach ($existing_bookings as $id => $title) {
                            if ($title === $booking_number) {
                                $keep_booking = true;
                                // Обновляем ID бронирования
                                $booking_data['booking_id'] = $id;
                                break;
                            }
                        }
                    }
                    
                    if ($keep_booking) {
                        $cleaned_bookings[$booking_number] = $booking_data;
                    } else {
                        $removed_bookings++;
                        $changes_made = true;
                    }
                }
                
                if ($changes_made) {
                    update_post_meta($apartament_id, '_apartament_bookings', json_encode($cleaned_bookings));
                }
            }
        }
        
        // 3. Обновляем старое поле _apartament_availability на основе очищенных _apartament_booked_dates
        if ($changes_made) {
            $cleaned_dates = get_post_meta($apartament_id, '_apartament_booked_dates', true);
            $dates_array = array();
            
            if (is_array($cleaned_dates)) {
                foreach ($cleaned_dates as $date => $booking_id) {
                    $dates_array[] = $date;
                }
            }
            
            update_post_meta($apartament_id, '_apartament_availability', json_encode($dates_array));
            
            // Записываем результаты только если были изменения
            $results[$apartament_id] = array(
                'removed_dates' => $removed_dates,
                'removed_bookings' => $removed_bookings
            );
        }
    }
    
    return $results;
}

// Добавляем метабокс для быстрого сброса доступности в редакторе апартамента
add_action('add_meta_boxes', 'sun_add_reset_availability_metabox');

function sun_add_reset_availability_metabox() {
    add_meta_box(
        'reset_availability_metabox',
        'Сброс доступности',
        'sun_render_reset_availability_metabox',
        'apartament',
        'side',
        'low'
    );
}

// Отображение метабокса
function sun_render_reset_availability_metabox($post) {
    wp_nonce_field('sun_reset_availability_metabox', 'reset_availability_nonce');
    ?>
    <p>Если апартамент отображается как недоступный после удаления бронирований, используйте эту кнопку:</p>
    <input type="submit" name="reset_availability_btn" class="button button-secondary" value="Сбросить доступность" style="width: 100%; margin-bottom: 10px;">
    
    <p>Или очистите только осиротевшие бронирования:</p>
    <input type="submit" name="cleanup_orphaned_btn" class="button button-secondary" value="Очистить осиротевшие" style="width: 100%;">
    <?php
}

// Обработка нажатия кнопки в метабоксе
add_action('save_post', 'sun_process_reset_availability_metabox', 10, 2);

function sun_process_reset_availability_metabox($post_id, $post) {
    // Проверяем тип поста
    if ($post->post_type != 'apartament') {
        return;
    }
    
    // Проверяем nonce
    if (!isset($_POST['reset_availability_nonce']) || !wp_verify_nonce($_POST['reset_availability_nonce'], 'sun_reset_availability_metabox')) {
        return;
    }
    
    // Проверяем права
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Проверяем, была ли нажата кнопка сброса
    if (isset($_POST['reset_availability_btn'])) {
        sun_reset_apartament_availability($post_id);
        
        // Добавляем сообщение для отображения
        add_filter('redirect_post_location', function($location) {
            return add_query_arg('reset_availability', '1', $location);
        });
    }
    
    // Проверяем, была ли нажата кнопка очистки осиротевших бронирований
    if (isset($_POST['cleanup_orphaned_btn'])) {
        // Создаем массив для одного апартамента
        $apartaments = array(get_post($post_id));
        
        // Создаем временную функцию для очистки только одного апартамента
        $clean_single = function() use ($post_id) {
            // Получаем все существующие бронирования
            $existing_bookings = array();
            $bookings = get_posts(array(
                'post_type' => 'sun_booking',
                'posts_per_page' => -1,
                'post_status' => array('pending', 'confirmed', 'completed', 'cancelled', 'publish', 'draft'),
            ));
            
            foreach ($bookings as $booking) {
                $existing_bookings[$booking->ID] = $booking->post_title;
            }
            
            $changes_made = false;
            $removed_dates = 0;
            $removed_bookings = 0;
            
            // 1. Проверяем и очищаем _apartament_booked_dates
            $booked_dates = get_post_meta($post_id, '_apartament_booked_dates', true);
            if (is_array($booked_dates) && !empty($booked_dates)) {
                $cleaned_dates = array();
                
                foreach ($booked_dates as $date => $booking_id) {
                    if (isset($existing_bookings[$booking_id])) {
                        // Бронирование существует, оставляем запись
                        $cleaned_dates[$date] = $booking_id;
                    } else {
                        // Бронирование не существует, удаляем запись
                        $removed_dates++;
                        $changes_made = true;
                    }
                }
                
                if ($changes_made) {
                    update_post_meta($post_id, '_apartament_booked_dates', $cleaned_dates);
                }
            }
            
            // 2. Проверяем и очищаем _apartament_bookings (старые данные)
            $old_bookings_json = get_post_meta($post_id, '_apartament_bookings', true);
            if ($old_bookings_json) {
                $old_bookings = json_decode($old_bookings_json, true);
                
                if (is_array($old_bookings) && !empty($old_bookings)) {
                    $cleaned_bookings = array();
                    
                    foreach ($old_bookings as $booking_number => $booking_data) {
                        $keep_booking = false;
                        
                        // Проверяем, существует ли связанное бронирование
                        if (isset($booking_data['booking_id']) && $booking_data['booking_id'] > 0) {
                            if (isset($existing_bookings[$booking_data['booking_id']])) {
                                $keep_booking = true;
                            }
                        } else {
                            // Для старых бронирований без ID проверяем по номеру
                            foreach ($existing_bookings as $id => $title) {
                                if ($title === $booking_number) {
                                    $keep_booking = true;
                                    // Обновляем ID бронирования
                                    $booking_data['booking_id'] = $id;
                                    break;
                                }
                            }
                        }
                        
                        if ($keep_booking) {
                            $cleaned_bookings[$booking_number] = $booking_data;
                        } else {
                            $removed_bookings++;
                            $changes_made = true;
                        }
                    }
                    
                    if ($changes_made) {
                        update_post_meta($post_id, '_apartament_bookings', json_encode($cleaned_bookings));
                    }
                }
            }
            
            // 3. Обновляем старое поле _apartament_availability на основе очищенных _apartament_booked_dates
            if ($changes_made) {
                $cleaned_dates = get_post_meta($post_id, '_apartament_booked_dates', true);
                $dates_array = array();
                
                if (is_array($cleaned_dates)) {
                    foreach ($cleaned_dates as $date => $booking_id) {
                        $dates_array[] = $date;
                    }
                }
                
                update_post_meta($post_id, '_apartament_availability', json_encode($dates_array));
                
                return array(
                    'removed_dates' => $removed_dates,
                    'removed_bookings' => $removed_bookings
                );
            }
            
            return false;
        };
        
        $result = $clean_single();
        
        if ($result) {
            // Добавляем сообщение для отображения
            add_filter('redirect_post_location', function($location) use ($result) {
                return add_query_arg(array(
                    'cleanup_orphaned' => '1',
                    'removed_dates' => $result['removed_dates'],
                    'removed_bookings' => $result['removed_bookings']
                ), $location);
            });
        } else {
            // Ничего не нашли
            add_filter('redirect_post_location', function($location) {
                return add_query_arg('cleanup_orphaned', '0', $location);
            });
        }
    }
}

// Вывод сообщения об успешном сбросе доступности
add_action('admin_notices', 'sun_display_reset_availability_notice');

function sun_display_reset_availability_notice() {
    // Сообщение о сбросе доступности
    if (isset($_GET['reset_availability']) && $_GET['reset_availability'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>Данные о доступности апартамента успешно сброшены!</p></div>';
    }
    
    // Сообщение об очистке осиротевших бронирований
    if (isset($_GET['cleanup_orphaned'])) {
        if ($_GET['cleanup_orphaned'] == '1') {
            $removed_dates = isset($_GET['removed_dates']) ? intval($_GET['removed_dates']) : 0;
            $removed_bookings = isset($_GET['removed_bookings']) ? intval($_GET['removed_bookings']) : 0;
            
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>Очистка осиротевших бронирований завершена!</p>';
            echo '<p>Удалено ' . $removed_dates . ' дат и ' . $removed_bookings . ' записей о бронированиях.</p>';
            echo '</div>';
        } elseif ($_GET['cleanup_orphaned'] == '0') {
            echo '<div class="notice notice-info is-dismissible"><p>Осиротевших бронирований не найдено.</p></div>';
        }
    }
}