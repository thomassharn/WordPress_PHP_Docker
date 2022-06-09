<?php
/**
 * Plugin Name: miniOrange SSO using SAML 2.0
 * Plugin URI: http://miniorange.com/
 * Description: miniOrange SAML plugin allows sso/login using Azure, Azure B2C, Okta, ADFS, Keycloak, Onelogin, Salesforce, Google Apps (Gsuite), Salesforce, Shibboleth, Centrify, Ping, Auth0 and other Identity Providers. It acts as a SAML Service Provider which can be configured to establish a trust between the plugin and IDP to securely authenticate and login the user to WordPress site.
 * Version: 4.9.20
 * Author: miniOrange
 * Author URI: http://miniorange.com/
 * License: MIT/Expat
 * License URI: https://docs.miniorange.com/mit-license
 * Text Domain: miniorange-saml-20-single-sign-on
 */

include_once dirname( __FILE__ ) . '/mo_login_saml_sso_widget.php';
require( 'mo-saml-class-customer.php' );
require( 'mo_saml_settings_page.php' );
require( 'MetadataReader.php' );
include_once 'Utilities.php';
include_once 'mo_saml_logger.php';
include_once  'WPConfigEditor.php';

class saml_mo_login {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'miniorange_sso_menu' ) );
		add_action( 'admin_init', array( $this, 'miniorange_login_widget_saml_save_settings' ) );
		add_action( 'admin_init', array( $this, 'miniorange_admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		register_deactivation_hook( __FILE__, array( $this, 'mo_saml_deactivate') );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'my_plugin_action_links') );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		$mo_saml_utls = new Utilities();
		remove_action( 'admin_notices', array( $mo_saml_utls, 'mo_saml_success_message' ) );
		remove_action( 'admin_notices', array( $mo_saml_utls, 'mo_saml_error_message' ) );
		add_action( 'wp_authenticate', array( $this, 'mo_saml_authenticate' ) );
		add_action( 'admin_footer', array( $this, 'feedback_request' ) );
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this,'mo_saml_plugin_action_links') );
		register_activation_hook(__FILE__,array($this,'plugin_activate'));
        add_action('login_form', array( $this, 'mo_saml_modify_login_form' ) );
		add_action('plugins_loaded', array($this, 'mo_saml_load_translations'));
        add_action( 'wp_ajax_skip_entire_plugin_tour', array($this, 'close_welcome_modal'));
		register_shutdown_function( array( $this, 'log_errors' ) );

	}

    function close_welcome_modal(){
        update_option('mo_is_new_user',1);

    }

	function miniorange_admin_notices(){
		if ( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_upgrade_message" ) {
			update_option( 'mo_saml_show_upgrade_notice', 1 );
			return;
		}
		if ( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_addons_message" ) {
			update_option( 'mo_saml_show_addons_notice', 1 );
			return;
		}
		$saml_logger = new MoSAMLLogger();
		if(!$saml_logger->is_log_file_writable() && MoSAMLLogger::is_debugging_enabled())
		{
			add_action('admin_notices', function (){echo wp_kses_post( sprintf(
				__( '<div class="error" style=""><p/>To allow logging, make  <code>"%1s"</code> directory writable.miniOrange will not be able to log the errors.</div>', 'miniorange-saml-20-single-sign-on' ),
				MO_SAML_DIRC
			) ); } );
		}
		if($saml_logger->is_log_file_writable() && MoSAMLLogger::is_debugging_enabled()){
			add_action('admin_notices', function (){echo wp_kses_post( sprintf(
			/* translators: %s: documentation URL */
				__( '<div class="updated" ><p/> miniOrange SAML 2.0 logs are active. Want to turn it off? <a href="%s">Learn more here.</a></div>', 'miniorange-saml-20-single-sign-on' ),
				admin_url().'admin.php?page=mo_saml_enable_debug_logs'
			) ); } );
		}
	}
	/**
	 * Ensures fatal errors are logged so they can be picked up in the status report.
	 *
	 * @since 4.9.09
	 */
	public function log_errors() {
		$logger = new MoSAMLLogger;
		$logger->log_critical_errors();
	}
	function my_plugin_action_links( $links ) {
		$url = esc_url( add_query_arg(
			'page',
			'mo_saml_settings',
			get_admin_url() . 'admin.php?page=mo_saml_settings&tab=licensing'
		) );

		$license_link = "<a href='$url'>" . __( 'Premium Plans' ) . '</a>';

		array_push(
			$links,
			$license_link
		);
		return $links;
	}

	function mo_saml_load_translations(){
		load_plugin_textdomain('miniorange-saml-20-single-sign-on', false, dirname(plugin_basename(__FILE__)). '/resources/lang/');
	}


	function feedback_request() {

		mo_saml_display_saml_feedback_form();
	}

	function mo_login_widget_saml_options() {
		global $wpdb;

		mo_saml_register_saml_sso();
	}

	public function mo_saml_deactivate(){
        delete_option('mo_is_new_user');

		if(mo_saml_is_customer_registered_saml(false))
			return;
		if(!mo_saml_is_curl_installed())
			return;

		$site_home_path = ABSPATH;
		$wp_config_path = $site_home_path . 'wp-config.php';
		$wp_config_editor = new WPConfigEditor($wp_config_path);  //that will be null in case wp-config.php is not writable

		if(is_writeable($wp_config_path)){
			$wp_config_editor->update('MO_SAML_LOGGING', 'false'); //fatal error
		}

		delete_option('mo_saml_show_upgrade_notice');
		delete_option('mo_saml_show_addons_notice');
		wp_redirect('plugins.php');

	}

	public function mo_saml_remove_account() {
		if ( ! is_multisite() ) {
			//delete all customer related key-value pairs
			delete_option( 'mo_saml_host_name' );
			delete_option( 'mo_saml_new_registration' );
			delete_option( 'mo_saml_admin_phone' );
			delete_option( 'mo_saml_admin_password' );
			delete_option( 'mo_saml_verify_customer' );
			delete_option( 'mo_saml_admin_customer_key' );
			delete_option( 'mo_saml_admin_api_key' );
			delete_option( 'mo_saml_customer_token' );
			delete_option('mo_saml_admin_email');
			delete_option( 'mo_saml_message' );
			delete_option( 'mo_saml_registration_status' );
			delete_option( 'mo_saml_idp_config_complete' );
			delete_option( 'mo_saml_transactionId' );
			delete_option( 'mo_proxy_host' );
			delete_option( 'mo_proxy_username' );
			delete_option( 'mo_proxy_port' );
			delete_option( 'mo_proxy_password' );
			delete_option( 'mo_saml_show_mo_idp_message' );


		} else {
			global $wpdb;
			$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			$original_blog_id = get_current_blog_id();

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				//delete all your options
				//E.g: delete_option( {option name} );
				delete_option( 'mo_saml_host_name' );
				delete_option( 'mo_saml_new_registration' );
				delete_option( 'mo_saml_admin_phone' );
				delete_option( 'mo_saml_admin_password' );
				delete_option( 'mo_saml_verify_customer' );
				delete_option( 'mo_saml_admin_customer_key' );
				delete_option( 'mo_saml_admin_api_key' );
				delete_option( 'mo_saml_customer_token' );
				delete_option( 'mo_saml_message' );
				delete_option( 'mo_saml_registration_status' );
				delete_option( 'mo_saml_idp_config_complete' );
				delete_option( 'mo_saml_transactionId' );
				delete_option( 'mo_saml_show_mo_idp_message' );
				delete_option('mo_saml_admin_email');
			}
			switch_to_blog( $original_blog_id );
		}
	}

	function plugin_settings_style( $page) {
		if ( $page != 'toplevel_page_mo_saml_settings' && !(isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_licensing') && $page != 'miniorange-saml-2-0-sso_page_mo_saml_enable_debug_logs') {
            if($page != 'index.php')
		        return;
		}
		if((isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'licensing') || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_licensing') || (isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'save') || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_settings') || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_enable_debug_logs')){
			wp_enqueue_style( 'mo_saml_bootstrap_css', plugins_url( 'includes/css/bootstrap/bootstrap.min.css', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, 'all' );
		}

		wp_enqueue_style('mo_saml_jquery_ui_style',plugins_url('includes/css/jquery-ui.min.css', __FILE__), array(), mo_saml_options_plugin_constants::Version, 'all');
        wp_enqueue_style( 'mo_saml_admin_gotham_font_style', 'https://fonts.cdnfonts.com/css/gotham', array(), mo_saml_options_plugin_constants::Version, 'all' );
		wp_enqueue_style( 'mo_saml_admin_settings_style', plugins_url( 'includes/css/style_settings.min.css', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, 'all' );
		wp_enqueue_style( 'mo_saml_admin_settings_phone_style', plugins_url( 'includes/css/phone.css', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, 'all' );
		wp_enqueue_style( 'mo_saml_time_settings_style', plugins_url( 'includes/css/datetime-style-settings.min.css', __FILE__ ), array(),mo_saml_options_plugin_constants::Version, 'all' );
		wp_enqueue_style( 'mo_saml_wpb-fa', plugins_url( 'includes/css/style-icon.css', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, 'all' );

	}

	function plugin_settings_script( $page ) {

		if ( $page != 'toplevel_page_mo_saml_settings' && !(isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_licensing') && $page != 'miniorange-saml-2-0-sso_page_mo_saml_enable_debug_logs') {
			return;
		}
		wp_localize_script( 'rml-script', 'readmelater_ajax', array( 'ajax_url' => admin_url('admin-ajax.php')) );


		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('mo_saml_select2_script', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js');
		wp_enqueue_script('mo_saml_timepicker_script', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js');
		wp_enqueue_script( 'mo_saml_admin_settings_script', plugins_url( 'includes/js/settings.min.js', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, false );
		wp_enqueue_script( 'mo_saml_admin_settings_phone_script', plugins_url( 'includes/js/phone.min.js', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, false );

		if((isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'licensing') || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_licensing')){
			wp_enqueue_script( 'mo_saml_modernizr_script', plugins_url( 'includes/js/modernizr.js', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, false );
			wp_enqueue_script( 'mo_saml_popover_script', plugins_url( 'includes/js/bootstrap/popper.min.js', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, false );
			wp_enqueue_script( 'mo_saml_bootstrap_script', plugins_url( 'includes/js/bootstrap/bootstrap.min.js', __FILE__ ), array(), mo_saml_options_plugin_constants::Version, false );
		}


	}

    function mo_saml_modify_login_form() {
        if(get_option('mo_saml_add_sso_button_wp') == 'true')
            $this->mo_saml_add_sso_button();
    }

    function mo_saml_add_sso_button() {
        if(!is_user_logged_in()){
            $saml_idp_name = get_option('saml_identity_name');
            $customButtonText = $saml_idp_name ? 'Login with '. $saml_idp_name : 'Login with SSO';
			$html = '
                <script>
                window.onload = function() {
	                var target_btn = document.getElementById("mo_saml_button");
	                var before_element = document.querySelector("#loginform p");
	                before_element.before(target_btn);
                };                  
                    function loginWithSSOButton(id) {
                        if( id === "mo_saml_login_sso_button")
                            document.getElementById("saml_user_login_input").value = "saml_user_login";
                        document.getElementById("loginform").submit(); 
                    }
				</script>
                <input id="saml_user_login_input" type="hidden" name="option" value="">
                <div id="mo_saml_button" style="height:88px;">
                	<div id="mo_saml_login_sso_button" onclick="loginWithSSOButton(this.id)" style="width:100%;display:flex;justify-content:center;align-items:center;font-size:14px;margin-bottom:1.3rem" class="button button-primary">
                    <img style="width:20px;height:15px;padding-right:1px" src="'. plugin_dir_url(__FILE__) . 'images/lock-icon.png">'.$customButtonText.'
                	</div>
                	<div style="padding:5px;font-size:14px;height:20px;text-align:center"><b>OR</b></div>
            	</div>';
			echo $html;
        }
    }

	function mo_saml_cleanup_logs() {
		$logger = new MoSAMLLogger();
		$retention_period = absint(apply_filters('mo_saml_logs_retention_period',0));
		$timestamp = strtotime( "-{$retention_period} days" );
		if ( is_callable( array( $logger, 'delete_logs_before_timestamp' ) ) ) {
			$logger->delete_logs_before_timestamp($timestamp);
		}
	}
	public function plugin_activate(){
		if(is_multisite()){
			global $wpdb;
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			$original_blog_id = get_current_blog_id();

			foreach($blog_ids as $blog_id){
				switch_to_blog($blog_id);
				update_option('mo_saml_guest_log',true);
				update_option('mo_saml_guest_enabled',true);
				update_option( 'mo_saml_free_version', 1 );

			}
			switch_to_blog($original_blog_id);
		} else {
			update_option('mo_saml_guest_log',true);
			update_option('mo_saml_guest_enabled',true);
			update_option( 'mo_saml_free_version', 1 );
		}
		update_option('mo_plugin_do_activation_redirect', true);
	}

	static function mo_check_option_admin_referer($option_name){
		return (isset($_POST['option']) and $_POST['option']==$option_name and check_admin_referer($option_name));
	}

	function miniorange_login_widget_saml_save_settings() {

		if (get_option('mo_plugin_do_activation_redirect')) {
			delete_option('mo_plugin_do_activation_redirect');

			if(!isset($_GET['activate-multi']))
			{
				wp_redirect(admin_url() . 'admin.php?page=mo_saml_settings');
				exit;
			}
		}
		if ( current_user_can( 'manage_options' ) ) {

			$saml_logger = new MoSAMLLogger();
			$mo_saml_utils = new Utilities();

			if(self::mo_check_option_admin_referer("clear_attrs_list")){
				delete_option("mo_saml_test_config_attrs");
				update_option('mo_saml_message',__('List of attributes cleared','miniorange-saml-20-single-sign-on'));
				$mo_saml_utils->mo_saml_show_success_message();
				$saml_logger->add_log(mo_saml_error_log::showMessage('CLEAR_ATTR_LIST'),MoSAMLLogger::INFO);
			}

			if ( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_mo_idp_message" ) {
				update_option( 'mo_saml_show_mo_idp_message', 1 );

				return;
			}
			if( self::mo_check_option_admin_referer("change_miniorange")){
				self::mo_saml_remove_account();
				update_option('mo_saml_guest_enabled',true);
				//update_option( 'mo_saml_message', 'Logged out of miniOrange account' );
				//$this->mo_saml_show_success_message();
				return;
			}

			if ( self::mo_check_option_admin_referer("login_widget_saml_save_settings")) {
				if ( ! mo_saml_is_curl_installed() ) {
					update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Save Identity Provider Configuration failed.' );
					$mo_saml_utils->mo_saml_show_error_message();

					return;
				}


				if (( $mo_saml_utils->mo_saml_check_empty_or_null( $_POST['saml_identity_name'] ) || $mo_saml_utils->mo_saml_check_empty_or_null( $_POST['saml_login_url'] ) || $mo_saml_utils->mo_saml_check_empty_or_null( $_POST['saml_issuer'] )) && $mo_saml_utils->mo_saml_check_empty_or_null($_POST['saml_b2c_tenant_id'])) {
					update_option( 'mo_saml_message', __('All the fields are required. Please enter valid entries.','miniorange-saml-20-single-sign-on' ));
					$mo_saml_utils->mo_saml_show_error_message();
					$log_message = ['saml_identity_name' => $_POST['saml_identity_name'], 'same_login_url' => $_POST['saml_login_url'], 'saml_issuer' => $_POST['saml_issuer'], 'saml_b2c_tenant_id' => $_POST['saml_b2c_tenant_id']];
					$saml_logger->add_log(mo_saml_error_log::showMessage('INVAILD_CONFIGURATION_SETTING'),MoSAMLLogger::ERROR);

					return;
				} else if ( ! preg_match( "/^\w*$/", $_POST['saml_identity_name'] ) ) {
					update_option( 'mo_saml_message', __('Please match the requested format for Identity Provider Name. Only alphabets, numbers and underscore is allowed.','miniorange-saml-20-single-sign-on') );
					$mo_saml_utils->mo_saml_show_error_message();

					$log_message = ['saml_identity_name' => $_POST['saml_identity_name']];
					$saml_logger->add_log(mo_saml_error_log::showMessage('INVAILD_IDP_NAME_FORMAT',$log_message),MoSAMLLogger::ERROR);

					return;
				} else if(isset($_POST['saml_identity_name']) and !empty($_POST['saml_identity_name'])) {
					$saml_identity_name    = htmlspecialchars(trim( $_POST['saml_identity_name'] ));
					$saml_login_url        = htmlspecialchars(trim( $_POST['saml_login_url'] ));
					$saml_issuer           = htmlspecialchars(trim( $_POST['saml_issuer'] ));
					$saml_x509_certificate =  $_POST['saml_x509_certificate'];

					update_option( 'saml_identity_name', $saml_identity_name );
					update_option( 'saml_login_url', $saml_login_url );
					update_option( 'saml_issuer', $saml_issuer );

					if(array_key_exists('mo_saml_identity_provider_identifier_name',$_POST)){
						$mo_saml_identity_provider_identifier_name = htmlspecialchars($_POST['mo_saml_identity_provider_identifier_name']);
						update_option('mo_saml_identity_provider_identifier_name',$mo_saml_identity_provider_identifier_name);
					}


					foreach ( $saml_x509_certificate as $key => $value ) {
						if ( empty( $value ) ) {
							unset( $saml_x509_certificate[ $key ] );
						} else {
							$saml_x509_certificate[ $key ] = Utilities::sanitize_certificate( $value );

							if ( ! @openssl_x509_read( $saml_x509_certificate[ $key ] ) ) {
								update_option( 'mo_saml_message', __('Invalid certificate: Please provide a valid X.509 certificate.','miniorange-saml-20-single-sign-on') );
								$mo_saml_utils->mo_saml_show_error_message();
								delete_option( 'saml_x509_certificate' );
								$saml_logger->add_log(mo_saml_error_log::showMessage('INVALID_CERT'),MoSAMLLogger::ERROR);
								return;
							}
						}
					}
					if ( empty( $saml_x509_certificate ) ) {
						update_option( "mo_saml_message", __('Invalid Certificate: Please provide a certificate' ,'miniorange-saml-20-single-sign-on'));
						$mo_saml_utils->mo_saml_show_error_message();
						$saml_logger->add_log(mo_saml_error_log::showMessage('IDP_CERT_NULL'),MoSAMLLogger::ERROR);

						return;
					}
					$saml_x509_certificate = maybe_serialize($saml_x509_certificate);
					update_option( 'saml_x509_certificate',  $saml_x509_certificate );

					$iconv_enabled = '';
					if(array_key_exists('enable_iconv',$_POST))
						$iconv_enabled = 'checked';

					update_option('mo_saml_encoding_enabled',$iconv_enabled);

					$log_message =
						array('saml_identity_name' =>$saml_identity_name,
						      'saml_login_url' => $saml_login_url,
						      'saml_issuer' => $saml_issuer ,
						      'saml_identity_provider_name' => $mo_saml_identity_provider_identifier_name,
						      'saml_x509_certificate' => $saml_x509_certificate,
						      'iconv_enabled' => $iconv_enabled);
					$saml_logger->add_log(mo_saml_error_log::showMessage('SERVICE_PROVIDER_CONF',$log_message),MoSAMLLogger::DEBUG);


				}


				if(isset($_POST['saml_b2c_tenant_id']) and !empty($_POST['saml_b2c_tenant_id'])){
					$b2c_tenant_id = htmlspecialchars($_POST['saml_b2c_tenant_id']);
					$b2c_tenant_id_postfix = strpos($b2c_tenant_id, ".onmicrosoft.com");
					if($b2c_tenant_id_postfix !== false)
						$b2c_tenant_id = substr($b2c_tenant_id, 0, $b2c_tenant_id_postfix);
					update_option('saml_b2c_tenant_id', $b2c_tenant_id);
					$log_message = array(
						'b2c_tenant_id'=> $b2c_tenant_id
					);
					$saml_logger->add_log(mo_saml_error_log::showMessage('AZURE_B2C_CONFIGURATION_TENTENT_ID',$log_message), MoSAMLLogger::DEBUG);

				}
				if(isset($_POST['saml_IdentityExperienceFramework_id']) and !empty($_POST['saml_IdentityExperienceFramework_id'])){
					$saml_IdentityExperienceFramework_id = htmlspecialchars($_POST['saml_IdentityExperienceFramework_id']);
					update_option('saml_IdentityExperienceFramework_id', $saml_IdentityExperienceFramework_id);
					$log_message = array(
						'saml_IdentityExperienceFramework_id' =>  $saml_IdentityExperienceFramework_id
					);
					$saml_logger->add_log(mo_saml_error_log::showMessage('AZURE_B2C_CONFIGURATION_IEF_ID',$log_message), MoSAMLLogger::DEBUG);
				}
				if(isset($_POST['saml_ProxyIdentityExperienceFramework_id']) and !empty($_POST['saml_ProxyIdentityExperienceFramework_id'])){
					$saml_ProxyIdentityExperienceFramework_id = htmlspecialchars($_POST['saml_ProxyIdentityExperienceFramework_id']);
					update_option('saml_ProxyIdentityExperienceFramework_id', $saml_ProxyIdentityExperienceFramework_id);
					$log_message = array(
						'Azure B2C saml_ProxyIdentityExperienceFramework_id' => $saml_ProxyIdentityExperienceFramework_id
					);
					$saml_logger->add_log(mo_saml_error_log::showMessage('AZURE_B2C_CONFIGURATION_PEF_ID',$log_message), MoSAMLLogger::DEBUG);
				}


				update_option( 'mo_saml_message', __('Identity Provider details saved successfully.','miniorange-saml-20-single-sign-on' ));
				$mo_saml_utils->mo_saml_show_success_message();

			}

			if(self::mo_check_option_admin_referer('update_sso_config')){
				$metadata_url = 'https://tenant-name.b2clogin.com/tenant-name.onmicrosoft.com/B2C_1A_signup_signin_saml/Samlp/metadata';
				$b2c_tenant_id = get_option('saml_b2c_tenant_id');
				$metadata_url = str_replace('tenant-name', $b2c_tenant_id, $metadata_url);
				$this->_handle_upload_metadata($saml_logger, $metadata_url);
				$saml_logger->add_log(mo_saml_error_log::showMessage('AZURE_B2C_PLUGIN_CONFIGURATION_UPDATED'), MoSAMLLogger::DEBUG);
			}

			//Update SP Entity ID
			if(self::mo_check_option_admin_referer('mo_saml_update_idp_settings_option')){
				if(isset($_POST['mo_saml_sp_entity_id'])) {
					$sp_entity_id = htmlspecialchars($_POST['mo_saml_sp_entity_id']);
					update_option('mo_saml_sp_entity_id', $sp_entity_id);
				}

				update_option('mo_saml_message', __('Settings updated successfully.','miniorange-saml-20-single-sign-on'));
				$mo_saml_utils->mo_saml_show_success_message();
				$log_message = [ 'sp_entity_id' =>  $sp_entity_id ];
				$saml_logger->add_log(mo_saml_error_log::showMessage('SP_ENTITY_ID',$log_message), MoSAMLLogger::DEBUG);

			}
			//Save Attribute Mapping
			if (self::mo_check_option_admin_referer("login_widget_saml_attribute_mapping") ) {

				if ( ! mo_saml_is_curl_installed() ) {
					update_option( 'mo_saml_message', __('ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Save Attribute Mapping failed.','miniorange-saml-20-single-sign-on') );
					$mo_saml_utils->mo_saml_show_error_message();

					return;
				}


				update_option( 'mo_saml_message', __('Attribute Mapping details saved successfully','miniorange-saml-20-single-sign-on' ));
				$mo_saml_utils->mo_saml_show_success_message();

			}
			//Save Role Mapping
			if (self::mo_check_option_admin_referer("login_widget_saml_role_mapping")) {

				if ( ! mo_saml_is_curl_installed() ) {
					update_option( 'mo_saml_message', __('ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Save Role Mapping failed.','miniorange-saml-20-single-sign-on') );
					$mo_saml_utils->mo_saml_show_error_message();

					return;
				}


				update_option( 'saml_am_default_user_role', htmlspecialchars($_POST['saml_am_default_user_role']) );

				update_option( 'mo_saml_message', __('Role Mapping details saved successfully.','miniorange-saml-20-single-sign-on' ));
				$mo_saml_utils->mo_saml_show_success_message();

				$log_message = [ 'default_user_role' =>$_POST['saml_am_default_user_role']];
				$saml_logger->add_log(mo_saml_error_log::showMessage('DEFAULT_ROLE_ID',$log_message), MoSAMLLogger::DEBUG);

			}

			if(self::mo_check_option_admin_referer("mo_saml_demo_request_option")){

				if(isset($_POST['mo_saml_demo_email']))
					$demo_email = htmlspecialchars($_POST['mo_saml_demo_email']);

				if(isset($_POST['mo_saml_demo_plan']))
					$demo_plan_selected = htmlspecialchars($_POST['mo_saml_demo_plan']);

				if(isset($_POST['mo_saml_demo_description']))
					$demo_description = htmlspecialchars($_POST['mo_saml_demo_description']);

				$license_plans = mo_saml_license_plans::$license_plans;
				if(isset($license_plans[$demo_plan_selected]))
					$demo_plan = $license_plans[$demo_plan_selected];

				$addons = mo_saml_options_addons::$ADDON_TITLE;

				$addons_selected = array();
				foreach($addons as $key => $value){
					if(isset($_POST[$key]) && $_POST[$key] == "true")
						$addons_selected[$key] = $value;
				}
				$status = "";
				if(empty($demo_email)){
					$demo_email = get_option('mo_saml_admin_email');
					$status = "Error :" ."Email address for Demo is Empty.";
				} else if (!filter_var($demo_email, FILTER_VALIDATE_EMAIL)) {
                    update_option( 'mo_saml_message', __('Please enter a valid email address.' ,'miniorange-saml-20-single-sign-on'));
                    $mo_saml_utils->mo_saml_show_error_message();
                    return;
                }else{
					$license_plans_slugs = mo_saml_license_plans::$license_plans_slug;
					if(array_key_exists($demo_plan_selected,$license_plans_slugs)){
						$url = 'https://demo.miniorange.com/wordpress-saml-demo/';
						$headers = array( 'Content-Type' => 'application/x-www-form-urlencoded', 'charset' => 'UTF - 8');
						$args = array(
							'method' =>'POST',
							'body' => array(
								'option' => 'mo_auto_create_demosite',
								'mo_auto_create_demosite_email' => $demo_email,
								'mo_auto_create_demosite_usecase' => $demo_description,
								'mo_auto_create_demosite_demo_plan' => $license_plans_slugs[$demo_plan_selected],
							),
							'timeout' => '20',
							'redirection' => '5',
							'httpversion' => '1.0',
							'blocking' => true,
							'headers' => $headers,
						);

						$response = wp_remote_post( $url, $args );
						if ( is_wp_error( $response ) ) {
							$error_message = $response->get_error_message();
							echo "Something went wrong: $error_message";
							exit();
						}
						$output = wp_remote_retrieve_body($response);
						$output = json_decode($output);
						if(is_null($output)){
							update_option('mo_saml_message', __('Something went wrong. Please reach out to us using the Support/Contact Us form to get help with the demo.','miniorange-saml-20-single-sign-on'));
							$status = __('Error :','miniorange-saml-20-single-sign-on') . __('Something went wrong while setting up demo.','miniorange-saml-20-single-sign-on');
						}

						if($output->status == 'SUCCESS'){
							update_option('mo_saml_message', $output->message);
							$status = __('Success :','miniorange-saml-20-single-sign-on').$output->message;
						}else{
							update_option('mo_saml_message', $output->message);
							$status = __('Error :','miniorange-saml-20-single-sign-on') .$output->message;
						}
					}else{
						$status = __('Please setup manual demo.','miniorange-saml-20-single-sign-on');
					}
				}

				$message = "[Demo For Customer] : " . $demo_email;
				if(!empty($demo_plan))
					$message .= " <br>[Selected Plan] : " . $demo_plan;
				if(!empty($demo_description))
					$message .= " <br>[Requirements] : " . $demo_description;

				$message .= " <br>[Status] : " .$status;
				if(!empty($addons_selected)){
					$message .= " <br>[Addons] : ";
					foreach($addons_selected as $key => $value){
						$message .= $value;
						if(next($addons_selected))
							$message .= ", ";
					}
				}

				$user = wp_get_current_user();
				$customer = new Customersaml();
				$email = get_option( "mo_saml_admin_email" );
				if ( $email == '' ) {
					$email = $user->user_email;
				}
				$phone = get_option( 'mo_saml_admin_phone' );
				$submited = json_decode( $customer->send_email_alert( $email, $phone, $message, true ), true );
				if ( json_last_error() == JSON_ERROR_NONE ) {
					if ( is_array( $submited ) && array_key_exists( 'status', $submited ) && $submited['status'] == 'ERROR' ) {
						update_option( 'mo_saml_message', $submited['message'] );
						$mo_saml_utils->mo_saml_show_error_message();

					}
					else {
						$demo_status = strpos($status,"Error");
						if ( $submited == false || $demo_status !== false ) {

							update_option( 'mo_saml_message', $status );
							$mo_saml_utils->mo_saml_show_error_message();
						} else {
							update_option( 'mo_saml_message', __('Thanks! We have received your request and will shortly get in touch with you.','miniorange-saml-20-single-sign-on'));
							$mo_saml_utils->mo_saml_show_success_message();
						}
					}
				}

			}

			if (self::mo_check_option_admin_referer("saml_upload_metadata")) {
				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
				}
				$this->_handle_upload_metadata($saml_logger);
			}
			if ( self::mo_check_option_admin_referer("mo_saml_register_customer")) {

				//register the admin to miniOrange
				$user = wp_get_current_user();
				if ( ! mo_saml_is_curl_installed() ) {
					update_option( 'mo_saml_message', __('ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Registration failed.' ,'miniorange-saml-20-single-sign-on'));
					$mo_saml_utils->mo_saml_show_error_message();

					return;
				}

				//validation and sanitization
                $email = '';
                $password = '';
                $confirmPassword = '';

                if(isset($_POST['registerEmail']) and !empty($_POST['registerEmail'])) {

                    if ($mo_saml_utils->mo_saml_check_empty_or_null($_POST['password']) || $mo_saml_utils->mo_saml_check_empty_or_null($_POST['confirmPassword'])) {

                        update_option('mo_saml_message', __('Please enter the required fields.', 'miniorange-saml-20-single-sign-on'));
                        $mo_saml_utils->mo_saml_show_error_message();

                        return;
                    } else if (!filter_var($_POST['registerEmail'], FILTER_VALIDATE_EMAIL)) {
                        update_option('mo_saml_message', __('Please enter a valid email address.', 'miniorange-saml-20-single-sign-on'));
                        $mo_saml_utils->mo_saml_show_error_message();
                        return;
                    } else if ($this->checkPasswordpattern(htmlspecialchars($_POST['password']))) {
                        update_option('mo_saml_message', __('Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*-_) should be present.', 'miniorange-saml-20-single-sign-on'));
                        $mo_saml_utils->mo_saml_show_error_message();
                        return;
                    } else {

                        $email = sanitize_email($_POST['registerEmail']);
                        $password = stripslashes(htmlspecialchars($_POST['password']));
                        $confirmPassword = stripslashes(htmlspecialchars($_POST['confirmPassword']));
                    }
                    update_option('mo_saml_admin_email', $email);

                    if (strcmp($password, $confirmPassword) == 0) {
                        update_option('mo_saml_admin_password', $password);
                        $email = get_option('mo_saml_admin_email');
                        $customer = new CustomerSaml();
                        $content = json_decode($customer->check_customer(), true);
                        if (!is_null($content)) {
                            if (strcasecmp($content['status'], 'CUSTOMER_NOT_FOUND') == 0) {

                                $response = $this->create_customer();
                                if (is_array($response) && array_key_exists('status', $response) && $response['status'] == 'success') {
                                    wp_redirect(admin_url('/admin.php?page=mo_saml_settings&tab=licensing'), 301);
                                    exit;
                                }
                            } else {
                                $response = $this->get_current_customer();
                                if (is_array($response) && array_key_exists('status', $response) && $response['status'] == 'success') {
                                    wp_redirect(admin_url('/admin.php?page=mo_saml_settings&tab=licensing'), 301);
                                    exit;
                                }
                                //$this->mo_saml_show_error_message();
                            }
                        }

                    } else {
                        update_option('mo_saml_message', __('Passwords do not match.', 'miniorange-saml-20-single-sign-on'));
                        delete_option('mo_saml_verify_customer');
                        $mo_saml_utils->mo_saml_show_error_message();
                    }
                    return;
                }
                else if ( isset($_POST['loginEmail']) and !empty($_POST['loginEmail'])) {
                    if ($mo_saml_utils->mo_saml_check_empty_or_null( $_POST['password'] ) ) {
                        update_option( 'mo_saml_message', __('All the fields are required. Please enter valid entries.','miniorange-saml-20-single-sign-on' ));
                        $mo_saml_utils->mo_saml_show_error_message();

                        return;
                    } else if($this->checkPasswordpattern(htmlspecialchars($_POST['password']))){
                        update_option( 'mo_saml_message', __('Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*-_) should be present.' ,'miniorange-saml-20-single-sign-on'));
                        $mo_saml_utils->mo_saml_show_error_message();
                        return;
                    }else {
                        $email    = sanitize_email( $_POST['loginEmail'] );
                        $password = stripslashes( htmlspecialchars($_POST['password'] ));
                    }

                    update_option( 'mo_saml_admin_email', $email );
                    update_option( 'mo_saml_admin_password', $password );
                    $customer    = new Customersaml();
                    $content     = $customer->get_customer_key();
                    if(!is_null($content)){
                        $customerKey = json_decode( $content, true );
                        if ( json_last_error() == JSON_ERROR_NONE ) {
                            update_option( 'mo_saml_admin_customer_key', $customerKey['id'] );
                            update_option( 'mo_saml_admin_api_key', $customerKey['apiKey'] );
                            update_option( 'mo_saml_customer_token', $customerKey['token'] );
                            $certificate = get_option( 'saml_x509_certificate' );
                            if ( empty( $certificate ) ) {
                                update_option( 'mo_saml_free_version', 1 );
                            }
                            update_option( 'mo_saml_admin_password', '' );
                            update_option( 'mo_saml_message', __('Customer retrieved successfully','miniorange-saml-20-single-sign-on' ));
                            update_option( 'mo_saml_registration_status', 'Existing User' );
                            delete_option( 'mo_saml_verify_customer' );
                            $mo_saml_utils->mo_saml_show_success_message();
                            //if(is_array($response) && array_key_exists('status', $response) && $response['status'] == 'success'){
                            wp_redirect( admin_url( '/admin.php?page=mo_saml_settings&tab=licensing' ), 301 );
                            exit;
                            //}
                        } else {
                            update_option( 'mo_saml_message', __('Invalid username or password. Please try again.','miniorange-saml-20-single-sign-on' ));
                            $mo_saml_utils->mo_saml_show_error_message();
                        }
                        update_option( 'mo_saml_admin_password', '' );
                    }
                }
			}
			else if( self::mo_check_option_admin_referer("mosaml_metadata_download")){
				mo_saml_miniorange_generate_metadata(true);
			}
			if ( self::mo_check_option_admin_referer("mo_saml_verify_customer") ) {    //register the admin to miniOrange

				if ( ! mo_saml_is_curl_installed() ) {
					update_option( 'mo_saml_message', __('ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Login failed.','miniorange-saml-20-single-sign-on' ));
					$mo_saml_utils->mo_saml_show_error_message();

					return;
				}

				//validation and sanitization
				$email    = '';
				$password = '';
				if ( $mo_saml_utils->mo_saml_check_empty_or_null( $_POST['email'] ) || $mo_saml_utils->mo_saml_check_empty_or_null( $_POST['password'] ) ) {
					update_option( 'mo_saml_message', __('All the fields are required. Please enter valid entries.','miniorange-saml-20-single-sign-on' ));
					$mo_saml_utils->mo_saml_show_error_message();

					return;
				} else if($this->checkPasswordpattern(htmlspecialchars($_POST['password']))){
					update_option( 'mo_saml_message', __('Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*-_) should be present.' ,'miniorange-saml-20-single-sign-on'));
					$mo_saml_utils->mo_saml_show_error_message();
					return;
				}else {
					$email    = sanitize_email( $_POST['email'] );
					$password = stripslashes( htmlspecialchars($_POST['password'] ));
				}

				update_option( 'mo_saml_admin_email', $email );
				update_option( 'mo_saml_admin_password', $password );
				$customer    = new Customersaml();
				$content     = $customer->get_customer_key();
				if(!is_null($content)){
					$customerKey = json_decode( $content, true );
					if ( json_last_error() == JSON_ERROR_NONE ) {
						update_option( 'mo_saml_admin_customer_key', $customerKey['id'] );
						update_option( 'mo_saml_admin_api_key', $customerKey['apiKey'] );
						update_option( 'mo_saml_customer_token', $customerKey['token'] );
						$certificate = get_option( 'saml_x509_certificate' );
						if ( empty( $certificate ) ) {
							update_option( 'mo_saml_free_version', 1 );
						}
						update_option( 'mo_saml_admin_password', '' );
						update_option( 'mo_saml_message', __('Customer retrieved successfully','miniorange-saml-20-single-sign-on' ));
						update_option( 'mo_saml_registration_status', 'Existing User' );
						delete_option( 'mo_saml_verify_customer' );
						$mo_saml_utils->mo_saml_show_success_message();
						//if(is_array($response) && array_key_exists('status', $response) && $response['status'] == 'success'){
						wp_redirect( admin_url( '/admin.php?page=mo_saml_settings&tab=licensing' ), 301 );
						exit;
						//}
					} else {
						update_option( 'mo_saml_message', __('Invalid username or password. Please try again.','miniorange-saml-20-single-sign-on' ));
						$mo_saml_utils->mo_saml_show_error_message();
					}
					update_option( 'mo_saml_admin_password', '' );
				}
			}
			else if ( self::mo_check_option_admin_referer("mo_saml_contact_us_query_option") ) {

				if ( ! mo_saml_is_curl_installed() ) {
					update_option( 'mo_saml_message', __('ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Query submit failed.' ,'miniorange-saml-20-single-sign-on'));
					$mo_saml_utils->mo_saml_show_error_message();
					return;
				}

				// Contact Us query
				$email    = sanitize_email($_POST['mo_saml_contact_us_email']);
				$query    = htmlspecialchars($_POST['mo_saml_contact_us_query']);
				$phone    = htmlspecialchars($_POST['mo_saml_contact_us_phone']);


				$call_setup = false;

				if(array_key_exists('saml_setup_call',$_POST)===true){
					$time_zone = $_POST['mo_saml_setup_call_timezone'];
					$call_date = $_POST['mo_saml_setup_call_date'];
					$call_time = $_POST['mo_saml_setup_call_time'];
					$call_setup = true;
				}

				$plugin_config_json = mo_saml_miniorange_import_export(true, true);
				$customer = new CustomerSaml();

				if($call_setup == false) {
					$query = $query.'<br><br>'.'Plugin Configuration: '.$plugin_config_json;

					if ( $mo_saml_utils->mo_saml_check_empty_or_null( $email ) || $mo_saml_utils->mo_saml_check_empty_or_null( $query ) ) {
						update_option( 'mo_saml_message', __('Please fill up Email and Query fields to submit your query.','miniorange-saml-20-single-sign-on' ));
						$mo_saml_utils->mo_saml_show_error_message();
					} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						update_option( 'mo_saml_message', __('Please enter a valid email address.' ,'miniorange-saml-20-single-sign-on'));
						$mo_saml_utils->mo_saml_show_error_message();
					} else {
						$submited = $customer->submit_contact_us( $email, $phone, $query, false);
						if(!is_null($submited)){
							if ( $submited == false ) {
								update_option( 'mo_saml_message', __('Your query could not be submitted. Please try again.','miniorange-saml-20-single-sign-on' ));
								$mo_saml_utils->mo_saml_show_error_message();
							} else {
								update_option( 'mo_saml_message', __('Thanks for getting in touch! We shall get back to you shortly.' ,'miniorange-saml-20-single-sign-on'));
								$mo_saml_utils->mo_saml_show_success_message();
							}
						}
					}
				} else {
					if ( $mo_saml_utils->mo_saml_check_empty_or_null( $email )) {
						update_option('mo_saml_message', __('Please fill up Email fields to submit your query.','miniorange-saml-20-single-sign-on'));
						$mo_saml_utils->mo_saml_show_error_message();
					} else if ($mo_saml_utils->mo_saml_check_empty_or_null($call_date)  || $mo_saml_utils->mo_saml_check_empty_or_null($call_time) || $mo_saml_utils->mo_saml_check_empty_or_null($time_zone) ) {
						update_option('mo_saml_message', __('Please fill up Schedule Call Details to submit your query.','miniorange-saml-20-single-sign-on'));
						$mo_saml_utils->mo_saml_show_error_message();
					}
					else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						update_option( 'mo_saml_message', __('Please enter a valid email address.','miniorange-saml-20-single-sign-on' ));
						$mo_saml_utils->mo_saml_show_error_message();
					} else {
						$local_timezone='Asia/Kolkata';
						$call_datetime=$call_date.$call_time;
						$convert_datetime = strtotime ( $call_datetime );
						$ist_date = new DateTime(date ( 'Y-m-d H:i:s' , $convert_datetime ), new DateTimeZone($time_zone));
						$ist_date->setTimezone(new DateTimeZone($local_timezone));

						$query = $query .  '<br><br>' .'Meeting Details: '.'('.$time_zone.') '. date('d M, Y  H:i',$convert_datetime). ' [IST Time -> '. $ist_date->format('d M, Y  H:i').']'.'<br><br>'.'Plugin Config: '.$plugin_config_json;
						$response = $customer->submit_contact_us( $email, $phone, $query, true);
						if(!is_null($response)){
							if ( $response == false ) {
								update_option( 'mo_saml_message', __('Your query could not be submitted. Please try again.','miniorange-saml-20-single-sign-on' ));
								$mo_saml_utils->mo_saml_show_error_message();
							} else {
								update_option('mo_saml_message', __('Thanks for getting in touch! You will receive the call details on your email shortly.','miniorange-saml-20-single-sign-on'));
								$mo_saml_utils->mo_saml_show_success_message();
							}
						}
					}
				}
			}
			else if ( self::mo_check_option_admin_referer("mo_saml_go_back") ) {
				update_option( 'mo_saml_registration_status', '' );
				update_option( 'mo_saml_verify_customer', '' );
				delete_option( 'mo_saml_new_registration' );
				delete_option( 'mo_saml_admin_email' );
				delete_option( 'mo_saml_admin_phone' );
			}
            else if(self::mo_check_option_admin_referer('mo_saml_add_sso_button_wp_option')){
                if(mo_saml_is_sp_configured()) {
                    if(array_key_exists("mo_saml_add_sso_button_wp", $_POST)) {
                        $add_button = htmlspecialchars($_POST['mo_saml_add_sso_button_wp']);
                    } else {
                        $add_button = 'false';
                    }
                    update_option('mo_saml_add_sso_button_wp', $add_button);
                    update_option('mo_saml_message', 'Sign in option updated.');
                    $mo_saml_utils->mo_saml_show_success_message();
                } else {
                    update_option( 'mo_saml_message', 'Please complete '.addLink('Service Provider' , add_query_arg( array('tab' => 'save'), $_SERVER['REQUEST_URI'] )) . ' configuration first.');
                    $mo_saml_utils->mo_saml_show_error_message();
                }
            }
			else if ( self::mo_check_option_admin_referer("mo_saml_goto_login") ) {
				delete_option( 'mo_saml_new_registration' );
				update_option( 'mo_saml_verify_customer', 'true' );
			}
			else if ( self::mo_check_option_admin_referer("mo_saml_forgot_password_form_option") ) {
				if ( ! mo_saml_is_curl_installed() ) {
					update_option( 'mo_saml_message', __('ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Resend OTP failed.','miniorange-saml-20-single-sign-on' ));
					$mo_saml_utils->mo_saml_show_error_message();

					return;
				}

				$email = get_option( 'mo_saml_admin_email' );

				$customer = new Customersaml();
				$content  = json_decode( $customer->mo_saml_forgot_password( $email ), true );
				if(!is_null($content)){
					if ( strcasecmp( $content['status'], 'SUCCESS' ) == 0 ) {
						update_option( 'mo_saml_message', sprintf(__('Your password has been reset successfully. Please enter the new password sent to %s','miniorange-saml-20-single-sign-on') , $email) . '.' );
						$mo_saml_utils->mo_saml_show_success_message();
					} else {
						update_option( 'mo_saml_message', __('An error occurred while processing your request. Please Try again.','miniorange-saml-20-single-sign-on') );
						$mo_saml_utils->mo_saml_show_error_message();
					}
				}
			}
			/**
			 * Added for feedback mechanisms
			 */
			if(self::mo_check_option_admin_referer('mo_saml_logger')){

				if(isset($_POST['download'])){

					$logger = new MoSAMLLogger();
					$file= $logger->get_log_file_path('mo_saml');
					$log_message = mo_saml_miniorange_import_export(false,true);
					$logger->add_log(mo_saml_error_log::showMessage('PLUGIN_CONFIGURATIONS',json_decode($log_message,TRUE)), MoSAMLLogger::INFO);

					if (file_exists($file)) {
						header("Content-Disposition: attachment;");
						header('Content-type: application');
						header('Content-Disposition: attachment; filename="'.basename($file).'"');
						header('Expires: 0');
						header('Cache-Control: must-revalidate');
						header('Pragma: public');
						header('Content-Length: ' . filesize($file));
						readfile($file);
						exit;
					}else{
						update_option( 'mo_saml_message', __('Log file doesn\'t exists.','miniorange-saml-20-single-sign-on' ));
						$mo_saml_utils->mo_saml_show_error_message();
					}
				}elseif(isset($_POST['clear'])){
					$this->mo_saml_cleanup_logs();
					update_option( 'mo_saml_message', __('Successfully cleared log files.','miniorange-saml-20-single-sign-on' ));
					$mo_saml_utils->mo_saml_show_success_message();

				}
				else {
					$mo_saml_enable_logs = false;
					if(isset($_POST['mo_saml_enable_debug_logs']) and $_POST['mo_saml_enable_debug_logs'] === 'true')
						$mo_saml_enable_logs = true;

					$site_home_path = ABSPATH;
					$wp_config_path = $site_home_path . 'wp-config.php';
					if(!is_writeable($wp_config_path)){
						update_option( 'mo_saml_message', __('WP-config.php is not writable, please follow the <a href="'.admin_url().'admin.php?page=mo_saml_enable_debug_logs'.'"> manual</a> steps to enable/disable the debug logs.','miniorange-saml-20-single-sign-on' ));
						$mo_saml_utils->mo_saml_show_error_message();}
					else{
						try {
							$wp_config_editor = new WPConfigEditor($wp_config_path);  //that will be null in case wp-config.php is not writable
							if($mo_saml_enable_logs) {
								$wp_config_editor->update('MO_SAML_LOGGING', 'true'); //fatal error is call on null
								$saml_logger->add_log("MO SAML Debug Logs Enabled",MoSAMLLogger::INFO);
							}
							else {
								$saml_logger->add_log("MO SAML Debug Logs Disabled",MoSAMLLogger::INFO);
								$wp_config_editor->update('MO_SAML_LOGGING', 'false');//fatal error
							}
							$delay_for_file_write = (int) 2;
							sleep($delay_for_file_write);
							wp_redirect(saml_get_current_page_url());
							exit();
						} catch (Exception $e){
							return;
						}
					}
				}
			}

			if ( self::mo_check_option_admin_referer("mo_skip_feedback") ) {
				update_option( 'mo_saml_message', __('Plugin deactivated successfully','miniorange-saml-20-single-sign-on') );
				$mo_saml_utils->mo_saml_show_success_message();
				deactivate_plugins( __FILE__ );


			}
			if ( self::mo_check_option_admin_referer("mo_feedback") ) {
				$user = wp_get_current_user();

				$message = 'Plugin Deactivated';

				$deactivate_reason_message = array_key_exists( 'query_feedback', $_POST ) ? htmlspecialchars($_POST['query_feedback']) : false;


				$reply_required = '';
				if(isset($_POST['get_reply']))
					$reply_required = htmlspecialchars($_POST['get_reply']);
				if(empty($reply_required)){
					$reply_required = "don't reply";
					$message.='<b style="color:red";> &nbsp; [Reply :'.$reply_required.']</b>';
				}else{
					$reply_required = "yes";
					$message.='[Reply :'.$reply_required.']';
				}

				if(is_multisite())
					$multisite_enabled = 'True';
				else
					$multisite_enabled = 'False';

				$message.= ', [Multisite enabled: ' . $multisite_enabled .']';

				$message.= ', Feedback : '.$deactivate_reason_message.'';

				if (isset($_POST['rate']))
					$rate_value = htmlspecialchars($_POST['rate']);

				$message.= ', [Rating :'.$rate_value.']';

				$email = $_POST['query_mail'];
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
					$email = get_option('mo_saml_admin_email');
					if(empty($email))
						$email = $user->user_email;
				}
				$phone = get_option( 'mo_saml_admin_phone' );
				$feedback_reasons = new Customersaml();
				if(!is_null($feedback_reasons)){
					if(!mo_saml_is_curl_installed()){
						deactivate_plugins( __FILE__ );
						wp_redirect('plugins.php');
					} else {
						$submited = json_decode( $feedback_reasons->send_email_alert( $email, $phone, $message ), true );
						if ( json_last_error() == JSON_ERROR_NONE ) {
							if ( is_array( $submited ) && array_key_exists( 'status', $submited ) && $submited['status'] == 'ERROR' ) {
								update_option( 'mo_saml_message', $submited['message'] );
								$mo_saml_utils->mo_saml_show_error_message();

							}
							else {
								if ( $submited == false ) {

									update_option( 'mo_saml_message', __('Error while submitting the query.','miniorange-saml-20-single-sign-on') );
									$mo_saml_utils->mo_saml_show_error_message();
								}
							}
						}

						deactivate_plugins( __FILE__ );
						update_option( 'mo_saml_message', __('Thank you for the feedback.','miniorange-saml-20-single-sign-on' ));
						$mo_saml_utils->mo_saml_show_success_message();
					}
				}
			}
		}
	}

	function _handle_upload_metadata($saml_logger, $metadata_url = '') {
		$mo_saml_utils = new Utilities();
		if ( isset( $_FILES['metadata_file'] ) || isset( $_POST['metadata_url'] ) || !empty($metadata_url)) {
			if ( ! empty( $_FILES['metadata_file']['tmp_name'] ) ) {
				$file = @file_get_contents( $_FILES['metadata_file']['tmp_name'] );
				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_SUCCESS'),MoSAMLLogger::DEBUG);
			} else {
				if(!mo_saml_is_curl_installed()){
					update_option( 'mo_saml_message', __('PHP cURL extension is not installed or disabled. Cannot fetch metadata from URL.','miniorange-saml-20-single-sign-on' ));
					$mo_saml_utils->mo_saml_show_error_message();
					$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_CURL_ERROR'), MoSAMLLogger::ERROR);
					return;
				}
				if(isset( $_POST['metadata_url'] ))
					$url = filter_var( $_POST['metadata_url'], FILTER_SANITIZE_URL );
				else
					$url = $metadata_url;

				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_URL',array('url'=>$url)), MoSAMLLogger::INFO);

				$response = Utilities::mo_saml_wp_remote_get($url, array('sslverify'=>false));
				if(!is_null($response)){
					$file = $response['body'];
					$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_SUCCESS_FROM_URL'), MoSAMLLogger::INFO);

				}
				else{
					$file = null;
					$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_ERROR_FROM_URL'), MoSAMLLogger::ERROR);
				}

			}
			if(!is_null($file))
				$this->upload_metadata( $file, $saml_logger, $metadata_url );
		}
	}

	function upload_metadata( $file, $saml_logger, $metadata_url='' ) {
		$mo_saml_utils = new Utilities();
		$old_error_handler = set_error_handler( array( $this, 'handleXmlError' ) );
		$document          = new DOMDocument();
		$document->loadXML( $file );
		restore_error_handler();
		$first_child = $document->firstChild;
		if ( ! empty( $first_child ) ) {
			$metadata           = new IDPMetadataReader( $document );
			$identity_providers = $metadata->getIdentityProviders();
			if ( ! preg_match( "/^\w*$/", $_POST['saml_identity_metadata_provider'] ) ) {
				update_option( 'mo_saml_message', __('Please match the requested format for Identity Provider Name. Only alphabets, numbers and underscore is allowed.','miniorange-saml-20-single-sign-on') );
				$mo_saml_utils->mo_saml_show_error_message();
				$saml_logger->add_log(mo_saml_error_log::showMessage('INVAILD_IDP_NAME_FORMAT',array('saml_identity_name'=>$metadata_url)), MoSAMLLogger::ERROR);

				return;
			}
			if ( empty( $identity_providers ) && !empty( $_FILES['metadata_file']['tmp_name']) ) {
				update_option( 'mo_saml_message', __('Please provide a valid metadata file.' ,'miniorange-saml-20-single-sign-on'));
				$mo_saml_utils->mo_saml_show_error_message();
				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_INVALID_FILE'), MoSAMLLogger::ERROR);

				return;
			}
			if ( empty( $identity_providers ) && !empty($_POST['metadata_url']) ) {
				update_option( 'mo_saml_message', __('Please provide a valid metadata URL.','miniorange-saml-20-single-sign-on') );
				$mo_saml_utils->mo_saml_show_error_message();
				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_INVALID_URL'), MoSAMLLogger::ERROR);


				return;
			}
			if(empty($identity_providers) && !empty($metadata_url)){
				update_option( 'mo_saml_message', __('Unable to fetch Metadata. Please check your IDP configuration again.','miniorange-saml-20-single-sign-on' ));
				$mo_saml_utils->mo_saml_show_error_message();
				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_INVALID_CONFIGURATION'), MoSAMLLogger::ERROR);


				return;
			}
			foreach ( $identity_providers as $key => $idp ) {
				//$saml_identity_name = preg_match("/^[a-zA-Z0-9-\._ ]+/", $idp->getIdpName()) ? $idp->getIdpName() : "";
				$saml_identity_name = htmlspecialchars($_POST['saml_identity_metadata_provider']);

				$saml_login_url = $idp->getLoginURL( 'HTTP-Redirect' );

				$saml_issuer           = $idp->getEntityID();
				$saml_x509_certificate = $idp->getSigningCertificate();

				update_option( 'saml_identity_name', $saml_identity_name );

				update_option( 'saml_login_url', $saml_login_url );


				update_option( 'saml_issuer', $saml_issuer );
				//certs already sanitized in Metadata Reader
				$saml_x509_certificate = maybe_serialize($saml_x509_certificate);
				update_option( 'saml_x509_certificate',  $saml_x509_certificate  );

				$log_message = [ 'saml_identity_name' =>$saml_identity_name,
				                 'saml_login_url' => $saml_login_url,
				                 'saml_issuer' => $saml_issuer ,
				                 'saml_x509_certificate' =>  $saml_x509_certificate];
				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_CONFIGURATION_SAVED',$log_message), MoSAMLLogger::DEBUG);

				break;
			}
			update_option( 'mo_saml_message', __('Identity Provider details saved successfully.','miniorange-saml-20-single-sign-on' ));
			$mo_saml_utils->mo_saml_show_success_message();
		} else {
			if(!empty( $_FILES['metadata_file']['tmp_name']))
			{
				update_option( 'mo_saml_message', __('Please provide a valid metadata file.','miniorange-saml-20-single-sign-on' ));
				$mo_saml_utils->mo_saml_show_error_message();
				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_INVALID_FILE'), MoSAMLLogger::ERROR);
			}
			if(!empty($_POST['metadata_url']))
			{
				update_option( 'mo_saml_message', __('Please provide a valid metadata URL.','miniorange-saml-20-single-sign-on' ));
				$mo_saml_utils->mo_saml_show_error_message();
				$saml_logger->add_log(mo_saml_error_log::showMessage('UPLOAD_METADATA_INVALID_URL'), MoSAMLLogger::ERROR);
			}
		}
	}

	function get_current_customer() {
		$customer    = new CustomerSaml();
		$content     = $customer->get_customer_key();
		$mo_saml_utils = new Utilities();
		if(!is_null($content)){
			$customerKey = json_decode( $content, true );

			$response = array();
			if ( json_last_error() == JSON_ERROR_NONE ) {
				update_option( 'mo_saml_admin_customer_key', $customerKey['id'] );
				update_option( 'mo_saml_admin_api_key', $customerKey['apiKey'] );
				update_option( 'mo_saml_customer_token', $customerKey['token'] );
				update_option( 'mo_saml_admin_password', '' );
				$certificate = get_option( 'saml_x509_certificate' );
				if ( empty( $certificate ) ) {
					update_option( 'mo_saml_free_version', 1 );
				}

				delete_option( 'mo_saml_verify_customer' );
				delete_option( 'mo_saml_new_registration' );
				$response['status'] = "success";
				return $response;
			} else {

				update_option( 'mo_saml_message', __('You already have an account with miniOrange. Please enter a valid password.','miniorange-saml-20-single-sign-on' ));
				$mo_saml_utils->mo_saml_show_error_message();
				//update_option( 'mo_saml_verify_customer', 'true' );
				//delete_option( 'mo_saml_new_registration' );
				$response['status'] = "error";
				return $response;
			}
		}
	}

	function create_customer() {
		$customer    = new CustomerSaml();
		$customerKey = json_decode( $customer->create_customer(), true );
		if(!is_null($customerKey)){
			$response = array();
			//print_r($customerKey);
			if ( strcasecmp( $customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS' ) == 0 ) {
				$api_response = $this->get_current_customer();
				//print_r($api_response);exit;
				if($api_response){
					$response['status'] = "success";
				}
				else
					$response['status'] = "error";

			} else if ( strcasecmp( $customerKey['status'], 'SUCCESS' ) == 0 ) {
				update_option( 'mo_saml_admin_customer_key', $customerKey['id'] );
				update_option( 'mo_saml_admin_api_key', $customerKey['apiKey'] );
				update_option( 'mo_saml_customer_token', $customerKey['token'] );
				update_option( 'mo_saml_free_version', 1 );
				update_option( 'mo_saml_admin_password', '' );
				update_option( 'mo_saml_message', __('Thank you for registering with miniOrange.','miniorange-saml-20-single-sign-on') );
				update_option( 'mo_saml_registration_status', '' );
				delete_option( 'mo_saml_verify_customer' );
				delete_option( 'mo_saml_new_registration' );
				$response['status']="success";
				return $response;
			}

			update_option( 'mo_saml_admin_password', '' );
			return $response;
		}
	}

	function miniorange_sso_menu() {
		//Add miniOrange SAML SSO
		$logger = new MoSAMLLogger();
		$slug = 'mo_saml_settings';
		add_menu_page( 'MO SAML Settings ' . __( 'Configure SAML Identity Provider for SSO','miniorange-saml-20-single-sign-on'), 'miniOrange SAML 2.0 SSO', 'administrator', $slug, array(
			$this,
			'mo_login_widget_saml_options'
		), plugin_dir_url( __FILE__ ) . 'images/miniorange.png' );
		add_submenu_page( $slug	,'miniOrange SAML 2.0 SSO'	,__('Plugin Configuration','miniorange-saml-20-single-sign-on'),'manage_options','mo_saml_settings'
			, array( $this, 'mo_login_widget_saml_options'));
		add_submenu_page( $slug	,'miniOrange SAML 2.0 SSO'	,__('<div style="color:orange"><img src="'. plugin_dir_url(__FILE__) . 'images/premium_plans_icon.png" style="height:10px;width:12px">  Premium Plans</div>','miniorange-saml-20-single-sign-on'),'manage_options','mo_saml_licensing'
			, array( $this, 'mo_login_widget_saml_options'));
		add_submenu_page( $slug	,'miniOrange SAML 2.0 SSO'	,__('<div id="mo_saml_addons_submenu">Add-Ons</div>','miniorange-saml-20-single-sign-on'),'manage_options','mo_saml_settings&tab=addons'
			, array( $this, 'mo_login_widget_saml_options'));
		add_submenu_page( $slug	,'miniOrange SAML 2.0 SSO'	,__('<div id="mo_saml_troubleshoot">Troubleshoot</div>','miniorange-saml-20-single-sign-on'),'manage_options','mo_saml_enable_debug_logs'
			, array( 'MoSAMLLogger', 'mo_saml_log_page'));

	}

	function mo_saml_authenticate() {
		$redirect_to = '';
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = htmlentities( $_REQUEST['redirect_to'] );
		}

		if ( is_user_logged_in() ) {
			$this->mo_saml_login_redirect($redirect_to);
		}
	}

	function mo_saml_login_redirect($redirect_to){
		$is_admin_url = false;

		if(strcmp(admin_url(),$redirect_to) == 0 || strcmp(wp_login_url(),$redirect_to) == 0 ){
			$is_admin_url = true;
		}

		if ( ! empty( $redirect_to ) && !$is_admin_url ) {
			header( 'Location: ' . $redirect_to );
		} else {
			header( 'Location: ' . site_url() );
		}
		exit();
	}


	function handleXmlError( $errno, $errstr, $errfile, $errline ) {
		if ( $errno == E_WARNING && ( substr_count( $errstr, "DOMDocument::loadXML()" ) > 0 ) ) {
			return;
		} else {
			return false;
		}
	}

	function mo_saml_plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=mo_saml_settings' ) ) . '">' . __( 'Settings','miniorange-saml-20-single-sign-on' ) . '</a>'
		), $links );
		return $links;
	}

	function checkPasswordpattern($password){
		$pattern = '/^[(\w)*(\!\@\#\$\%\^\&\*\.\-\_)*]+$/';

		return !preg_match($pattern,$password);
	}
}
new saml_mo_login;