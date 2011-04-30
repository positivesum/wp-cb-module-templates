<?php
/*
Plugin Name: WPCB Module Templates plugin 
Plugin URI:  http://positivesum.ca/
Description: WP plugin extends the functionality of CB's built in Templates functionality by adding the ability to save modules with the rows
Version: 0.1
Author: Alexander Yachmenev
Author URI: http://www.odesk.com/users/~~94ca72c849152a57
*/
if ( !class_exists( 'wp_cb_module_templates' ) ) {
	class wp_cb_module_templates {
		/**
		 * Initializes plugin variables and sets up wordpress hooks/actions.
		 *
		 * @return void
		 */
		function __construct( ) {
			add_action('admin_init', array(&$this, 'wp_cb_module_templates_admin_init'));
		}
		
		function wp_cb_module_templates_admin_init() {
			add_action('cfct-ajax-return',  array(&$this, 'wp_cb_module_templates_return'), 10, 2 );
			add_filter('cfct-ajax-response', array(&$this, 'wp_cb_module_templates_response'), 10, 2 );
		}
		
		function wp_cb_module_templates_return($result, $cfct_build) {
			if (isset($_POST['func']) && ($_POST['func'] == 'save_as_template')) {
				$message = $result->message();
				$parts = explode(":", $message);
				$guid = trim($parts[1]);
				$args = json_decode(stripslashes($_POST['args']));
				$postmeta = get_post_meta($args->post_id, CFCT_BUILD_POSTMETA, true);
				if (isset($postmeta['data'])) {
					$templates = get_option(CFCT_BUILD_TEMPLATES);
					parse_str($args->data, $data);
					$name = esc_attr(strip_tags($data['cfct-new-template-name']));
					$description = esc_attr(($data['cfct-new-template-description']));
					$template = $cfct_build->template->sanitize_template($postmeta['template']);
					$templates[$guid] = array(
						'guid' => $guid,
						'name' => $name,
						'description' => $description,
						'template' => $template,
						'data' => $postmeta
					);
					update_option(CFCT_BUILD_TEMPLATES, $templates);				
				}
			}
		}			

		function wp_cb_module_templates_response($result, $cfct_build) {
			if (isset($_POST['func']) && ($_POST['func'] == 'insert_template')) {
				$args = json_decode(stripslashes($_POST['args']));
				$templates = get_option(CFCT_BUILD_TEMPLATES);
				$template = $templates[$args->template_id];
				if (isset($template['data'])) {
					$cfct_build->set_postmeta($args->post_id, $template['data']);
					$results = $result->get_results();
					$results['html'] = $cfct_build->template->html($template['data']['data']);
					$result->add($results);
				}
			} 
			return $result;
		}
	}
	$wp_cb_module_templates = new wp_cb_module_templates();	
}
