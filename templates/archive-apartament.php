<?php
/*
Template Name: aparts
*/

get_header('flat');

?>

<main>
    <section class="section section-breadcrumbs">
        <div class="container">
        <?php custom_breadcrumbs();?>
        </div>
    </section>
    <section class="section">
    <div class="container">
        <h2 class="section-title h3-title">Апартаменты</h2>

        <div class="row row-cols-1 row-cols-xl-3 row-cols-lg-2 row-cols-md-2 g-4 section-grid">
            <?php
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

            if (!empty($_POST['submit'])) {
                $args = array(
                    'post_type' => 'apartament',
                    'posts_per_page' => 10, // Укажите количество постов на странице
                    'paged' => $paged,
                    'meta_query' => array('relation' => 'AND'),
                    'tax_query' => array('relation' => 'AND'),
                );

                if (isset($_POST['sunapartament_type']) && $_POST['sunapartament_type'] != '') {
                    array_push($args['meta_query'], array(
                        'key' => 'sunapartament_type',
                        'value' => esc_attr($_POST['sunapartament_type']),
                    ));
                }

                if (isset($_POST['sunapartament_price']) && $_POST['sunapartament_price'] != '') {
                    array_push($args['meta_query'], array(
                        'key' => 'sunapartament_price',
                        'value' => esc_attr($_POST['sunapartament_price']),
                        'type' => 'numeric',
                        'compare' => '<=',
                    ));
                }

                if (isset($_POST['sunapartament_apartament-type']) && $_POST['sunapartament_apartament-type'] != '') {
                    array_push($args['tax_query'], array(
                        'taxonomy' => 'apartament-type',
                        'terms' => $_POST['sunapartament_property-type'],
                    ));
                }

                $apartaments = new WP_Query($args);

                if ($apartaments->have_posts()) {
                    while ($apartaments->have_posts()) {
                        $apartaments->the_post();
                        $sunApartament_Template->get_template_part('partials/content');
                    }
                } else {
                    echo '<p>' . esc_html__('No Properties', 'sunapartament') . '</p>';
                }

                // Пагинация для пользовательского запроса
                echo paginate_links(array(
                    'total' => $apartaments->max_num_pages,
                    'current' => $paged,
                    'prev_text' => __('&laquo; Previous'),
                    'next_text' => __('Next &raquo;'),
                ));

                wp_reset_postdata();
            } else {
                if (have_posts()) {
                    while (have_posts()) {
                        the_post();
                        $sunApartament_Template->get_template_part('partials/content');
                    }

                   
                } else {
                    echo '<p>' . esc_html__('No Properties', 'sunapartament') . '</p>';
                }
                 
            }
            ?>
        </div>
    </div>
    <?php 
    
    echo '<div class="custom-pagination-container text-center">';
echo paginate_links(array(
    'total' => $wp_query->max_num_pages,
    'current' => $paged,
    'prev_text' => '<span class="custom-prev-class">&laquo; Previous</span>',
    'next_text' => '<span class="custom-next-class">Next &raquo;</span>',
    'before_page_number' => '<span class="custom-page-number">',
    'after_page_number' => '</span>',
));
echo '</div>';
                ?>
    </section>
    
</main>

<?php
get_footer();
?>