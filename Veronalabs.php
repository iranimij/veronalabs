<?php
/**
 * Plugin Name: Test Veronalabs Plugin
 * Description: A Sample WordPress Plugin with autoload and PHP namespace
 * Plugin URI:  https://hamyarwoo.com
 * Version:     1.0
 * Author:      Iman Heydari
 * Author URI:  https://iranimij.com
 * License:     MIT
 * Text Domain: psr4-wordpress-plugin
 * Domain Path: /languages
 */

add_action('plugins_loaded', [Veronalabs::get_instance(), 'plugin_setup']);
register_activation_hook(__FILE__, [
  Veronalabs::get_instance(),
  'install_tables',
]);
register_deactivation_hook(__FILE__, [
  Veronalabs::get_instance(),
  'deActivation',
]);

class Veronalabs {

  /**
   * Plugin instance.
   *
   * @see get_instance()
   * @type object
   */
  protected static $instance = NULL;

  /**
   * URL to this plugin's directory.
   *
   * @type string
   */
  public $plugin_url = '';

  /**
   * Path to this plugin's directory.
   *
   * @type string
   */
  public $plugin_path = '';

  /**
   * Access this plugin’s working instance
   *
   * @wp-hook plugins_loaded
   * @since   2012.09.13
   * @return  object of this class
   */
  public static function get_instance() {
    NULL === self::$instance and self::$instance = new self;
    return self::$instance;
  }

  /**
   * Used for regular plugin work.
   *
   * @wp-hook plugins_loaded
   * @return  void
   */
  public function plugin_setup() {
    $this->plugin_url = plugins_url('/', __FILE__);
    $this->plugin_path = plugin_dir_path(__FILE__);
    $this->load_language('psr4-wordpress-plugin');


    spl_autoload_register([$this, 'autoload']);
    // Example: Modify the Contents
    Actions\Post::addEmojiToContents();
  }

  public function registerPostTypes() {
    register_post_type('book',
      [
        'labels' => [
          'name' => __('Book'),
          'singular_name' => __('Book'),
        ],
        'public' => TRUE,
        'has_archive' => TRUE,
        'taxonomies' => ['Publisher', 'Authors'],
        'register_meta_box_cb' => [$this,'add_new_meta_box']

      ]
    );
  }

  public function add_new_meta_box() {
    add_meta_box("isbn", __("isbn", "psr4-wordpress-plugin"), [$this, 'mycallback']);
  }

  public function mycallback($post) {
    $post_id = $post->ID;
    if (is_numeric($post_id)){
    global $wpdb;
    $info = $wpdb->get_row('SELECT isbn FROM '.$wpdb->prefix.'books_info where post_id ='.$post_id,ARRAY_N );

    }
    ?>
    <label for="wporg_field"><?PHP _e("isbn","psr4-wordpress-plugin");?></label>
    <input type="text" name="isbn" value="<?=$info[0];?>">
<?PHP
  }

  function wporg_save_postdata($post_id)
  {
    global $wpdb;
    $info = $wpdb->get_row('SELECT ID FROM '.$wpdb->prefix.'books_info where post_id ='.$post_id,ARRAY_N );
    $id = $info[0];

    if (isset($id)){
      $wpdb->update(
        $wpdb->prefix.'books_info',
        array(
          'post_id' => $post_id,
          'isbn' => $_POST['isbn']
        ),
        array( 'ID' => $id ),
        array(
          '%d',	// value1
          '%s'	// value2
        ),
        array( '%d' )
      );
    }else{

    $wpdb->insert(
      $wpdb->prefix.'books_info',
      array(
        'post_id' => $post_id,
        'isbn' => $_POST['isbn']
      ),
      array(
        '%d',
        '%s'
      )
    );
    }
  }

  /**
   * Constructor. Intentionally left empty and public.
   *
   * @see plugin_setup()
   */
  public function __construct() {
    add_action('init', array( $this, 'registerPostTypes' ) );
    add_action( 'init', array( $this, 'create_private_book_tax' ) );
    add_action('save_post', array( $this, 'wporg_save_postdata' ));
  }



  public function books_info_func(){
    echo "TEST";
  }

  public function create_private_book_tax(){
    register_taxonomy(
      'Publisher',
      'book',
      array(
        'label' => __( 'Publisher' ),
        'public' => TRUE,
        'rewrite' => TRUE,
        'hierarchical' => FALSE,
      )
    );
    register_taxonomy(
      'Authors',
      'book',
      array(
        'label' => __( 'Authors' ),
        'public' => TRUE,
        'rewrite' => TRUE,
        'hierarchical' => FALSE,
      )
    );
  }

  /**
   * Loads translation file.
   *
   * Accessible to other classes to load different language files (admin and
   * front-end for example).
   *
   * @wp-hook init
   *
   * @param   string $domain
   *
   * @return  void
   */
  public function load_language($domain) {

    load_plugin_textdomain($domain, FALSE, basename(dirname(__FILE__)) . '/languages');

  }

  /**
   * @param $class
   *
   */
  public function autoload($class) {

    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    if (!class_exists($class)) {
      $class_full_path = $this->plugin_path . 'includes/' . $class . '.php';

      if (file_exists($class_full_path)) {
        require $class_full_path;
      }
    }

  }

  public function install_tables() {
    global $wpdb;
    $banners = $wpdb->prefix . 'books_info';

    $wp_banners = 'CREATE TABLE ' . $banners . ' (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` varchar(255) COLLATE utf8_persian_ci NOT NULL,
  `isbn` varchar(1000) COLLATE utf8_persian_ci NOT NULL,
  PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($wp_banners);
  }

  function deActivation() {

    global $wpdb;

    $wp_banners = $wpdb->prefix . 'books_info';
    $wpdb->query("DROP TABLE IF EXISTS " . $wp_banners);

  }
}