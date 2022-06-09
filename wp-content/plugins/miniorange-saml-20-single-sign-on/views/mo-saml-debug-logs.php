<?php
/**
 * Used to show the UI part of the log feature to user screen.
 */
function mo_saml_display_log_page()
{

    $debugging_enabled = MoSAMLLogger::is_debugging_enabled();
    mo_saml_display_plugin_header('debug');
?>  
    <?php
    $active_tab = mo_display_troubleshoot_headers_page();
    mo_display_tabs_troubleshoot_page($active_tab);
    if($active_tab == 'debug-logs'){
    ?>
    <div class="bg-main-cstm mo-saml-margin-left pb-5">
        <div class="row container-fluid">
            <div class="col-md-8 mt-4 ml-4">
                <div class="p-4 shadow-cstm bg-white rounded">
                    <form action="" method="post" id="mo_saml_logger">
                        <?php wp_nonce_field('mo_saml_logger'); ?>
                        <input type="hidden" name="option" value="mo_saml_logger" />
                        <div class="row">
                            <div class="col-md-6">
                                <h4>SAML Debug Tools
                                 </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="<?php echo mo_saml_add_query_arg(array('tab' => 'save'), htmlentities($_SERVER['REQUEST_URI'])); ?>" class="btn btn-cstm ml-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
                                    </svg>&nbsp; Back To Plugin Configuration</a>
                            </div>
                        </div>
                        <div class="form-head"></div>
                        <h5 class="mt-4">If you are facing any issues with the SSO, please follow these steps for easier debugging</h5>

                        <h6 class="mt-4"><b>Step 1: </b>Enable the Debug Logs option below and reproduce the issue</h6>
                        <div class="row align-items-top mt-4">
                            <div class="col-md-7">
                                <h6 class="text-secondary">Enable miniOrange SAML Debug Logs</h6>
                            </div>
                            <div class="col-md-3 pl-0">
                                <input type="checkbox" id="mo_saml_enable_debug_logs" name="mo_saml_enable_debug_logs" class="mo-saml-switch" value="true" onchange="submit();" <?php if ($debugging_enabled) echo ' checked '; ?> />
                                <label class="mo-saml-switch-label" for="mo_saml_enable_debug_logs"></label>
                            </div>
                        </div>

                        <div class="text-center">
                            <input type="submit" class="btn btn-cstm mt-4" name="clear" value="Clear Log Files" <?php if (!$debugging_enabled) echo ' title="Enable debug logs first" disabled '; ?>>
                        </div>
                        <div class="call-setup-div mt-4">
                            <h6 class="call-setup-heading"><strong>
                                    <span class="text-danger">Note: </span><u>If your wp-config.php is not writable</u>, follow the steps below to Enable debug logs Manually
                                </strong></h6>

                            <h6 class="mt-3">
                                Copy this code <code>define('MO_SAML_LOGGING', true);</code>
                                and paste it in the <a href="https://wordpress.org/support/article/editing-wp-config-php/">wp-config.php</a> file before the line
                                <br> <code>/* That's all, stop editing! Happy publishing. */</code> to enable the miniOrange SAML logs.
                            </h6>

                        </div>

                        <h6 class="mt-4"><b>Step 2: </b> Download the Debug Log File and Plugin Configurations</h6>

                        <div class="text-center">
                            <input type="submit" class="btn btn-cstm mt-4" name="download" value="Download Debug Logs" <?php
                                                                                                                        if (!$debugging_enabled)
                                                                                                                            echo ' title="Enable debug logs first" disabled ';
                                                                                                                        ?>>
                        </div>
                    </form>
                    <h6 class="mt-4">Send this file to us at <a class="text-danger" href="mailto:samlsupport@xecurify.com">samlsupport@xecurify.com</a></h6>
                </div>
            </div>
            <?php mo_saml_display_support_form(); ?>
        </div>
     </div>
      <?php
     }
     else if($active_tab == 'error-codes'){
           error_codes();
        ?>
    </div>
   <?php
  } else {
      ?>
       <div class="d-flex text-center pt-3 border-bottom justify-content-center">
           <a href="">FAQs</a>
       </div>
     <?php 
  }
 
}

function mo_display_troubleshoot_headers_page(){
    if ( isset( $_GET['tab'] ) ) 
        $active_tab = $_GET['tab'];
    else
        $active_tab = 'debug-logs';
    return $active_tab;
}

function mo_display_tabs_troubleshoot_page($active_tab){
    ?>
    <div class="bg-main-cstm pb-4 mo-saml-margin-left" id="container">
        <div class="d-flex text-center pt-3 border-bottom padding-left-10px" style="padding-left: 2.4rem;">

            <a id="sp-setup-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'debug-logs' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'debug-logs'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Debug Tools', 'miniorange-saml-20-single-sign-on'); ?></a>
            <a id="sp-setup-tab" class="mo-saml-nav-tab-cstm <?php echo $active_tab == 'error-codes' ? 'mo-saml-nav-tab-active' : ''; ?>" href="<?php echo add_query_arg(array('tab' => 'error-codes'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Error Codes', 'miniorange-saml-20-single-sign-on'); ?></a>        
            <a id="sp-setup-tab" class="mo-saml-nav-tab-cstm" target="_blank" href="https://faq.miniorange.com/kb/saml-single-sign-on/">FAQs</a>      
        </div>
    </div>
    <?php
}