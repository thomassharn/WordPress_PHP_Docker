<?php

function mo_saml_display_plugin_dependency_warning()
{
    if (!mo_saml_is_curl_installed()) {
?>
        <p><span style="color: #FF0000; ">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP
                    cURL extension</a> is not installed or disabled)</span></p>
    <?php
    }

    if (!mo_saml_is_openssl_installed()) {
    ?>
        <p><span style="color: #FF0000; ">(Warning: <a href="http://php.net/manual/en/openssl.installation.php" target="_blank">PHP
                    openssl extension</a> is not installed or disabled)</span></p>
    <?php
    }

    if (!mo_saml_is_dom_installed()) {
    ?>
        <p><span style="color: #FF0000; ">(Warning: PHP
                dom extension is not installed or disabled)</span></p>
    <?php
    }
}

function mo_saml_display_welcome_page()
{
    ?>

    <input type="hidden" value="<?php echo get_option("mo_is_new_user"); ?>" id="mo_modal_value">
    <input type="hidden" value="<?php echo get_option("saml_issuer"); ?>" id="sp_configured_welcome_check">
    <div id="getting-started" class="modal" style="display: none">

        <div class="modal-dialog modal-dialog-centered" role="document">

            <div class="modal-content mt-3">
                <span class="pt-2" style="cursor: pointer" onclick="skip_plugin_tour();"><i class="dashicons dashicons-dismiss float-right"></i></span>
                <div class="modal-header d-block text-center">
                    <h2 class="h1 text-info"><?php esc_html_e('Let\'s get started!', 'miniorange-saml-20-single-sign-on'); ?></h2>
                    <div class="bg-cstm p-3 mt-3 rounded">
                        <p class="h6"><?php _e('Hey, Thank you for installing <b style="color: #E85700">miniOrange SSO using SAML 2.0 plugin', 'miniorange-saml-20-single-sign-on'); ?></b>.</p>
                        <p class="h6"><?php _e('We support all SAML 2.0 compliant Identity Providers. ', 'miniorange-saml-20-single-sign-on');

                                        _e('Please find some of the well-known <b>IdP configuration guides</b> below.', 'miniorange-saml-20-single-sign-on');
                                        _e(' If you do not find your IDP guide here, do not worry! mail us at <a href="mailto:info@xecurify.com">info@xecurify.com</a>', 'miniorange-saml-20-single-sign-on'); ?> </p>
                        <p class="h6"><?php _e('Make sure to check out the list of supported', 'miniorange-saml-20-single-sign-on'); ?> <a onclick="skip_plugin_tour();" href="<?php echo add_query_arg(array('tab' => 'addons'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('add-ons', 'miniorange-saml-20-single-sign-on'); ?></a> <?php _e('to increase the functionality of your WordPress site.', 'miniorange-saml-20-single-sign-on'); ?></p>

                    </div>
                </div>

                <div class="modal-body">
                    <?php
                    $index = 0;
                    foreach (mo_saml_options_plugin_idp::$IDP_GUIDES as $key => $value) {

                        $url_string = 'https://plugins.miniorange.com/' . trim($value[1]);

                        if ($index % 5 === 0) { ?>
                            <div class="idp-guides-btns">
                            <?php } ?>
                            <button class="guide-btn" onclick="window.open('<?php echo $url_string ?>','_blank')"><img class="idp-guides-logo <?php echo $key ?>" src="<?php echo plugin_dir_url(__FILE__) . '../images/idp-guides-logos/' . $value[0] . '.png'; ?>" /><?php echo $key ?></button>
                        <?php
                        if ($index % 5 === 4) {
                            echo '</div>';
                            $index = -1;
                        }
                        $index++;
                    }

                        ?>
                            </div>

                </div>
                <div class="modal-footer d-block" style="position: sticky;">
                    <button type="button" class="btn-cstm rounded mt-3" id="skip-plugin-tour" onclick="skip_plugin_tour()"><?php _e('Configure Your IDP Now', 'miniorange-saml-20-single-sign-on'); ?></button>

                </div>
            </div>

        </div>

    </div>
    <script>
        document.onkeydown = function(evt) {
            evt = evt || window.event;
            if (evt.keyCode == 27) {
                skip_plugin_tour();
            }
        };
    </script>


<?php
}

