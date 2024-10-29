<?php

/**
 * 
 * Plugin Name: AI Proposal Builder
 * Version: 1.1.6
 * Description: This is the best plugin to generate the Freelance Proposal in the quickest way! Use Shortcode ['bitcx_aipb_form'] anywhere. The plugin will create a page with the name "Proposal Page" automatically once activated.
 * Author: Bitcraftx
 * Author URI: https://www.bitcraftx.com
 * Requires at least: 6.0
 * Tested up to: 6.5.2
 * Text Domain: ai-proposal-builder
 * License: GPLv2 or later
 * 
 */
if (!defined('ABSPATH')) {
    die('Kangaroos cannot jump here');
}

//Define Dirpath for hooks
define('bitcx_aipb_DIR_PATH', plugin_dir_path(__FILE__));

    class bitcx_ai_proposal_builder {
        public $plugin_version = '1.1.6';

        /**
         * Constructor
         */
        public function __construct() {
            $this->bitcx_aipb_setup_actions();

            // Filter for adding "Settings" link on Plugin Activate Page
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'bitcx_aipb_add_settings_link'));
            
            // Filter for adding "Contact Support" link to plugin details
            add_filter('plugin_row_meta', array($this, 'bitcx_aipb_add_support_link'), 10, 2);
        }
        
         /**
         * Callback function for the "Settings" link filter
         */
        public function bitcx_aipb_add_settings_link($links) {
            $settings_link = '<a href="admin.php?page=bitcx_aipb_settings">Settings</a>';
            array_push($links, $settings_link);
            return $links;
        }

        /**
         * Callback function for the "Contact Support" link filter
         */
        public function bitcx_aipb_add_support_link($data, $file) {
            if (plugin_basename(__FILE__) === $file) {
                // Check if 'Description' key exists, initialize if not
                if (!isset($data['Description'])) {
                    $data['Description'] = '';
                }
        
                $support_link = '<a href="https://bitcraftx.com/contact-us/">Contact Support</a>';
                $data['Description'] .= $support_link;
            }
        
            return $data;
        }
        

        /**
         * Setting up Hooks
         */
        public function bitcx_aipb_setup_actions() {
            // ChatGPT API Key Conformation
            add_action("admin_init", array($this , 'bitcx_aipb_handle_api_submit'));

            // Create Custom Menu
            add_action('admin_menu', array($this, 'bitcx_aipb_create_menu'));

            // Custom Post types
            add_action('init', array($this, 'bitcx_aipb_insert_portfolio_cpt'));
            add_action('init', array($this, 'bitcx_aipb_insert_testimonial_cpt'));
            add_action('init', array($this, 'bitcx_aipb_insert_cta_cpt'));

            // Shortcode for front (user) page
            add_shortcode('bitcx_aipb_form', array($this, 'bitcx_aipb_pr_shortcode'));
            $this->bitcx_aipb_init_metabox();

            // Admin side page css
            add_action( 'admin_head', array($this,'bitcx_aipb_custom_admin_css') );

            //Create page on Activation
            add_action('init', array($this, 'bitcx_aipb_create_pr_page'));

            //Deactivation Hook
            register_deactivation_hook(__FILE__, array($this, 'bitcx_aipb_deactivate_plugin'));

            //API Ajax Handler
            add_action('wp_ajax_bitcx_aipb_get_nonce', array($this, 'bitcx_aipb_ai_request'));
            add_action('wp_ajax_nopriv_bitcx_aipb_get_nonce', array($this, 'bitcx_aipb_ai_request'));

            add_action('wp_ajax_bitcx_aipb_ai_response', array($this, 'bitcx_aipb_ai_request'));
            add_action('wp_ajax_nopriv_bitcx_aipb_ai_response', array($this, 'bitcx_aipb_ai_request'));

            // Fix custom taxonomy parent menu
            add_action( 'parent_file', array($this, 'bitcx_highlight_taxonomy_parent_menu'));
        }
        
        /**
         * Enqueue CSS for admin side Dashboard Page
        */
        public function bitcx_aipb_custom_admin_css(){
            wp_enqueue_style("bitcx_aipb_admin_css", plugin_dir_url(__FILE__) . "src/admin/css/admin.css", array(), $this->plugin_version);
        }


        /**
         * Create Main Menu and Sub menues for Proposal Builder Plugin
        */
        public function bitcx_aipb_create_menu() {
            add_menu_page(
                'Proposal Builder',                                     // Page title
                'Proposal Builder',                                     // Menu title
                'manage_options',                                       // Capability
                'bitcx_aipb_proposal_builder',                      // Menu slug
                false,                                                  // Callback function to display content
                'dashicons-portfolio',                                  // Icon
                30                                                      // Position in the menu
            );
            
            add_submenu_page(
                'bitcx_aipb_proposal_builder',                      // Parent menu slug
                'Proposal Builder',                                     // Page title
                'Proposal Builder',                                     // Menu title
                'manage_options',                                       // Capability
                'bitcx_aipb_settings',                              // Menu slug
                array($this, 'bitcx_aipb_settings_page'),           // Callback function to display content
                0
            );
            add_submenu_page(
                'bitcx_aipb_proposal_builder',                      // Parent menu slug
                'Portfolio Categories',                                 // Page title
                'Portfolio Categories',                                 // Menu title
                'manage_options',                                       // Capability
                'edit-tags.php?taxonomy=bitcx_portfolio_category',  // Menu slug
                null                                                    // Callback function
            );
        }

         /**
         * @param mixed
         * 
         * Setting Parent of Portfolio Category
         * 
         * @return mixed
        */
        public function bitcx_highlight_taxonomy_parent_menu( $parent_file ) {
            if ( get_current_screen()->taxonomy == 'bitcx_portfolio_category' ) {
                $parent_file = 'bitcx_aipb_proposal_builder';
            }
        
            return $parent_file;
        }
    
        /**
         * Returns admin Proposal Builder Main page template
        */
        public function bitcx_aipb_settings_page() {
            require_once 'src/admin/templates/bitcx_aipb_admin_template.php';
        }

        /**
         * Admin side API key submit handler
        */
        public function bitcx_aipb_handle_api_submit() {
             // Handle form submission
             if (isset($_POST['bitcx_aipb_ai_save_api_key']) && isset($_POST['nonce_api_key'])) {
                if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_api_key'])), 'bitcx_aipb_nonce_api_key_submit')) {
                    $_SESSION["bitcx_aipb_api_message_error"] = "Nonce verification failed. Please try again.";
                    return;
                }
                // Sanitize and save API key
                $bitcx_aipb_api_key = sanitize_text_field($_POST['bitcx_aipb_ai_api_key']);
                $bitcx_aipb_is_valid = $this->bitcx_aipb_verify_api_key($bitcx_aipb_api_key);
                // Instantiate the class
                if ($bitcx_aipb_is_valid) {
                    update_option('bitcx_aipb_ai_api_key', sanitize_text_field($bitcx_aipb_api_key));
                    $_SESSION["bitcx_aipb_api_message_success"] = "API key is valid!";
                } else {
                    $_SESSION["bitcx_aipb_api_message_error"] = "Invalid API key. Please try again.";
                }
            }
        }
        /**
         * @param string
         * 
         * Verify API Key
         * 
         * @return boolean
        */
        public function bitcx_aipb_verify_api_key($bitcx_aipb_api_key) {
            $bitcx_aipb_api_url = 'https://api.openai.com/v1/models';
            $bitcx_aipb_api_key = sanitize_text_field($bitcx_aipb_api_key);
        
            // Set the headers
            $headers = [
                'Authorization' => 'Bearer ' . $bitcx_aipb_api_key,
            ];
        
            // Set HTTP request arguments
            $args = [
                'headers' => $headers,
            ];
        
            // Make the API request using WordPress HTTP API
            $response = wp_remote_get($bitcx_aipb_api_url, $args);
        
            // Check for errors
            if (is_wp_error($response)) {
                return false;
            }
        
            $body = wp_remote_retrieve_body($response);
            $response_data = json_decode($body);
        
            return !(isset($response_data) && !empty($response_data->error));
        }
        

        /**
         * bitcx api request from form
         * 
         * @return json
        */
        public function bitcx_aipb_ai_request() {
            // Get the action from the AJAX request
            $action = sanitize_text_field( $_POST['action'] ?? '' );
            
            // If the action is 'get_nonce', generate and return a nonce
            if ($action === 'bitcx_aipb_get_nonce') {
                // Generate nonce
                $nonce = wp_create_nonce('bitcx_aipb_openai_nonce');
                
				if (!wp_verify_nonce($nonce, 'bitcx_aipb_openai_nonce')) {
                    wp_send_json_error('Invalid nonce');
				}
                
                // Return the nonce to the client
                echo wp_json_encode(['nonce' => $nonce]);
                exit; // Exit to prevent further execution of OpenAI request
            }
            
            // If the action is not 'get_nonce', proceed with OpenAI request
            // Get the API key from a secure location
            $api_key = esc_attr(get_option('bitcx_aipb_ai_api_key'));
            // Get the input prompt from the AJAX request
            $prompt = sanitize_text_field($_POST['prompt'] ?? '');
            // Verify nonce
            $nonce = sanitize_key($_POST['nonce'] ?? '');
            if (!wp_verify_nonce($nonce, 'bitcx_aipb_openai_nonce')) {
                die('Invalid nonce');
            }
        
            // Set the request arguments
            // Set the request arguments with a timeout of 20 seconds
            $args = array(
                'body' => wp_json_encode(array(
                    'model' => 'gpt-3.5-turbo',
                    'messages' => array(
                        array('role' => 'user', 'content' => $prompt),
                    ),
                )),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'timeout' => 30, // Set timeout to 20 seconds (or adjust as needed)
            );
            // Make the request using wp_remote_post
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
        
            // Check for errors
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo wp_json_encode(['error' => $error_message]);
            } else {
                // Retrieve the response body
                $body = wp_remote_retrieve_body($response);
                
                // Send the OpenAI response back to the client
                echo wp_json_encode(['response' => $body]);
            }
        }
        

        /**
         * Create Page: Proposal Form
         */

        public function bitcx_aipb_create_pr_page() {

            // Check if the page creation process has already been completed
            if (get_transient('bitcx_aipb_page_created')) {
                return;
            }

            $check_page_exist = get_page_by_path(sanitize_title("Proposal Page"), OBJECT, 'page');

            // Check if the page already exists
            if (empty($check_page_exist)) {
                $page_id = wp_insert_post(
                    array(
                        'comment_status' => 'close',
                        'ping_status'    => 'close',
                        'post_author'    => 1,
                        'post_title'     => ucwords('Proposal Page'),
                        'post_name'      => strtolower(str_replace(' ', '-', trim('Proposal Page'))),
                        'post_status'    => 'publish',
                        'post_content'   => '[bitcx_aipb_form]',
                        'post_type'      => 'page',
                    )
                );
                if ($page_id) {
                    update_option('bitcx_aipb_page_id', sanitize_text_field($page_id));
                }
            }

            // Set a transient to indicate that the page creation process has been completed
            set_transient('bitcx_aipb_page_created', true);
        }

        /**
         * Meta box initialization.
         */

        public function bitcx_aipb_init_metabox() {
            add_action('add_meta_boxes', array($this, 'bitcx_aipb_add_meta_box'));
            add_action('save_post', array($this, 'bitcx_aipb_save_post'), 10, 2);
        }

        /**
         * @param array
         * 
         * Adds the meta box container.
         * 
         * @return void
         */

        public function bitcx_aipb_add_meta_box($post_type) {
            // Limit meta box to certain post types.
            $post_types = array('bitcx_portfolio');

            if (in_array($post_type, $post_types)) {
                add_meta_box(
                    'bitcx_portfolio_fields',
                    __('Portfolio Fields', 'textdomain'),
                    array($this, 'bitcx_aipb_render_meta_box_content'),
                    $post_type,
                    'advanced',
                    'high'
                );
            }
        }

        /**
         * @param int
         * 
         * Save the meta when the post is saved.
         */

        public function bitcx_aipb_save_post($post_id) {

            /*
             * We need to verify this came from our screen and with proper authorization,
             * because save_post can be triggered at other times.
             */

            // Check if our nonce is set.
            if (!isset($_POST['bitcx_aipb_inner_custom_box_nonce'])) {
                return $post_id;
            }

            $nonce = isset($_POST['bitcx_aipb_inner_custom_box_nonce']) ? sanitize_text_field($_POST['bitcx_aipb_inner_custom_box_nonce']) : '';

            // Verify that the nonce is valid.
            if (!wp_verify_nonce($nonce, 'bitcx_aipb_inner_custom_box')) {
                return $post_id;
            }

            /*
             * If this is an autosave, our form has not been submitted,
             * so we don't want to do anything.
             */
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            // Check the user's permissions.
            if ('page' == $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id)) {
                    return $post_id;
                }
            } else {
                if (!current_user_can('edit_post', $post_id)) {
                    return $post_id;
                }
            }

            /* OK, it's safe for us to save the data now. */

            // Sanitize the user input.
            $mydata = sanitize_text_field($_POST['bitcx_portfolio_link']);

            // Update the meta field.
            update_post_meta($post_id, 'bitcx_portfolio_item_link', $mydata);
        }

        /**
         * @param mixed
         * 
         * Render Meta Box content.
         */
        public function bitcx_aipb_render_meta_box_content($post) {

            // Add a nonce field so we can check for it later.
            wp_nonce_field('bitcx_aipb_inner_custom_box', 'bitcx_aipb_inner_custom_box_nonce');

            // Use get_post_meta to retrieve an existing value from the database.
            $value = get_post_meta($post->ID, 'bitcx_portfolio_item_link', true);

            // Display the form, using the current value.
            ?>
<label for="portfolio_link" size="25">
    <?php esc_html_e('Link of Portfolio Site: ', 'textdomain');?>
</label>
<input type="text" id="bitcx_portfolio_link" name="bitcx_portfolio_link" value="<?php echo esc_attr(sanitize_url($value));?>"
    size="25" />
<?php
            }

        /**
         * Portfolio CPT
         */

        public function bitcx_aipb_insert_portfolio_cpt() {
            $supports = array(
                'title', // post title
                'editor', // post content
                'author', // post author
                'thumbnail', // featured images
                'excerpt', // post excerpt
                'custom-fields', // custom fields
                'revisions', // post revisions
            );

            $labels = array(
                'name' => __('Portfolios', 'ai-proposal-builder'),
                'singular_name' => _x('Portfolio', 'singular', 'ai-proposal-builder'),
                'menu_name' => _x('Portfolios', 'admin menu', 'ai-proposal-builder'),
                'name_admin_bar' => _x('Portfolio', 'admin bar', 'ai-proposal-builder'),
                'add_new' => _x('Add New', 'add new', 'ai-proposal-builder'),
                'add_new_item' => __('Add New Portfolio', 'ai-proposal-builder'),
                'new_item' => __('New Portfolio', 'ai-proposal-builder'),
                'edit_item' => __('Edit Portfolio', 'ai-proposal-builder'),
                'view_item' => __('View Portfolio', 'ai-proposal-builder'),
                'all_items' => __('All Portfolios', 'ai-proposal-builder'),
                'search_items' => __('Search Portfolios', 'ai-proposal-builder'),
                'not_found' => __('No Portfolios found.', 'ai-proposal-builder'),
            );
            

            $args = array(
                'supports' => $supports,
                'labels' => $labels,
                'public' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'bitcx_portfolio'),
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => 20, // Adjusted menu position
                'show_in_menu' => 'bitcx_aipb_proposal_builder',
            );
            register_post_type('bitcx_portfolio', $args);

            $category_labels = array(
                'name' => _x('Portfolio Categories', 'taxonomy general name', 'ai-proposal-builder'),
                'singular_name' => _x('Portfolio Category', 'taxonomy singular name', 'ai-proposal-builder'),
                'search_items' => __('Search Portfolio Categories', 'ai-proposal-builder'),
                'all_items' => __('All Portfolio Categories', 'ai-proposal-builder'),
                'parent_item' => __('Parent Portfolio Category', 'ai-proposal-builder'),
                'parent_item_colon' => __('Parent Portfolio Category:', 'ai-proposal-builder'),
                'edit_item' => __('Edit Portfolio Category', 'ai-proposal-builder'),
                'update_item' => __('Update Portfolio Category', 'ai-proposal-builder'),
                'add_new_item' => __('Add New Portfolio Category', 'ai-proposal-builder'),
                'new_item_name' => __('New Portfolio Category Name', 'ai-proposal-builder'),
                'menu_name' => __('Portfolio Categories', 'ai-proposal-builder'),
            );            

            register_taxonomy('bitcx_portfolio_category', 'bitcx_portfolio', array(
                'hierarchical' => true,
                'labels' => $category_labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'bitcx_portfolio_category', 'with_front' => false),
            ));

            register_taxonomy_for_object_type('bitcx_portfolio_category', 'bitcx_portfolio');
        }

        

        /**
         * Testimonial CPT
         */

         public function bitcx_aipb_insert_testimonial_cpt() {
            $supports = array(
                'title', // post title
                'editor', // post content
                'author', // post author
                'thumbnail', // featured images
                'excerpt', // post excerpt
                'custom-fields', // custom fields
                'revisions', // post revisions
            );

            $labels = array(
                'name' => _x('Testimonials', 'plural', 'ai-proposal-builder'),
                'singular_name' => _x('Testimonials', 'singular', 'ai-proposal-builder'),
                'menu_name' => _x('Testimonials', 'admin menu', 'ai-proposal-builder'),
                'name_admin_bar' => _x('Testimonials', 'admin bar', 'ai-proposal-builder'),
                'add_new' => _x('Add New', 'add new', 'ai-proposal-builder'),
                'add_new_item' => __('Add New Testimonial', 'ai-proposal-builder'),
                'new_item' => __('New Testimonial', 'ai-proposal-builder'),
                'edit_item' => __('Edit Testimonial', 'ai-proposal-builder'),
                'view_item' => __('View Testimonial', 'ai-proposal-builder'),
                'all_items' => __('Testimonials', 'ai-proposal-builder'),
                'search_items' => __('Search Testimonials', 'ai-proposal-builder'),
                'not_found' => __('No Testimonial found.', 'ai-proposal-builder'),
            );            

            $args = array(
                'supports' => $supports,
                'labels' => $labels,
                'public' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'bitcx_testimonial'),
                'has_archive' => true,
                'hierarchical' => false,
                'show_in_menu' => 'bitcx_aipb_proposal_builder',
            );
            register_post_type('bitcx_testimonial', $args);
        }

        /**
         * CTAs CPT
         */

         public function bitcx_aipb_insert_cta_cpt() {
            $supports = array(
                'title', // post title
                'editor', // post content
                'author', // post author
                'thumbnail', // featured images
                'excerpt', // post excerpt
                'custom-fields', // custom fields
                'revisions', // post revisions
            );

            $labels = array(
                'name' => _x('CTAs', 'plural', 'ai-proposal-builder'),
                'singular_name' => _x('CTA', 'singular', 'ai-proposal-builder'),
                'menu_name' => _x('CTAs', 'admin menu', 'ai-proposal-builder'),
                'name_admin_bar' => _x('CTAs', 'admin bar', 'ai-proposal-builder'),
                'add_new' => _x('Add New', 'add new', 'ai-proposal-builder'),
                'add_new_item' => __('Add New CTA', 'ai-proposal-builder'),
                'new_item' => __('New CTA', 'ai-proposal-builder'),
                'edit_item' => __('Edit CTA', 'ai-proposal-builder'),
                'view_item' => __('View CTA', 'ai-proposal-builder'),
                'all_items' => __('CTAs', 'ai-proposal-builder'),
                'search_items' => __('Search CTAs', 'ai-proposal-builder'),
                'not_found' => __('No CTA found.', 'ai-proposal-builder'),
            );            

            $args = array(
                'supports' => $supports,
                'labels' => $labels,
                'public' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'bitcx_aipb_cta'),
                'has_archive' => true,
                'hierarchical' => false,
                'show_in_menu' => 'bitcx_aipb_proposal_builder',
            );
            register_post_type('bitcx_aipb_cta', $args);
        }

        /**
         * Enqueue Files
         */

        public function bitcx_aipb_enqueue_files() {
            wp_enqueue_style("bitcx_aipb_builder_css", plugin_dir_url(__FILE__) . "dist//19902f75.css", array(), $this->plugin_version);
            /*
            Swiper.js:
               - Repository: [Swiper.js GitHub Repository](https://github.com/nolimits4web/swiper/)
               - Uncompressed Source Code: [Swiper.js Uncompressed Source](https://github.com/nolimits4web/swiper/tree/master/src)
               - Documentation: [Swiper.js Documentation](https://swiperjs.com/get-started)
               */
            wp_enqueue_script("bitcx-aipb-swiper", plugin_dir_url(__FILE__) . "src/public/js/swiper-bundle.min.js", array('jquery'), $this->plugin_version, true);
            
            /*
           jsPDF:
               - Repository: [jsPDF GitHub Repository](https://github.com/parallax/jsPDF/)
               - Uncompressed Source Code: [jsPDF Uncompressed Source](https://github.com/parallax/jsPDF/tree/master/src)
               - Documentation: [jsPDF Documentation](https://raw.githack.com/MrRio/jsPDF/master/docs/index.html)
               */
            wp_enqueue_script("bitcx-aipb-pdf", plugin_dir_url(__FILE__) . "src/public/js/jspdf.min.js", array('jquery'), $this->plugin_version, true);
            wp_enqueue_script("bitcx_aipb_builder_js", plugin_dir_url(__FILE__) . "dist/d3ae0097.js", array('jquery'), $this->plugin_version, true);
            wp_localize_script( 'bitcx_aipb_user', 'bitcx_aipb_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
            wp_enqueue_script( 'jquery');
        }

        /**
         * Get Portfolio Categories
         */

        public function bitcx_aipb_get_portfolio_cats() {
            $args = array(
                'taxonomy' => 'bitcx_portfolio_category',
                'orderby' => 'name',
                'order'   => 'ASC'
            );

            $portfolio_cats = get_categories($args);
            return $portfolio_cats;
        }

        /**
         * Get Portfolio Items
         */

        public function bitcx_aipb_get_portfolio() {
            $args = array(
                'post_type'      => 'bitcx_portfolio',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            );

            $portfolio = new WP_Query($args);
            return $portfolio;
        }

        /**
         * Get Testimonials Items
         */

        public function bitcx_aipb_get_testimonials() {
            $args = array(
                'post_type'      => 'bitcx_testimonial',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            );

            $testimonials = new WP_Query($args);
            return $testimonials;
        }

        /**
         * Get CTAs Items
         */

        public function bitcx_aipb_get_ctas() {
            $args = array(
                'post_type'      => 'bitcx_aipb_cta',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            );

            $ctas = new WP_Query($args);
            return $ctas;
        }

        /**
         * Shortcode callback
         */

        public function bitcx_aipb_pr_shortcode() {
           
            $this->bitcx_aipb_enqueue_files();
            ob_start();
            $portfolio_items    = $this->bitcx_aipb_get_portfolio();
            $portfolio_cats     = $this->bitcx_aipb_get_portfolio_cats();
            $testimonial_items  = $this->bitcx_aipb_get_testimonials();
            $cta_items          = $this->bitcx_aipb_get_ctas();
            require_once 'src/public/templates/bitcx_aipb_shortcode_template.php';
            $content = ob_get_contents();
            ob_end_clean();
            return $content; 

        }

        /**
         * Deactivate Plugin
        */
        public function bitcx_aipb_deactivate_plugin(){
            set_transient('bitcx_aipb_page_created', false);
            delete_option( 'bitcx_aipb_ai_api_key' );
        }
        
    }
    
    
    // instantiate the plugin class
    $wp_plugin_init = new bitcx_AI_Proposal_Builder();