<?php 
class sunApartament_Template_Loader extends Gamajo_Template_Loader {

    protected $filter_prefix = 'sunapartament';
    protected $theme_template_directory = 'sunapartament';
    protected $plugin_directory = SUNAPARTAMENT_PATH;
    protected $plugin_template_directory = 'templates'; // Исправлено: правильное название свойства
    public $templates;

    public function register(){
        add_filter('template_include', [$this,'sunapartaments_templates']);
        add_filter('template_include', [$this,'load_template_from_plugin']);
        add_filter('template_include', [$this,'load_page_templates']);
    }

    public function load_template_from_plugin($template) {
        if (!locate_template('partials/content-single.php')) {
            $plugin_template = plugin_dir_path(__FILE__) . 'partials/content-single.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }


    public function sunapartaments_templates($template){
        // Для архива квартир
        if(is_post_type_archive('apartament')){
            $theme_files = ['archive-apartament.php','sunapartament/archive-apartament.php'];
            $exist = locate_template($theme_files, false);
            return ($exist != '') ? $exist : plugin_dir_path(__DIR__).'templates/archive-apartament.php';
        } 
        
        // Для одиночной страницы квартиры
        elseif(is_singular('apartament')){
            // Определяем какой шаблон использовать
            $theme_files = $this->get_single_template_files();
            
            // Ищем шаблон в теме
            $exist = locate_template($theme_files, false);
            
            if($exist != ''){
                return $exist;
            } 
            // Возвращаем шаблон из плагина
            else {
                return $this->get_plugin_template_path($theme_files[0]);
            }
        }

        return $template;
    }

    /**
     * Выбирает правильные файлы шаблона
     */
    private function get_single_template_files() {
        // Проверяем параметр source=results
        if(isset($_GET['source']) && $_GET['source'] === 'results') {
            return ['single-apartament-results.php', 'sunapartament/single-apartament-results.php'];
        }
        return ['single-apartament.php', 'sunapartament/single-apartament.php'];
    }

    /**
     * Формирует путь к шаблону в плагине
     */
    private function get_plugin_template_path($template_name) {
        $plugin_path = plugin_dir_path(__DIR__).'templates/';
        
        // Для специального шаблона результатов
        if(strpos($template_name, 'results') !== false) {
            return $plugin_path . 'single-apartament-results.php';
        }
        
        // Стандартный шаблон
        return $plugin_path . 'single-apartament.php';
    }

    /**
     * Загружает специальные страничные шаблоны из плагина
     */
    public function load_page_templates($template) {
        global $post;
        
        if (is_page() && $post) {
            // Получаем текущий шаблон
            $current_template = get_page_template_slug($post->ID);
            
            // Проверяем название текущей страницы на соответствие нашим шаблонам
            $page_name = $post->post_name;
            
            // Обрабатываем шаблон страницы booking
            if ($current_template == 'page-booking.php' || $page_name == 'booking') {
                $plugin_template = plugin_dir_path(__DIR__) . 'templates/page-booking.php';
                if (file_exists($plugin_template)) {
                    return $plugin_template;
                }
            }
            
            // Обрабатываем шаблон страницы results
            if ($current_template == 'page-results.php' || $page_name == 'results') {
                $plugin_template = plugin_dir_path(__DIR__) . 'templates/page-results.php';
                if (file_exists($plugin_template)) {
                    return $plugin_template;
                }
            }
            
            // Также проверяем по содержимому запроса
            if (isset($_GET['source']) && $_GET['source'] === 'results') {
                $plugin_template = plugin_dir_path(__DIR__) . 'templates/page-results.php';
                if (file_exists($plugin_template)) {
                    return $plugin_template;
                }
            }
        }
        
        return $template;
    }
}

$sunApartament_Template = new sunApartament_Template_Loader();
$sunApartament_Template->register();