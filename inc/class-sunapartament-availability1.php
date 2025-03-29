<?php
if (!class_exists('sunApartamentAvailability')) {
    class sunApartamentAvailability {
        public function register() {
           
            
            add_action('init', [$this, 'fix_db_encoding']);
            
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

       

        

    }
}

// Инициализация класса
if (class_exists('sunApartamentAvailability')) {
    $sunApartamentAvailability = new sunApartamentAvailability();
    $sunApartamentAvailability->register();
}