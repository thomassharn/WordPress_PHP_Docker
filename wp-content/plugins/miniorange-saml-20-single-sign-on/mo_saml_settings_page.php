<?php
include_once 'Import-export.php';
require_once 'mo_saml_logger.php';

foreach (glob(plugin_dir_path(__FILE__).'views'.DIRECTORY_SEPARATOR.'*.php') as $filename)
{
    include_once $filename;
}

function mo_saml_register_saml_sso() {
    if ( isset( $_GET['tab'] ) ) {
        $active_tab = $_GET['tab'];
        if($active_tab== 'addons')
        {
            echo "<script type='text/javascript'>
            highlightAddonSubmenu();
            </script>";

        }

    } else if ( mo_saml_is_customer_registered_saml() ) {
        $active_tab = 'save';
    } else {
        $active_tab = 'login';
    }
    ?>
    <?php

    mo_saml_display_plugin_dependency_warning();

    ?>
    <div id="mo_saml_settings" >
        <?php
            mo_saml_display_welcome_page();

        mo_saml_display_plugin_header($active_tab);
        ?>

    </div>

    <?php mo_saml_display_plugin_tabs($active_tab);

}

function mo_saml_is_curl_installed() {
    if ( in_array( 'curl', get_loaded_extensions() ) ) {
        return 1;
    } else {
        return 0;
    }
}

function mo_saml_is_openssl_installed() {

    if ( in_array( 'openssl', get_loaded_extensions() ) ) {
        return 1;
    } else {
        return 0;
    }
}

function mo_saml_is_dom_installed(){

    if ( in_array( 'dom', get_loaded_extensions() ) ) {
        return 1;
    } else {
        return 0;
    }
}

function mo_saml_is_iconv_installed(){

    if ( in_array( 'iconv', get_loaded_extensions() ) ) {
        return 1;
    } else {
        return 0;
    }
}

function mo_saml_get_attribute_mapping_url(){

    return add_query_arg( array('tab' => 'opt'), $_SERVER['REQUEST_URI'] );
}

function mo_saml_get_service_provider_url(){

        return add_query_arg( array('tab' => 'save'), $_SERVER['REQUEST_URI'] );

}
function mo_saml_get_redirection_sso_url(){
    return add_query_arg( array('tab' => 'general'), $_SERVER['REQUEST_URI'] );
}

function mo_saml_get_debug_log_url(){
    return add_query_arg( array('page' => 'mo_saml_enable_debug_logs'), $_SERVER['REQUEST_URI'] );
}

function mo_saml_get_test_url() {

        $url = site_url() . '/?option=testConfig';


    return $url;
}

function mo_saml_is_customer_registered_saml($check_guest=true) {

    $email       = get_option( 'mo_saml_admin_email' );
    $customerKey = get_option( 'mo_saml_admin_customer_key' );

    if(mo_saml_is_guest_enabled() && $check_guest)
        return 1;
    if ( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
        return 0;
    } else {
        return 1;
    }
}

function mo_saml_is_guest_enabled(){
    $guest_enabled = get_option('mo_saml_guest_enabled');

    return $guest_enabled;
}

function mo_saml_is_sp_configured() {
    $saml_login_url = get_option( 'saml_login_url' );


    if ( empty( $saml_login_url ) ) {
        return 0;
    } else {
        return 1;
    }
}

function mo_saml_display_test_config_error_page($error_code, $error_cause, $error_message, $statusmessage='') {
    echo '<div style="font-family:Calibri;padding:0 3%;">';
    echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">' . __('ERROR: ' . $error_code, 'miniorange-saml-20-single-sign-on') . '</div>
                <div style="color: #a94442;font-size:14pt; margin-bottom:20px;text-align: justify"><p><strong>' . __('Error', 'miniorange-saml-20-single-sign-on') . '</strong>: ' . $error_cause . ' </p>
                
                <p><strong>' . __('Possible Cause: ', 'miniorange-saml-20-single-sign-on') . '</strong>' . $error_message . ' </p>';
    if (!empty($statusmessage))
        echo '<p><strong>Status Message in the SAML Response:</strong> <br/>' . $statusmessage . '</p><br>';
    if($error_code == 'WPSAMLERR010' || $error_code == 'WPSAMLERR004') {
        $option_id = '';
        switch($error_code){
            case 'WPSAMLERR004':
                $option_id = 'mo_fix_certificate';
                break;
            case 'WPSAMLERR010':
                $option_id = 'mo_fix_entity_id';
                break;
        }
        echo '<div>
			    <ol style="text-align: center">
                    <form method="post" action="">';
        wp_nonce_field($option_id);
        echo '<input type="hidden" name="option" value="'.$option_id.'" />
                <input type="submit" class="miniorange-button" style="width: 15%" value="' . __('Fix Issue', 'miniorange-saml-20-single-sign-on') . '">
                <br>
                </ol>      
            </form>      
          </div>';
    }
    echo '</div>
        </div>';
}

