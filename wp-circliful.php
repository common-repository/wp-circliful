<?php
/*
Plugin Name: WP Circliful
Plugin URI: http://bappi-d-great.com
Description: This plugin will help you to add responsive circliful in anywhere of your site. Nicely organized by custom post type. You can use it using shortcode, custom function and in widget. Use Shortcode: [circliful id='ID'], Template Function: show_circliful(ID) or widget. And finally, if you don't want to use custom post type, just use direct shortcode: [circliful_direct dimension="400" text="120" info="Raised" width="45" fontsize="45" percent="78" fgcolor="red" bgcolor="green" icon="fa-plus" icon_size="35" icon_color="#ccc" border="yes"].
Version: 1.2
Autho name: Bappi D Great (Ashok)
Author URI: http://bappi-d-great.com
*/

class WP_Circliful {
    
    public $domain;
    public $plugin_url;
    public $plugin_dir;
    
    public function __construct() {
        
        $this->domain = 'wp_circliful';
        $this->plugin_dir = WP_PLUGIN_DIR . '/wp-circliful/';
        $this->plugin_url = plugins_url('/', __FILE__);
        
        add_action( 'plugins_loaded', array( $this, 'WP_Circliful_load_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'add_styles_scripts' ) );
        
        add_action( 'init', array( $this, 'circliful_post_type' ) );
        add_shortcode( 'circliful', array( $this, 'circliful_shortcode' ) );
        add_shortcode( 'circliful_direct', array( $this, 'circliful_direct_shortcode' ) );
        add_action( 'save_post', array($this, 'save_circliful_settings'), 1, 2 );
        
        add_action( "manage_edit-circlifuls_columns",          array($this, 'circlifuls_columns_id') );
        add_filter( "manage_edit-circlifuls_sortable_columns", array($this, 'circlifuls_columns_id') );
        add_filter( "manage_circlifuls_posts_custom_column",         array($this, 'circlifuls_custom_id_columns'), 10, 2 );
        
        add_action('init', array($this, 'circliful_editor_from_post_type'));
        
    }
    
    /*
     * Adding language file
     */
    function WP_Circliful_load_textdomain() {
        load_plugin_textdomain( $this->domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
    }
    
    /*
     * Register style and scripts
     */
    function add_styles_scripts() {
        wp_register_script( 'jquery-circliful', $this->plugin_url . 'assets/js/jquery.circliful.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'jquery-circliful' );
        
        wp_register_script( 'custom-circliful', $this->plugin_url . 'assets/js/custom.js', array( 'jquery' ) );
        wp_enqueue_script( 'custom-circliful' );
        
        wp_register_style( 'jquery-circliful-css', $this->plugin_url . 'assets/css/jquery.circliful.css' );
        wp_enqueue_style('jquery-circliful-css');
        
        wp_register_style( 'font-awesome-css', $this->plugin_url . 'assets/css/font-awesome.min.css' );
        wp_enqueue_style('font-awesome-css');
    }
    
    //Custom Post Type
    public function circliful_post_type() {
        $labels = array(
            'name' => _x('Circlifuls', 'name', $this->domain),
            'singular_name' => __('Circliful', $this->domain),
            'menu_name' => _x('Circlifuls', 'menu-name', $this->domain),
            'all_items' => __('All Circlifuls', $this->domain),
            'add_new' => _x('Add New Circliful', 'add-new', $this->domain),
            'add_new_item' => _x('Add New Circliful', 'add-new-item', $this->domain),
            'edit_item' => __('Edit Circliful', $this->domain),
            'new_item' => __('New Circliful', $this->domain),
            'view_item' => __('View Circliful', $this->domain),
            'search_items' => __('Search a Circliful', $this->domain),
            'not_found' => _x('No Circliful Found', 'not-found', $this->domain),
            'not_found_in_trash' => _x('No Circliful Found', 'not-found-in-trash', $this->domain)
        );

        $args = array(
            'labels' => $labels,
            'description' => __('Custom Post Types for adding Circlifuls', $this->domain),
            'public' => TRUE,
            'exclude_from_search' => FALSE,
            'show_ui' => TRUE,
            'show_in_nav_menus' => FALSE,
            'show_in_menu' => TRUE,
            'menu_position' => 20,
            'can_export' => TRUE,
            'rewrite' => FALSE,
            'hierarchical' => FALSE,
            'capability_type' => 'post',
            'query_var' => TRUE,
            'supports' => array('title', 'editor', 'author', 'custom-fields', 'page-attributes'),
            'register_meta_box_cb' => array($this, 'add_circlifuls_met_box')
        );

        register_post_type('circlifuls', $args);
    }
    
    function circliful_editor_from_post_type() {
        remove_post_type_support( 'circlifuls', 'editor' );
    }
    
    function add_circlifuls_met_box() {
        add_meta_box( 'settings', __('Circliful Settings', $this->domain), array($this, 'circliful_settings'), 'circlifuls');
    }
    
    function circliful_settings() {
        global $post;
        
        // Noncename needed to verify where the data originated
        echo '<input type="hidden" name="circliful_noncename" id="circliful_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
        
        $_dimension = get_post_meta($post->ID, '_dimension', true);
        $_text = get_post_meta($post->ID, '_text', true);
        $_info = get_post_meta($post->ID, '_info', true);
        $_width = get_post_meta($post->ID, '_width', true);
        $_fontsize = get_post_meta($post->ID, '_fontsize', true);
        $_percentage = get_post_meta($post->ID, '_percentage', true);
        $_fcolor = get_post_meta($post->ID, '_fcolor', true);
        $_bgcolor = get_post_meta($post->ID, '_bgcolor', true);
        $_fill = get_post_meta($post->ID, '_fill', true);
        $_icon = get_post_meta($post->ID, '_icon', true);
        $_iconSize = get_post_meta($post->ID, '_iconSize', true);
        $_iconCol = get_post_meta($post->ID, '_iconCol', true);
        $_inline = get_post_meta($post->ID, '_inline', true);
        
        ?>
        <table width="100%" cellspacing="5" cellpadding="5">
            <tr>
                <th width="30%"><?php _e( 'Dimension', $this->domain ) ?></th>
                <td>
                    <input type="text" name="_dimension" value="<?php echo isset($_dimension) ? $_dimension : '' ?>" size="50" />
                </td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Text', $this->domain ) ?></th>
                <td><input type="text" name="_text" value="<?php echo isset($_text) ? $_text : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Information', $this->domain ) ?></th>
                <td><input type="text" name="_info" value="<?php echo isset($_info) ? $_info : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Width', $this->domain ) ?></th>
                <td><input type="text" name="_width" value="<?php echo isset($_width) ? $_width : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Font Size', $this->domain ) ?></th>
                <td><input type="text" name="_fontsize" value="<?php echo isset($_fontsize) ? $_fontsize : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Percentage', $this->domain ) ?></th>
                <td><input type="text" name="_percentage" value="<?php echo isset($_percentage) ? $_percentage : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Foreground Color', $this->domain ) ?></th>
                <td><input type="text" name="_fcolor" value="<?php echo isset($_fcolor) ? $_fcolor : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Background Color', $this->domain ) ?></th>
                <td><input type="text" name="_bgcolor" value="<?php echo isset($_bgcolor) ? $_bgcolor : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Fill', $this->domain ) ?></th>
                <td><input type="text" name="_fill" value="<?php echo isset($_fill) ? $_fill : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Icon', $this->domain ) ?></th>
                <td><input type="text" name="_icon" value="<?php echo isset($_icon) ? $_icon : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Icon Size', $this->domain ) ?></th>
                <td><input type="text" name="_iconSize" value="<?php echo isset($_iconSize) ? $_iconSize : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Icon Color', $this->domain ) ?></th>
                <td><input type="text" name="_iconCol" value="<?php echo isset($_iconCol) ? $_iconCol : '' ?>" size="50" /></td>
            </tr>
            <tr>
                <th width="30%"><?php _e( 'Inline Border', $this->domain ) ?></th>
                <td>
                    <label>
                        <input <?php echo (isset($_inline) && $_inline == 'yes') ? 'Checked' : '' ?> type="radio" name="_inline" value="yes">
                        <?php _e( 'Yes', $this->domain ) ?>
                    </label>
                    <label>
                        <input <?php echo (isset($_inline) && $_inline == 'no') ? 'Checked' : '' ?> type="radio" name="_inline" value="no">
                        <?php _e( 'No', $this->domain ) ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php _e( '<b>Example Data:</b> Dimension = 250, Text = 72, Information = New Users, Width = 30, Font Size = 38, Percentage = 46, Foreground Color = #61a9dc, Background color = #eee, Fill = #ddd ,Icon = fa-users, Icon Size = 28, Icon color = #ccc. Note that, for icons all font awesome icons are available.', $this->domain ) ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    function save_circliful_settings($post_id, $post) {
        global $post;
        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        if (isset($_POST['circliful_noncename']) && !wp_verify_nonce($_POST['circliful_noncename'], plugin_basename(__FILE__))) {
            return $post->ID;
        }
        
        if (isset($_POST['circliful_noncename'])) {
            $data = array();
            $data['_dimension'] = $_POST['_dimension'];
            $data['_text'] = $_POST['_text'];
            $data['_info'] = $_POST['_info'];
            $data['_width'] = $_POST['_width'];
            $data['_fontsize'] = $_POST['_fontsize'];
            $data['_percentage'] = $_POST['_percentage'];
            $data['_fcolor'] = $_POST['_fcolor'];
            $data['_bgcolor'] = $_POST['_bgcolor'];
            $data['_fill'] = $_POST['_fill'];
            $data['_icon'] = $_POST['_icon'];
            $data['_iconSize'] = $_POST['_iconSize'];
            $data['_iconCol'] = $_POST['_iconCol'];
            $data['_inline'] = $_POST['_inline'];
            
            if ($post->post_type == 'revision') return;
            foreach ($data as $key => $value) {
                if (get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
                    update_post_meta($post->ID, $key, $value);
                } else { // If the custom field doesn't have a value
                    add_post_meta($post->ID, $key, $value);
                }
                if (!$value)
                    delete_post_meta($post->ID, $key); // Delete if blank
            }
        }
    }
    
    function circliful_direct_shortcode( $atts ){
        $data = shortcode_atts( array(
            'dimension'     => 250,
            'text'     => 72,
            'info'     => 'New Users',
            'width'     => 30,
            'fontsize'     => 38,
            'percent'     => 46,
            'fgcolor'     => '#61a9dc',
            'bgcolor'     => '#eee',
            'fill'     => '',
            'icon'     => '',
            'icon_size'     => 28,
            'icon_color'     => '#ccc',
            'border'     => 'no'
        ), $atts );
        
        $html = '<div class="_circliful" data-dimension="'.$data['dimension'].'" data-text="'.$data['text'].'" data-info="'.$data['info'].'" data-width="'.$data['width'].'" data-fontsize="'.$data['fontsize'].'" data-percent="'.$data['percent'].'" data-fgcolor="'.$data['fgcolor'].'" data-bgcolor="'.$data['bgcolor'].'" data-fill="'.$data['fill'].'" data-icon="'.$data['icon'].'" data-icon-size="'.$data['icon_size'].'" data-icon-color="'.$data['icon_color'].'" data-border="'.$data['border'].'"></div>';
        
        return $html;
    }
    
    function circliful_shortcode( $atts ){
	$data = shortcode_atts( array(
            'id' => 'null'
        ), $atts );
        
        if($data['id'] == 'null' || $data['id'] == '') return __( 'You need to define an ID.', $this->domain );
        
        $_dimension = get_post_meta($data['id'], '_dimension', true);
        $_text = get_post_meta($data['id'], '_text', true);
        $_info = get_post_meta($data['id'], '_info', true);
        $_width = get_post_meta($data['id'], '_width', true);
        $_fontsize = get_post_meta($data['id'], '_fontsize', true);
        $_percentage = get_post_meta($data['id'], '_percentage', true);
        $_fcolor = get_post_meta($data['id'], '_fcolor', true);
        $_bgcolor = get_post_meta($data['id'], '_bgcolor', true);
        $_fill = get_post_meta($data['id'], '_fill', true);
        $_icon = get_post_meta($data['id'], '_icon', true);
        $_iconSize = get_post_meta($data['id'], '_iconSize', true);
        $_iconCol = get_post_meta($data['id'], '_iconCol', true);
        $_inline = get_post_meta($data['id'], '_inline', true);
        
        $id = 'circliful_' . $data['id'];
        
        $html = '<div class="_circliful" id="'.$id.'" ';
            if(isset($_dimension)) $html .= "data-dimension = '$_dimension' ";
            if(isset($_text)) $html .= "data-text = '$_text' ";
            if(isset($_info)) $html .= "data-info= '$_info' ";
            if(isset($_width)) $html .= "data-width = '$_width' ";
            if(isset($_fontsize)) $html .= "data-fontsize = '$_fontsize' ";
            if(isset($_percentage)) $html .= "data-percent = '$_percentage' ";
            if(isset($_fcolor)) $html .= "data-fgcolor = '$_fcolor' ";
            if(isset($_bgcolor)) $html .= "data-bgcolor = '$_bgcolor' ";
            if(isset($_fill)) $html .= "data-fill = '$_fill' ";
            if(isset($_icon)) $html .= "data-icon = '$_icon' ";
            if(isset($_iconSize)) $html .= "data-icon-size = '$_iconSize' ";
            if(isset($_iconCol)) $html .= "data-icon-color = '$_iconCol' ";
            if(isset($_inline)) $html .= "data-border = '$_inline' ";
        $html .= '></div>';
        
        return $html;
        
    }
    
    
    public function circlifuls_columns_id($columns) {
        return $columns + array('id' => __('ID', 'mtt'));
    }

    public function circlifuls_custom_id_columns($column_name, $id) {
        echo $id;
    }
    
    
}

$WP_Circliful = new WP_Circliful();

/*
 * Widget
 */
class circliful_widget extends WP_Widget {
        
        public $domain;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
                $this->domain = 'wp_circliful';
		parent::__construct(
			'circliful_widget', // Base ID
			__('Circliful Widget', $this->domain), // Name
			array( 'description' => __( 'A Circliful Widget', $this->domain ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
                $id = apply_filters( 'widget_title', $instance['id'] );
                $title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $id ) )
			echo $args['before_title'] . $title . $args['after_title'];
		echo do_shortcode( "[circliful id='$id']" );
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
                if ( isset( $instance[ 'id' ] ) ) {
			$id = $instance[ 'id' ];
		}
		else {
			$id = __( 'New ID', $this->domain );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', $this->domain ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo isset($title) ? esc_attr( $title ) : 'Circliful Widget Title'; ?>">
                <label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'ID:', $this->domain ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" type="text" value="<?php echo isset($id) ? esc_attr( $id ) : 'Circliful ID'; ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
                $instance = array();
		$instance['id'] = ( ! empty( $new_instance['id'] ) ) ? strip_tags( $new_instance['id'] ) : '';
                $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

add_action( 'widgets_init', 'register_circliful_widget' );
function register_circliful_widget() {
    register_widget( 'circliful_widget' );
}

// Template function
function show_circliful($id) {
    if( !class_exists( 'WP_Circliful' ) ) return __( 'You need to activate the plugin.', 'wp_circliful' );
    if($id == '') return __( 'You need to provide an ID.', 'wp_circliful' );
    return do_shortcode( "[circliful id='$id']" );
}