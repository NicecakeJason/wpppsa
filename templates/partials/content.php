<div id="post-<?php the_ID(); ?>" <?php post_class('col'); ?>>
    <div class="card">

        <?php
        $gallery_images = get_post_meta(get_the_ID(), 'sunapartament_gallery', true);
        if ($gallery_images) {
            echo '<div class="slick">';
            foreach ($gallery_images as $image_id) {
                // Получаем URL текущего поста
                $post_url = get_permalink();
                // Получаем URL изображения
                $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                $image_url = wp_get_attachment_image_src($image_id, 'large')[0]; // 'large' — размер изображения
                // Выводим изображение, обернутое в тег <a>, который ссылается на страницу поста
                echo '<div><a href="' . esc_url($post_url) . '" class="gallery-link">' . wp_get_attachment_image($image_id, 'large', false, array(
                    'class' => 'img-fluid img-content', // Ваши классы
                    'alt' => esc_attr($alt_text), // Альтернативный текст
                )) . '</a></div>';
            }
            echo '</div>';
        }
        ?>

        <div class="card-body">
            <a href="<?php the_permalink(); ?>">
                <h4 class="detail-room__title amenities-card__title h4-title">
                    <?php the_title(); ?>
                </h4>
            </a>
            <?php
            $square_footage = get_post_meta(get_the_ID(), 'sunapartament_square_footage', true);
            $guest_count = get_post_meta(get_the_ID(), 'sunapartament_guest_count', true);
            $floor_count = get_post_meta(get_the_ID(), 'sunapartament_floor_count', true);

            $square_footage_icon = get_post_meta(get_the_ID(), 'sunapartament_square_footage_icon', true);
            $guest_count_icon = get_post_meta(get_the_ID(), 'sunapartament_guest_count_icon', true);
            $floor_count_icon = get_post_meta(get_the_ID(), 'sunapartament_floor_count_icon', true);

            if ($square_footage || $guest_count || $floor_count) {
                echo '<div class="card-meta">';
                echo '<ul class="d-flex justify-content-between">';
                if ($square_footage) {
                    echo '<li class="header-social__item">';
                    echo '<div class="wrapper">';
                    if ($square_footage_icon) {
                        echo '<img class="card-icon" src="' . esc_url($square_footage_icon) . '" alt="Квадратура" class="icon">';
                    }
                    echo '<span class="detail-info__text">' . esc_html($square_footage) . ' м²</span>';
                    echo '</div>'; // Исправлено: убрана лишняя кавычка
                    echo '</li>';
                }

                if ($floor_count) {
                    echo '<li class="header-social__item">';
                    echo '<div class="wrapper">';

                    if ($floor_count_icon) {
                        echo '<img class="card-icon" src="' . esc_url($floor_count_icon) . '" alt="Кровать" class="icon">';
                    }
                    echo '<span class="detail-info__text">' . esc_html($floor_count) . ' этаж</span>'; // Завернуто в <span>
                    echo '</div>'; // Исправлено: убрана лишняя кавычка
            
                    echo '</li>';
                }
                //Вывод количества гостей
                if ($guest_count) {
                    echo '<li class="header-social__item">';
                    echo '<div class="wrapper">';
                    if ($guest_count_icon) {
                        echo '<img class="card-icon" src="' . esc_url($guest_count_icon) . '" alt="Гости" class="icon">';
                    }
                    echo '<span class="detail-info__text">До ' . esc_html($guest_count) . ' мест</span>';
                    echo '</div>';
                    echo '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }


            ?>
        </div>






        <div class="card-cost d-flex justify-content-between align-items-center">
            <?php
            // Выводим цену для текущего месяца
            $sunApartamentPrice = new sunApartamentPrice();
            $current_price = $sunApartamentPrice->display_current_price(get_the_ID());
            echo '<div class="d-flex flex-column">';
            echo '<span class="cost">' . esc_html($current_price) . ' ₽ </span>';
            echo '<span class="day">за ночь</span>';
            echo '</div>';
            ?>
            <a class="card-btn" href="<?php the_permalink(); ?>">Подробнее</a>
        </div>



    </div>
</div>