function mo_saml_download_logs($error_msg,$cause_msg) {

    echo '<div style="font-family:Calibri;padding:0 3%;">';
    echo '<hr class="header"/>';
    echo '          <p style="font-size: larger ;color: #a94442     ">' . __('You can check out the Troubleshooting section provided in the plugin to resolve the issue.<br> If the problem persists, mail us at <a href="mailto:samlsupport@xecurify.com">samlsupport@xecurify.com</a>','miniorange-saml-20-single-sign-on') . '.</p>
                   
                    </div>
                    <div style="margin:3%;display:block;text-align:center;">
                   
				<input class="miniorange-button" style="margin-left:60px" type="button" value="' . __('Close','miniorange-saml-20-single-sign-on') . '" onclick="self.close()"></form>            
                </div>    ';
    echo '&nbsp;&nbsp;';

    $samlResponse = htmlspecialchars($_POST['SAMLResponse']);
    update_option('MO_SAML_RESPONSE',$samlResponse);
    $error_array  = array("Error"=>$error_msg,"Cause"=>$cause_msg);
    update_option('MO_SAML_TEST',$error_array);
    update_option('MO_SAML_TEST_STATUS',0);
    ?>
    <style>
    .miniorange-button {
    padding:1%;
    background: linear-gradient(0deg,rgb(14 42 71) 0,rgb(26 69 138) 100%)!important;
    cursor: pointer;font-size:15px;
    border-width: 1px;border-style: solid;
    border-radius: 3px;white-space: nowrap;
    box-sizing: border-box;
    box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;
    margin: 22px;
    }
</style>
    <?php

    exit();

}

function mo_saml_add_query_arg($query_arg, $url){
    if(strpos($url, 'mo_saml_licensing') !== false){
        $url = str_replace('mo_saml_licensing', 'mo_saml_settings', $url);
    }
    else if (strpos($url, 'mo_saml_enable_debug_logs') !== false){
	    $url = str_replace('mo_saml_enable_debug_logs', 'mo_saml_settings', $url);
    }
    $url = add_query_arg($query_arg, $url);
    return $url;
}

function mo_saml_miniorange_generate_metadata($download=false) {

    $sp_base_url = get_option( 'mo_saml_sp_base_url' );
    if ( empty( $sp_base_url ) ) {
        $sp_base_url = site_url();
    }
    if ( substr( $sp_base_url, - 1 ) == '/' ) {
        $sp_base_url = substr( $sp_base_url, 0, - 1 );
    }
    $sp_entity_id = get_option( 'mo_saml_sp_entity_id' );
    if ( empty( $sp_entity_id ) ) {
        $sp_entity_id = $sp_base_url . '/wp-content/plugins/miniorange-saml-20-single-sign-on/';
    }

    $entity_id   = $sp_entity_id;
    $acs_url     = $sp_base_url . '/';

    if(ob_get_contents())
        ob_clean();
    header( 'Content-Type: text/xml' );
    if($download)
            header('Content-Disposition: attachment; filename="Metadata.xml"');
    echo '<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2022-10-28T23:59:59Z" cacheDuration="PT1446808792S" entityID="' . $entity_id . '">
  <md:SPSSODescriptor AuthnRequestsSigned="false" WantAssertionsSigned="true" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
    <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="' . $acs_url . '" index="1"/>
  </md:SPSSODescriptor>
  <md:Organization>
    <md:OrganizationName xml:lang="en-US">miniOrange</md:OrganizationName>
    <md:OrganizationDisplayName xml:lang="en-US">miniOrange</md:OrganizationDisplayName>
    <md:OrganizationURL xml:lang="en-US">http://miniorange.com</md:OrganizationURL>
  </md:Organization>
  <md:ContactPerson contactType="technical">
    <md:GivenName>miniOrange</md:GivenName>
    <md:EmailAddress>info@xecurify.com</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="support">
    <md:GivenName>miniOrange</md:GivenName> 
    <md:EmailAddress>info@xecurify.com</md:EmailAddress>
  </md:ContactPerson>
</md:EntityDescriptor>';
    exit;

}
?>