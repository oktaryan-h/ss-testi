<?php

/**
 * Plugin Name: SS Testimonial
 * Plugin URI: https://oktaryan.com/wpst
 * Description: The very first plugin that I have ever created.
 * Version: 1.0
 * Author: Oktaryan Nh
 * Author URI: https://oktaryan.com
 */

register_activation_hook(__FILE__, 'testimonial_install');

global $testimonial_db_version;
$testimonial_db_version = '1.0';

class SS_Testimonial {

	protected static $instance = null;

	public function __construct() {
		add_shortcode('ss-testimonial', array($this, 'ts_shortcode'));
	}

	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Install Plugin in the first time.
	 */
	public function install() {

		global $wpdb;
		global $testimonial_db_version;

		$table_name = $wpdb->prefix.'testimonial';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		email tinytext NOT NULL,
		phone_number tinytext NOT NULL,
		testimonial text NOT NULL,
		PRIMARY KEY  (id)) $charset_collate;";

		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option('testimonial_db_version',$testimonial_db_version);

	}

	/**
	 * Saves the submitted testimonial form to database.
	 */
	public function save() {

		if (isset($_POST['ts-submitted'])) {

			$name    = sanitize_text_field($_POST["ts-name"]);
			$email   = sanitize_email($_POST["ts-email"]);
			$phone_number = sanitize_text_field($_POST["ts-phone-number"]);
			$testimonial = esc_textarea($_POST["ts-testimonial"]);

			global $wpdb;

			$table_name = $wpdb->prefix.'testimonial';

			$wpdb->insert(
				$table_name, 
				array(
					'name' => $name,
					'email' => $email,
					'phone_number' => $phone_number,
					'testimonial' => $testimonial,
				)
			);
		}
	}

	/**
	 * The HTML form code to display in user form.
	 */
	public function html_form_code() {

		echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
		echo '<p>';
		echo 'Your Name (required) <br />';
		echo '<input type="text" name="ts-name" pattern="[a-zA-Z0-9 ]+" value="' . (isset($_POST["ts-name"]) ? esc_attr($_POST["ts-name"]) : '') . '" size="40" />';
		echo '</p>';
		echo '<p>';
		echo 'Your Email (required) <br />';
		echo '<input type="email" name="ts-email" value="' . (isset($_POST["ts-email"]) ? esc_attr($_POST["ts-email"]) : '') . '" size="40" />';
		echo '</p>';
		echo '<p>';
		echo 'Phone Number (required) <br />';
		echo '<input type="tel" name="ts-phone-number" value="' . (isset($_POST["ts-phone-number"]) ? esc_attr($_POST["ts-phone-number"]) : '') . '" size="40" />';
		echo '</p>';
		echo '<p>';
		echo 'Your Testimonial (required) <br />';
		echo '<textarea rows="10" cols="35" name="ts-testimonial">' . (isset($_POST["ts-testimonial"]) ? esc_attr($_POST["ts-testimonial"]) : '') . '</textarea>';
		echo '</p>';
		echo '<p><input type="submit" name="ts-submitted" value="Send"/></p>';
		echo '</form>';

	}

	function ts_shortcode() {
		ob_start();
		$this->save();
		$this->html_form_code();

		return ob_get_clean();
	}
}

/**
 * Adds WP Testimonial Admin Page.
 */
class WP_Testimonial_Admin {

	public function __construct() {
	}

	/**
	 * Adds Admin Page Menu into the WP dashboard.
	 */
	function my_admin_menu() {

		add_menu_page('SS Testimonial Admin Page', 'SS Testimonial', 'manage_options', 'ss-testimonial/ss-testimonial.php', 'ss_testimonial_admin_page', 'dashicons-tickets', 6 );
	}

	/**
	 * Admin Page.
	 */
	function admin_page(){

		global $wpdb;

		$result = $wpdb->get_results('select * from wp_testimonial',ARRAY_A);

		?>
		<div class="wrap">
			<h2>Testimonials</h2>
		</div>
		<div>
			<table class="table">
				<thead>
					<th>No</th>
					<th>Name</th>
					<th>Email</th>
					<th>Phone Number</th>
					<th>Testimonial</th>
					<th>Actions</th>
				</thead>

				<?php foreach ($result as $a) { ?>

				<tr>
					<td><?php echo $a['id']; ?></td>
					<td><?php echo $a['name']; ?></td>
					<td><?php echo $a['email']; ?></td>
					<td><?php echo $a['phone_number']; ?></td>
					<td><?php echo $a['testimonial']; ?></td>
					<td><button type="button">Delete</button></td>
				</tr>

				<?php } ?>
			</table>
		</div>
		<?php
	}

}

$testimonial = new SS_Testimonial;

	/**
	 * Calls to Install Plugin in the first time.
	 */
function testimonial_install() {
	$testimonial = SS_Testimonial::get_instance();
	$testimonial->install();
}

add_action('admin_menu', [new WP_Testimonial_Admin, 'my_admin_menu']);

/**
WIDGET_SECTION
*/

/**
 * Adds Foo_Widget widget.
 */
class testimonial_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'testimonial_widget', // Base ID
			esc_html__('Testimonial', 'text_domain'), // Name
			array('description' => esc_html__('User Testimonials', 'text_domain'),) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		$result = $wpdb->get_row('select * from wp_testimonial order by rand() limit 1',ARRAY_A);
		echo $result['name'].'<br>'.$result['email'].'<br>'.substr($result['phone_number'],0,7).'***'.'<br>'.$result['testimonial'];
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'text_domain' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Foo_Widget

	/**
	 * Registers testimonial Widget
	 */
function register_testimonial_widget() {
	register_widget( 'testimonial_widget' );
}
add_action( 'widgets_init', 'register_testimonial_widget' );