function mo_saml_display_plugin_header($active_tab)
{
?>
    <!-- First Slot (Buttons) -->
    <div class="wrap shadow-cstm p-3 mr-0 mt-0 mo-saml-margin-left">
        <?php if ($active_tab == 'licensing' || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_licensing')) { ?>
            <h3 class="text-center"><?php _e('miniOrange SSO using SAML 2.0', 'miniorange-saml-20-single-sign-on'); ?></h3>
            <div class="float-left"><a class="bg-light text-dark rounded h6 p-2" href="<?php echo mo_saml_add_query_arg(array('tab' => 'save'), htmlentities($_SERVER['REQUEST_URI'])); ?>"> Back To Plugin Configuration</a></div>
            <br />
            <div class="text-center text-danger"><?php _e('You are currently on the Free version of the plugin', 'miniorange-saml-20-single-sign-on'); ?></div>
        <?php } else {
            update_option('mo_license_plan_from_feedback', '');
            update_option('mo_saml_license_message', '');
        ?>

            <div class="row align-items-top">
                <div class="col-md-5 h3 pl-4">
                    <?php _e('miniOrange SSO using SAML 2.0', 'miniorange-saml-20-single-sign-on'); ?>
                </div>
                <div class="col-md-3 text-center">
                    <a id="license_upgrade" class="text-white pl-4 pr-4 pt-2 pb-2 btn-prem prem-btn-cstm" href="<?php echo mo_saml_add_query_arg(array('tab' => 'licensing'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Premium Plans | Upgrade Now', 'miniorange-saml-20-single-sign-on'); ?></a>
                </div>
                <div class="col-md-4 text-right d-flex align-items-center justify-content-end">
                    <a class="pb-3 pt-3 pl-5 pr-5 pop-up-btns" target="_blank" href="https://forum.miniorange.com/">Forum</a>
                    <a class="mr-2 pb-3 pt-3 pl-5 pr-5 pop-up-btns" href="?page=mo_saml_enable_debug_logs&tab=debug-logs">Troubleshoot</a>                    
                </div>
            </div>

        <?php } ?>

    </div>

<?php
}

