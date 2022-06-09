<?php
function mo_saml_show_licensing_page()
{
    $supportmail = "samlsupport@xecurify.com";
    $current_user = wp_get_current_user();
    $fname = $current_user->user_firstname;
    $lname = $current_user->user_lastname;
    wp_enqueue_script('jquery');
    wp_enqueue_style('mo_saml_contact_us', plugins_url('includes/css/support.css', __FILE__), array(), mo_saml_options_plugin_constants::Version, 'all');
    ?>
    <?php
    echo '<style>.update-nag, .updated, .error, .is-dismissible, .notice, .notice-error { display: none; }</style>';
    ?>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <div class="bg-white">
        <div class="lic-body">
            <div class="nav-menu">
                <div class="plugin-banner-text-saml goto-opt">
                    <a href="#pricing" class="pricing"> Pricing Plans </a>
                </div>
                <div class="plugin-banner-text-saml goto-opt">
                    <a href="#switch-id-saml" class="switch-id-saml"> Feature Comparison </a>
                </div>
                <div class="plugin-banner-text-saml goto-opt">
                    <a href="#pricing-faqs" class="pricing-faqs"> Pricing FAQs</a>
                </div>
                <div class="plugin-banner-text-saml goto-opt">
                    <a href="#payment-methods" class="payment-methods"> Payment Methods </a>
                </div>
                <div class="plugin-banner-text-saml goto-opt">
                    <a href="#upgrade-steps" class="steps-to-upgrade">Upgrade Steps</a>
                </div>
            </div>
            <input type="hidden" value="<?php echo mo_saml_is_customer_registered_saml(false); ?>" id="mo_customer_registered">
            <h2 class="plan-head-saml saml-scroll" id="pricing">Choose your Licensing Plan</h2>
            <div class="tabs text-center">
                <ul class="saml-pills">
                    <li><a id="single-site" data-tab="1" class="tab active">Single Site</a></li>
                    <li><a id="multi-site" data-tab="2" class="tab">Multi-Site</a></li>
                </ul>
            </div>
            <div class="row handler single-site text-center ml-4">
                <div class="col-md-9 reg-plans-saml single-site-rot common-rot reg-plans-saml-sing">
                    <div class="row">
                        <div class="col-md-4 hover-saml">
                            <h3 class="header-h3">STANDARD</h3>
                            <div class="price-list mt-5">
                                <li class="choose-plan-saml text-left"><i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Auto-Redirect to IdP</li>
                                <li class="choose-plan-saml text-left"><i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Basic Attribute Mapping (Username, Email, First Name, Last Name, Display Name)</li>
                                <li class="choose-plan-saml text-left"><br></li>
                                <li class="choose-plan-saml text-left"><br></li>
                            </div>
                            <p class="mt-5"><span class="display-1"><span>$</span>349 <sup>*</sup></span></p><br>
                            <a onclick="upgradeform('wp_saml_sso_standard_plan')" class="license-btn-saml">UPGRADE NOW</a>
                        </div>
                        <div class="col-md-4 hover-saml">
                            <h3 class="header-h3">PREMIUM</h3>
                            <div class="price-list mt-5">
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Advanced Attribute and Role Mapping
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>SAML Single Logout
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>IDP Metadata Sync
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>All Standard features<br>&nbsp;
                                </li>
                            </div>
                            <p class="mt-5"><span class="display-1"><span>$</span>449 <sup>*</sup></span></p><br>
                            <a onclick="upgradeform('wp_saml_sso_basic_plan')" class="license-btn-saml">UPGRADE NOW</a>
                        </div>
                        <div class="col-md-4 hover-saml">
                            <h3 class="header-h3">ENTERPRISE</h3>
                            <div class="price-list mt-5">
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Migration Support for Multiple Environments (Dev-Test-Prod)
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Multiple SAML IDP Support
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>SSO Login Audit
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>All Premium features<br>&nbsp;
                                </li>
                            </div>
                            <p class="mt-5"><span class="display-1"><span>$</span>549 <sup>*</sup></span></p><br>
                            <a onclick="upgradeform('wp_saml_sso_multiple_idp_plan')" class="license-btn-saml">UPGRADE NOW</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 single-site-rot common-rot">
                    <div class="incl-plan-saml">
                        <div class="product">
                            <div class="price-tag">
                                <p class="price">Best Value</p>
                            </div>
                        </div>
                        <h3 class="header-h3">ALL-INCLUSIVE</h3>
                        <div class="price-list mt-5 text-left price-list-incl">
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>SCIM Automatic User Sync
                            </li>
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>LearnDash, BuddyBoss & MemberPress Integration
                            </li>
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Page/Post Restriction
                            </li>
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i><a href="https://plugins.miniorange.com/wordpress-single-sign-on-sso-integrations" target="_blank" class="text-color"><u>All other addons</u></a> + All Enterprise features
                            </li>
                        </div>
                        <p class="mt-5"><span class="display-1"><span>$</span>649 <sup>*</sup></span></p><br>
                        <a onclick="upgradeform('wp_saml_sso_all_inclusive_plan')" class="license-btn-saml">UPGRADE NOW</a>
                    </div>
                </div>
            </div>
            <!-- MULTISITE -->
            <div class="row handler multi-site text-center ml-4" style="display: none;">
                <div class="col-md-8 reg-plans-saml multi-site-rot common-rot multi-site-height">
                    <div class="row">
                        <div class="col-md-6 hover-saml multi-site-height">
                            <h3 class="header-h3">PREMIUM</h3>
                            <div class="price-list mt-5 price-list-multi">
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Auto-Redirect to IdP
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Attribute and Role Management
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Connect all subsites to same SAML IdP
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>SAML Single Logout<br>&nbsp;
                                </li>
                            </div>
                            <p class="mt-5"><span class="display-1"><span>$</span>449 <sup>*</sup></span></p><br>
                            <a onclick="upgradeform('wp_saml_sso_multisite_basic_plan')" class="license-btn-saml">UPGRADE NOW</a>
                        </div>
                        <div class="col-md-6 hover-saml multi-site-height">
                            <h3 class="header-h3">ENTERPRISE</h3>
                            <div class="price-list mt-5 price-list-multi">
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Mu Domain Mapping Support
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Easy migration from staging to prod
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Setup SSO with multiple SAML IdPs
                                </li>
                                <li class="choose-plan-saml text-left">
                                    <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>All Premium features<br>&nbsp;
                                </li>
                            </div>
                            <p class="mt-5"><span class="display-1"><span>$</span>549 <sup>*</sup></span></p><br>
                            <a onclick="upgradeform('wp_saml_sso_multisite_multiple_idp_plan')" class="license-btn-saml">UPGRADE NOW</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 multi-site-rot common-rot">
                    <div class="incl-plan-saml multi-site-height">
                        <div class="product">
                            <div class="price-tag price-tag-mult">
                                <p class="price">Best Value</p>
                            </div>
                        </div>
                        <h3 class="header-h3">ALL-INCLUSIVE</h3>
                        <div class="price-list mt-5 text-left price-list-incl price-list-multi">
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Multisite Network SSO
                            </li>
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>SCIM Automatic User Sync
                            </li>
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i>Page/Post Restriction
                            </li>
                            <li class="choose-plan-saml">
                                <i class="fas fa-arrow-circle-right text-success">&nbsp;&nbsp;</i><a href="https://plugins.miniorange.com/wordpress-single-sign-on-sso-integrations" target="_blank" class="text-color"><u>All other addons</u></a> + All Enterprise features
                            </li>
                        </div>
                        <p class="mt-5"><span class="display-1"><span>$</span>649 <sup>*</sup></span></p><br>
                        <a onclick="upgradeform('wp_saml_sso_multisite_all_inclusive_plan')" class="license-btn-saml">UPGRADE NOW</a>
                    </div>
                </div>
            </div>
            <div class="saml-scroll" id="switch-id-saml"></div>
            <div class="text-center handler single-site" id="compare-plans">
                <a class="btn clk-icn collapsed" data-toggle="collapse" data-target="#demo" aria-expanded="false">Compare Plans&nbsp;&nbsp; <i class="fas fa-times"></i></a>
            </div>
            <div class="handler multi-site text-center" id="compare-multi-plans" style="display: none;">
                <a class="btn clk-icn collapsed" data-toggle="collapse" data-target="#demo1" aria-expanded="false">Compare Plans&nbsp;&nbsp; <i class="fas fa-close"></i></a>
            </div>
            <div class="row text-center mt-5 collapse cp-single-site show" id="demo">
                <table class="w-100 ml-3">
                    <tr class="box-shadow-saml sticky-menu-saml">
                        <th class="feat-head-saml">Features</th>
                        <th class="feat-head-saml">Standard</th>
                        <th class="feat-head-saml">Premium</th>
                        <th class="feat-head-saml">Enterprise</th>
                        <th class="feat-head-saml">All-Inclusive</th>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Unlimited Authentications</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Basic Attribute Mapping (Username, Email, First Name, Last Name,Display Name)</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Widget, Shortcode to add SAML IDP Login Link</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Auto-Redirect to IdP from login page</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Protect your complete site (Auto-Redirect to IdP from any page)</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Change SP base URL and SP Entity ID</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Different SAML Request binding type</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">SAML Single Logout</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Customized Role Mapping</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Auto-sync IdP Configuration from metadata</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Custom Attribute Mapping (Any attribute which is stored in user-meta table)</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Custom SP Certificate</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Easy Migration Support for Multiple Environments (Dev-test-prod)</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Multiple SAML IDP Support</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">SSO Login Audit</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Customize the SP metadata contact information</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">SCIM Automatic User Sync</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Page/Post Restriction (Premium)</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left tooltip-saml">Other Add-ons <i class="fas fa-info-circle"></i>
                            <ol class="tooltiptext">
                                <li>LearnDash Integration</li>
                                <li>BuddyPress/BuddyBoss Integration</li>
                                <li>Guest User Login</li>
                                <li>WooCommerce Integrator</li>
                                <li>SSO Session Management</li>
                                <li>Attribute Based Redirection</li>
                                <li>Paid Membership Pro Integrator</li>
                                <li>Profile Picture Map</li>
                                <li>Federation Single Sign-On</li>
                            </ol>
                        </td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><a onclick="upgradeform('wp_saml_sso_standard_plan')" class="license-btn-saml upg-btn border-rad-saml">UPGRADE NOW</a></td>
                        <td><a onclick="upgradeform('wp_saml_sso_basic_plan')" class="license-btn-saml upg-btn border-rad-saml">UPGRADE NOW</a></td>
                        <td><a onclick="upgradeform('wp_saml_sso_multiple_idp_plan')" class="license-btn-saml upg-btn border-rad-saml">UPGRADE NOW</a></td>
                        <td><a onclick="upgradeform('wp_saml_sso_all_inclusive_plan')" class="license-btn-saml upg-btn border-rad-saml">UPGRADE NOW</a></td>
                    </tr>
                </table>
            </div>
            <div class="row text-center mt-5 collapse cp-multi-site" id="demo1">
                <table class="w-100 ml-3">
                    <tr class="box-shadow-saml sticky-menu-saml">
                        <th class="feat-head-saml">Features</th>
                        <th class="feat-head-saml">Premium</th>
                        <th class="feat-head-saml">Enterprise</th>
                        <th class="feat-head-saml">All-Inclusive</th>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Unlimited Authentications</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Basic Attribute Mapping (Username, Email, First Name, Last Name,Display Name)</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Widget, Shortcode to add SAML IDP Login Link</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Auto-Redirect to IdP from login page</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Protect your complete site (Auto-Redirect to IdP from any page)</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Change SP base URL and SP Entity ID</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Different SAML Request binding type</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">SAML Single Logout</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Customized Role Mapping</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Auto-sync IdP Configuration from metadata</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Custom Attribute Mapping (Any attribute which is stored in user-meta table)</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Custom SP Certificate</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Sub-site specific SSO for Multisite</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Auto-Redirection from specific subsites</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Mu Domain Mapping Support</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Easy Migration Support for Multiple Environments (Dev-test-prod)</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Multiple SAML IDP Support</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">SSO Login Audit</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Customize the SP metadata contact information</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">SCIM Automatic User Sync</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left">Page/Post Restriction (Premium)</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr class="box-shadow-saml">
                        <td class="text-left tooltip-saml">Other Add-ons <i class="fas fa-info-circle"></i>
                            <ol class="tooltiptext">
                                <li>LearnDash Integration</li>
                                <li>BuddyPress/BuddyBoss Integration</li>
                                <li>Guest User Login</li>
                                <li>WooCommerce Integrator</li>
                                <li>SSO Session Management</li>
                                <li>Attribute Based Redirection</li>
                                <li>Paid Membership Pro Integrator</li>
                                <li>Profile Picture Map</li>
                                <li>Federation Single Sign-On</li>
                            </ol>
                        </td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><a onclick="upgradeform('wp_saml_sso_multisite_basic_plan')" class="license-btn-saml upg-btn border-rad-saml mt-4">UPGRADE NOW</a></td>
                        <td><a onclick="upgradeform('wp_saml_sso_multisite_multiple_idp_plan')" class="license-btn-saml upg-btn border-rad-saml mt-4">UPGRADE NOW</a></td>
                        <td><a onclick="upgradeform('wp_saml_sso_multisite_all_inclusive_plan')" class="license-btn-saml upg-btn border-rad-saml mt-4">UPGRADE NOW</a></td>
                    </tr>
                </table>
            </div>
            <form style="display:none; text-align: left;" id="loginform" action="https://login.xecurify.com/moas/login" target="_blank" method="post">
                <input type="text" name="redirectUrl" value="https://login.xecurify.com/moas/initializepayment">
                <input type="text" name="requestOrigin" id="requestOrigin" value="">
            </form>
            <div class="row ml-4 mr-4 mt-5 saml-scroll">
                <h2 class="plan-head-saml" id="pricing-faqs">Pricing FAQs</h2>
                <div class="col-md-6 mt-5">
                    <div class="faq">
                        <h3 class="faq-title">What is the licensing model?</h3>
                        <p class="faq-text">The plugin licenses are subscription and the Support Plan includes 12 months of maintenance (support and version updates). You can renew maintenance after 12 months at 50% of the current license cost.
                        </p>
                        <button class="faq-toggle"  onclick="toggleFAQ(0)" >
                            <i class="fas fa-chevron-down"></i>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mt-5">
                    <div class="faq">
                        <h3 class="faq-title">What is the refund policy?</h3>
                        <p class="faq-text">At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you've attempted to resolve any issues with our support team, which couldn't get resolved. We will
                            refund the whole amount within 10 days of the purchase. Please email us at <a href="mailto:samlsupport@xecurify.com" class="text-primary"><b>samlsupport@xecurify.com</b></a> for any queries regarding the return policy or contact us
                            <a href="https://www.miniorange.com/contact" target="_blank"><b>here</b>.</a>
                        </p>
                        <button class="faq-toggle" onclick="toggleFAQ(1)" >
                            <i class="fas fa-chevron-down"></i>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row ml-4 mr-4">
                <div class="col-md-6 mt-5">
                    <div class="faq">
                        <h3 class="faq-title">Does miniOrange offer technical support?</h3>
                        <p class="faq-text">Yes, we provide 24*7 support for all and any issues you might face while using the plugin, which includes technical support from our developers. You can get prioritized support based on the Support Plan you have opted. You can check out the
                            different Support Plans from <a href="https://www.miniorange.com/support-plans" target="_blank" class="text-primary"><b><u>here</u></b></a>.
                        </p>
                        <button class="faq-toggle" onclick="toggleFAQ(2)">
                            <i class="fas fa-chevron-down"></i>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mt-5">
                    <div class="faq">
                        <h3 class="faq-title">What is included in All-Inclusive Plan?</h3>
                        <p class="faq-text">Purchasing All-Inclusive plan will give all the features from Standard, Premium and Enterprise plans along with all the add-ons listed <a href="https://plugins.miniorange.com/wordpress-single-sign-on-sso-integrations" target="_blank"><b>here</b></a>.
                            <br> The cost given for All-Inclusive is for 1 license (site) only. If you have multiple sites then you will need to purchase license for each site, however, we do provide discount from 2nd license onwards.
                        </p>
                        <button class="faq-toggle"  onclick="toggleFAQ(3)">
                            <i class="fas fa-chevron-down"></i>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row ml-4 mr-4">
                <div class="col-md-6 mt-5">
                    <div class="faq">
                        <h3 class="faq-title">Does miniOrange store any user data?</h3>
                        <p class="faq-text">miniOrange does not store or transfer any data which is coming from the Identity Provider to the WordPress. All the data remains within your premises / server.</p>
                        <button class="faq-toggle"  onclick="toggleFAQ(4)">
                            <i class="fas fa-chevron-down"></i>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mt-5">
                    <div class="faq">
                        <h3 class="faq-title">Does miniorange provide developer license for paid plugin?</h3>
                        <p class="faq-text">We do not provide the developer license for our paid plugins and the source code is protected. It is strictly prohibited to make any changes in the code without having written permission from miniOrange. There are hooks provided in the plugin
                            which can be used by the developers to extend the plugin's functionality.</p>
                        <button class="faq-toggle"  onclick="toggleFAQ(5)">
                            <i class="fas fa-chevron-down"></i>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row ml-4 mr-4">
                <div class="col-md-12 col-xs-12">
                    <section class="tabs">
                        <ul class="tab-des">
                            <li class="tab-us intg-tab instances active-tab" style="border-bottom: 1px solid rgb(47, 79, 79);"><a class="tab-link"><span class="span-idp"></span>What is one instance?</a></li>
                            <li class="tab-us intg-tab multi-network" style="border-bottom: 4px solid rgb(47, 79, 79);"><a class="tab-link"><span class="span-idp"></span>What is a multisite network?</a></li>
                            <li class="tab-us intg-tab multi-idp" style="border-bottom: 1px solid rgb(47, 79, 79);"><a class="tab-link"><span class="span-idp"></span>What is Multiple IDP Support?</a></li>
                        </ul>
                    </section>
                    <section id="instances" class="integration-section" style="display: block;">
                        <br>
                        <p class="saml-text">A WordPress instance refers to a single installation of a WordPress site. It refers to each individual website where the plugin is active. In the case of a single site WordPress, each website will be counted as a single instance.
                            <br>
                            <b>License is linked to the domain of the WordPress instance, so if you have dev-staging-prod type of environment then you will require 3 licenses of the plugin (with discounts applicable on pre-production environments)
                            </b>
                        </p>
                        <p class="saml-text"><u>Note:</u> <b>Purchasing licenses for Unlimited instances will grant you upto 200 licenses</b>. If you want to purchase more licenses, please contact us <a href="https://www.miniorange.com/contact" target="_blank"><b>here</b></a> or drop
                            an email at <a href="mailto:samlsupport@xecurify.com" class="text-primary"><b>samlsupport@xecurify.com</b></a>
                        </p>
                    </section>
                    <section id="multi-network" class="integration-section active-section" style="display: none;">
                        <br>
                        <p class="saml-text"><a href="https://plugins.miniorange.com/wordpress-multisite-single-sign-on-sso-login" target="_blank">WordPress Multisite</a> is a feature that allows you to create a “network” of subsites within a single instance of WordPress, where the subsites share a file system and database. There are two types of multisite environments - subdomain (Example:
                            abc.domain.com) and subdirectory (Example: domain.com/abc)
                        </p>
                        <p class="saml-text"><u>Note:</u> <b>There is an additional cost for the number of subsites in Multisite Network</b> since the Multisite licenses are based on the <i>total number of subsites</i> in your WordPress Network.
                        </p>
                    </section>
                    <section id="multi-idp" class="integration-section" style="display: none;">
                        <br>
                        <p class="saml-text">Multiple IDP support allows you to enable authentication on your site from multiple IDPs. For example - If your users exist in Azure AD as well as Okta, then you can opt for the multiple IDP Plugin and allow user authentication from either
                            IDP. <b>We support SSO authentication from multiple Identity Providers in our Enterprise and All-Inclusive versions of the plugin</b>. You can check out more details of the multiple IDP flow from <a href="https://plugins.miniorange.com/wordpress-single-sign-on-sso-with-multiple-idp" target="_blank" class="text-primary"><b><u>here</u></b></a>.
                        </p>
                        <p class="saml-text"><u>Note:</u> <b>There is an additional cost for the IdPs</b> if the number of IdP is more than one.
                        </p>
                    </section>
                </div>
            </div>
            <br>
            <h2 class="plan-head-saml saml-scroll" id="payment-methods">Payment Methods </h2>
            <div class="row text-center">
                <div class="col-md-4 mt-5">
                    <div class="plan-box">
                        <div class="plan-box-up">
                            <i class="fab fa-cc-amex fa-3x"></i>
                            <i class="fab fa-cc-visa fa-3x"></i>
                            <i class="fab fa-cc-mastercard fa-3x"></i>
                        </div>
                        <p>Credit cards (American Express, Discover, MasterCard, and Visa) - If the payment is made through Credit Card/International Debit Card, the license will be created automatically once the payment is completed.</p>
                    </div>
                </div>
                <div class="col-md-4 mt-5">
                    <div class="plan-box">
                        <div class="plan-box-up">
                            <img class="payment-images" src="<?php echo plugin_dir_url(__FILE__) . '../images/paypal-logo.webp' ?>" alt="wordpress sso payment methods" width="35%">
                        </div>
                        <p>Please contact us <a href="https://www.miniorange.com/contact" target="_blank"><b>here</b></a> or drop an email at <a href="mailto:samlsupport@xecurify.com"><b>samlsupport@xecurify.com</b></a> for more information<br><br></p>
                    </div>
                </div>
                <div class="col-md-4 mt-5">
                    <div class="plan-box">
                        <div class="plan-box-up">
                            <i class="fa fa-university fa-3x"><span class="h3">&nbsp;&nbsp;Bank Transfer</span></i>
                        </div>
                        <p>Please contact us <a href="https://www.miniorange.com/contact" target="_blank"><b>here</b></a> or drop an email at <a href="mailto:samlsupport@xecurify.com"><b>samlsupport@xecurify.com</b></a> so that we can provide you the bank details.<br><br></p>
                    </div>
                </div>
            </div>
            <br>
            <h2 class="plan-head-saml saml-scroll" id="upgrade-steps">Steps to upgrade to Premium</h2>
            <br>
            <section class="section-steps ml-4 mr-3" id="section-steps">
                <div class="row">
                    <div class="col span-1-of-2 steps-box">
                        <div class="works-step">
                            <div>1</div>
                            <p class="saml-text">
                                Click on Upgrade Now button of the required licensing plan. You will be redirected to <b>miniOrange Login Console</b>. Enter your password with which you created an account with us. After that you will be redirected to payment page.
                            </p>
                        </div>
                        <br>
                        <div class="works-step">
                            <div>2</div>
                            <p class="saml-text">
                                Enter your card details and complete the payment. On successful payment completion, you will see the link to download the premium plugin.
                            </p>
                        </div>
                    </div>
                    <div class="col span-1-of-2 steps-box">
                        <div class="works-step">
                            <div>3</div>
                            <p class="saml-text">
                                To install the premium plugin, first deactivate and delete the free version of the plugin. Enable the "Keep Configuration Intact" checkbox before deactivating the plugin. By doing so, your saved configurations of the plugin will not get lost.
                            </p>
                        </div>
                        <br>
                        <div class="works-step">
                            <div>4</div>
                            <p class="saml-text">
                                From this point on, do not update the premium plugin from the WordPress store.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <form style="display:none;" id="loginform" action="<?php echo mo_saml_options_plugin_constants::HOSTNAME . '/moas/login'; ?>" target="_blank" method="post">
        <input type="email" name="username" value="<?php echo get_option('mo_saml_admin_email'); ?>" />
        <input type="text" name="redirectUrl" value="<?php echo mo_saml_options_plugin_constants::HOSTNAME . '/moas/initializepayment'; ?>" />
        <input type="text" name="requestOrigin" id="requestOrigin" />
    </form>
    <a id="mobacktoaccountsetup" style="display:none;" href="<?php echo mo_saml_add_query_arg(array('tab' => 'account-setup'), htmlentities($_SERVER['REQUEST_URI'])); ?>"><?php _e('Back', 'miniorange-saml-20-single-sign-on'); ?></a>
    <script>
        function upgradeform(planType) {
            jQuery('#requestOrigin').val(planType);
            if (jQuery('#mo_customer_registered').val() == 1)
                jQuery('#loginform').submit();
            else {
                location.href = jQuery('#mobacktoaccountsetup').attr('href');
            }
        }
    </script>
    <?php
}