<?php
if (!class_exists('sunApartamentShortcode')) {

    class sunApartamentShortcode
    {

        public function register()
        {
            add_shortcode('sunapartament_properties', [$this, 'sunapartament_display_properties']);
        }

        // Метод для шорткода
        public function sunapartament_display_properties($atts)
        {
            // Атрибуты шорткода
            $atts = shortcode_atts(array(
                'posts_per_page' => 5, // Количество постов для вывода
                'category' => '', // Слаг категории (термина таксономии)
            ), $atts, 'sunapartament_properties');

            // Аргументы для WP_Query
            $args = array(
                'post_type' => 'apartament', // Указываем ваш custom post type
                'posts_per_page' => $atts['posts_per_page'],
                'orderby' => 'ID', // Сортировка по ID
                'order' => 'ASC', // Порядок сортировки (ASC - по возрастанию)
            );

            // Если указана категория, добавляем фильтрацию по термину таксономии
            if (!empty($atts['category'])) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'apartament-type', // Указываем таксономию
                        'field' => 'slug', // Используем слаг термина
                        'terms' => $atts['category'], // Слаг категории из шорткода
                    ),
                );
            }

            // Запрос постов
            $query = new WP_Query($args);

            // Начало вывода
            $output = '';

            if ($query->have_posts()) {
                // Группируем посты по рубрикам (терминам таксономии)
                $grouped_posts = array();

                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();

                    // Получаем термины таксономии для текущего поста
                    $terms = get_the_terms($post_id, 'apartament-type');
                    if ($terms && !is_wp_error($terms)) {
                        // Если указана категория, фильтруем термины по этой категории
                        if (!empty($atts['category'])) {
                            $terms = array_filter($terms, function ($term) use ($atts) {
                                return $term->slug === $atts['category'];
                            });
                        }

                        // Добавляем пост в каждую из его рубрик
                        foreach ($terms as $term) {
                            if (!isset($grouped_posts[$term->term_id])) {
                                $grouped_posts[$term->term_id] = array(
                                    'term' => $term,
                                    'posts' => array(),
                                );
                            }
                            // Добавляем пост только если он еще не добавлен в эту рубрику
                            if (!in_array($post_id, $grouped_posts[$term->term_id]['posts'])) {
                                $grouped_posts[$term->term_id]['posts'][] = $post_id;
                            }
                        }
                    }
                }

                // Сброс данных поста
                wp_reset_postdata();

                // Вывод постов, сгруппированных по рубрикам
                foreach ($grouped_posts as $group) {
                    $term = $group['term'];
                    $posts = $group['posts'];

                    // Сортируем посты по ID
                    sort($posts);

                    // Вывод названия рубрики (если есть)
                    if ($term) {
                        $output .= '<h2 class="section-title h3-title">' . esc_html($term->name) . '</h2>';

                        // Вывод описания рубрики (если есть)
                        if (!empty($term->description)) {
                            $output .= '<div class="category-description">' . esc_html($term->description) . '</div>';
                        }
                    }

                    // Начало обертки для постов
                    $output .= '<div class="row row-cols-1 row-cols-xl-3 row-cols-lg-2 row-cols-md-2 g-4 mb-5 section-grid">'; // Открываем обертку

                    // Вывод постов в этой рубрике
                    foreach ($posts as $post_id) {
                        // Получаем объект поста и устанавливаем его как глобальный
                        $post = get_post($post_id);
                        setup_postdata($GLOBALS['post'] = $post);

                        ob_start(); // Начало буферизации вывода
                        ?>
                        <div id="post-<?php echo $post_id; ?>" <?php post_class('col'); ?> role="article">
                            <div class="card">
                                <?php
                                $gallery_images = get_post_meta($post_id, 'sunapartament_gallery', true);
                                if ($gallery_images) {
                                    echo '<div class="slick">';
                                    foreach ($gallery_images as $image_id) {
                                        // Получаем URL поста
                                        $post_url = get_permalink($post_id);

                                        // Выводим изображение, обернутое в ссылку на пост
                                        echo '<div>';
                                        echo '<a href="' . esc_url($post_url) . '">';
                                        echo wp_get_attachment_image($image_id, 'large', false, array(
                                            'class' => 'img-fluid img-card',
                                            'alt' => 'Property Image',
                                        ));
                                        echo '</a>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                                ?>
                                <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                                    <h4 class="detail-room__title amenities-card__title h4-title">
                                        <?php echo esc_html(get_the_title($post_id)); ?>
                                    </h4>
                                </a>
                                <?php
                                $square_footage = get_post_meta($post_id, 'sunapartament_square_footage', true);
                                $guest_count = get_post_meta($post_id, 'sunapartament_guest_count', true);
                                $floor_count = get_post_meta($post_id, 'sunapartament_floor_count', true);

                                $square_footage_icon = get_post_meta($post_id, 'sunapartament_square_footage_icon', true);
                                $guest_count_icon = get_post_meta($post_id, 'sunapartament_guest_count_icon', true);
                                $floor_count_icon = get_post_meta($post_id, 'sunapartament_floor_count_icon', true);

                                if ($square_footage || $guest_count || $floor_count) {
                                    echo '<div class="card-meta">';
                                    echo '<ul class="d-flex justify-content-between">';
                                    if ($square_footage) {
                                        echo '<li class="card-meta__item">';
                                        echo '<div class="wrapper">';
                                        if ($square_footage_icon) {
                                            echo '<img class="card-icon" src="' . esc_url($square_footage_icon) . '" alt="Площадь" class="icon">';
                                        }
                                        echo '<span class="card-meta__text">' . esc_html($square_footage) . ' м²</span>';
                                        echo '</div>';
                                        echo '</li>';
                                    }

                                    if ($floor_count) {
                                        echo '<li class="card-meta__item">';
                                        echo '<div class="wrapper">';
                                        if ($floor_count_icon) {
                                            echo '<img class="card-icon" src="' . esc_url($floor_count_icon) . '" alt="Кровать" class="icon">';
                                        }
                                        echo '<span class="card-meta__text">' . esc_html($floor_count) . ' этаж</span>';
                                        echo '</div>';
                                        echo '</li>';
                                    }

                                    if ($guest_count) {
                                        echo '<li class="card-meta__item">';
                                        echo '<div class="wrapper">';
                                        if ($guest_count_icon) {
                                            echo '<img class="card-icon" src="' . esc_url($guest_count_icon) . '" alt="Гости" class="icon">';
                                        }
                                        echo '<span class="card-meta__text">До ' . esc_html($guest_count) . ' мест</span>';
                                        echo '</div>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    echo '</div>';
                                }
                                ?>
                                <div class="clearfix facilities-amenities">
                                    <div class="card-cost d-flex justify-content-between align-items-center">
                                        <?php
                                        // Выводим цену на текущий день
                                        $sunApartamentPrice = new sunApartamentPrice();
                                        $current_price = $sunApartamentPrice->get_price_for_date($post_id);
                                        echo '<div class="d-flex flex-column">';
                                        echo '<span class="cost">' . esc_html($current_price) . ' ₽ </span>';
                                        echo '<span class="day">за ночь</span>';
                                        echo '</div>';
                                        
                                        
                                        ?>
                                        <div>
                                            <a class="card-btn" href="<?php echo esc_url(get_permalink($post_id)); ?>">Подробнее</a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $output .= ob_get_clean(); // Конец буферизации и добавление к выводу
                    }

                    // Закрываем обертку для постов
                    $output .= '</div>'; // Закрываем обертку
                }
            } else {
                $output .= '<p>No properties found</p>';
            }

            // Сброс данных поста
            wp_reset_postdata();

            return $output;
        }
    }
}

if (class_exists('sunApartamentShortcode')) {
    $sunApartamentShortcode = new sunApartamentShortcode();
    $sunApartamentShortcode->register();
}