function mo_saml_display_plugin_tabs($active_tab)
{
?>
    <div class="bg-main-cstm pb-4 mo-saml-margin-left" id="container">
        <span id="mo-saml-message"></span>

        <?php if ($active_tab != 'licensing' && !(isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_licensing')) { ?>
            <div class="d-flex text-center pt-3 border-bottom mo_saml_padding_left_2">
                <a id="sp-setup-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'save' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'save'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Service Provider Setup', 'miniorange-saml-20-single-sign-on'); ?></a>
                <a id="sp-meta-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'config' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'config'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Service Provider Metadata', 'miniorange-saml-20-single-sign-on'); ?></a>
                <a id="attr-role-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'opt' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'opt'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Attribute/Role Mapping', 'miniorange-saml-20-single-sign-on'); ?></a>

                <a id="redir-sso-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'general' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'general'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Redirection & SSO Links', 'miniorange-saml-20-single-sign-on'); ?></a>
                <a id="addon-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'addons' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'addons'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Add-Ons', 'miniorange-saml-20-single-sign-on'); ?></a>
                <a id="demo-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'demo' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'demo'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Demo Request', 'miniorange-saml-20-single-sign-on'); ?></a>
                <a id="acc-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'account-setup' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'account-setup'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Account Setup', 'miniorange-saml-20-single-sign-on'); ?></a>
            </div>
            <?php
            if ($active_tab == 'save') {
                mo_saml_apps_config_saml();
            } else if ($active_tab == 'opt') {
                mo_saml_save_optional_config();
            } else if ($active_tab == 'config') {
                mo_saml_configuration_steps();
            } else if ($active_tab == 'general') {
                mo_saml_general_login_page();
            } else if ($active_tab == 'addons') {
                mo_saml_show_addons_page();
            } else if ($active_tab == 'demo') {
                mo_saml_display_demo_request();
            } else if ($active_tab == 'account-setup') {
                if (mo_saml_is_customer_registered_saml(false)) {
                    mo_saml_show_customer_details();
                } else {
                    mo_saml_show_new_registration_page_saml();
                }
            } else {
                mo_saml_apps_config_saml();
            }
            ?>
            <a class="contact-us-cstm d-none"><span class="d-flex justify-content-center align-items-center pt-3 text-white"><svg width="16" height="16" fill="currentColor" class="mt-1" viewBox="0 0 16 16">
                        <path d="M8 1a5 5 0 0 0-5 5v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a6 6 0 1 1 12 0v6a2.5 2.5 0 0 1-2.5 2.5H9.366a1 1 0 0 1-.866.5h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 .866.5H11.5A1.5 1.5 0 0 0 13 12h-1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1V6a5 5 0 0 0-5-5z" />
                    </svg> &nbsp;&nbsp;miniOrange Support</span></a>

        <?php } else if ($active_tab == 'licensing' ||     (isset($_REQUEST['page']) && $_REQUEST['page'] == 'mo_saml_licensing')) {
            mo_saml_show_licensing_page();
        } ?>
    </div>
<?php
}

function mo_saml_troubleshoot_card()
{ ?>
 <div class="bg-white text-center shadow-cstm rounded contact-form-cstm mt-4 p-4" >
  <div class="mo-saml-call-setup mt-4 p-3">
    <h6>Facing issues? Check out the Troubleshooting options available in the plugin</h6>
    <hr />
    <div class="row align-items-center mt-3 justify-content-center">
        <div class="col-md-3 pl-0">
            <a href="?page=mo_saml_enable_debug_logs&tab=debug-logs" class="mo-saml-bs-btn btn-cstm text-white mt-1 w-9">Troubleshoot</a>
        </div>
    </div>
 </div>
 </div>
  <?php
}

function mo_saml_display_keep_settings_intact_section()
{
?>
    <div class="bg-white text-center shadow-cstm rounded contact-form-cstm mt-4 p-4" id="mo_saml_keep_configuration_intact">
        <div class="mo-saml-call-setup p-3">
            <h6 class="text-center">Keep configuration Intact</h6>
            <form name="f" method="post" action="" id="settings_intact">
                <?php wp_nonce_field('mo_saml_keep_settings_on_deletion'); ?>
                <input type="hidden" name="option" value="mo_saml_keep_settings_on_deletion" />
                <hr>
                <div class="row align-items-top mt-3">
                    <div class="col-md-9">
                        <h6 class="text-secondary">Enabling this would keep your settings intact when plugin is uninstalled</h6>
                    </div>
                    <div class="col-md-3 pl-0">
                        <input type="checkbox" id="mo-saml-switch-keep-config" name="mo_saml_keep_settings_intact" class="mo-saml-switch" <?php checked(get_option('mo_saml_keep_settings_on_deletion') == 'true'); ?> onchange="document.getElementById('settings_intact').submit();">
                        <label class="mo-saml-switch-label" for="mo-saml-switch-keep-config"></label>
                    </div>
                </div>
            </form>
        </div>
        <blockquote class="mt-3">Please enable this option when you are updating to a Premium version</blockquote>
    </div>
<?php
}

function mo_saml_display_suggested_idp_integration()
{
?>
    <div class="mo-saml-card-glass mt-4" id="mo-saml-ads-text">
        <div class="mo-saml-ads-text">
            <h5 class="text-center" id="mo-saml-ads-head">Wait! You have more to explore</h5>
            <hr />
            <ul class="pl-1">
                <p id="mo-saml-ads-cards-text"></p>
                <a target="_blank" href="" class="text-warning" id="ads-text-link">Azure AD / Office 365 Sync</a>
                <a target="_blank" href="" class="text-warning float-right" id="ads-knw-more-link">Azure AD / Office 365 Sync</a>
            </ul>
        </div>
    </div>
    <?php

}

function mo_saml_display_suggested_add_ons()
{
    $suggested_addons = mo_saml_options_suggested_add_ons::$suggested_addons;

    foreach ($suggested_addons as $addon) {
    ?>

        <div class="mo-saml-card-glass mt-4">
            <div class="mo-saml-ads-text">
                <h5 class="text-center"><?php echo $addon['title']; ?></h5>
                <hr />
                <ul class="pl-1">
                    <p><?php echo $addon['text']; ?></p>
                    <a target="_blank" href="<?php echo $addon['link']; ?>" class="text-warning">Download</a>
                    <a target="_blank" href="<?php echo $addon['knw-link']; ?>" class="text-warning float-right">Know More</a>
                </ul>
            </div>
        </div>

<?php
    }
}
