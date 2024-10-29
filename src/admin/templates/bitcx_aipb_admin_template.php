<?php if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="wrap bitcx_aipb_admin_area">
    <h1>Proposal Builder</h1>
    <?php
     if (isset($_SESSION['bitcx_aipb_api_message_success'])) {
        echo '<div class="updated"><p>' .esc_html(sanitize_text_field($_SESSION['bitcx_aipb_api_message_success'])). '</p></div>';
        unset($_SESSION['bitcx_aipb_api_message_success']);
    }
    
    if (isset($_SESSION['bitcx_aipb_api_message_error'])) {
        echo '<div class="error"><p>' .esc_html(sanitize_text_field($_SESSION['bitcx_aipb_api_message_error'])). '</p></div>';
        unset($_SESSION['bitcx_aipb_api_message_error']);
    }    
     ?>
    <div class="bitcx_aipb_main_menu">
        <a class="bitcx_aipb_menu_link" href="edit.php?post_type=bitcx_portfolio">
            <div class="bitcx_aipb_menu">
                <div class="bitcx_aipb_menu_icon">
                    <img class="portfolio_icon" src="<?php echo esc_url(sanitize_url(plugin_dir_url(__FILE__) . '../images/portfolio.png')); ?>"
                        alt="Portfolio IMG">
                </div> <br>
                PORTFOLIO
            </div>
        </a>

        <a class="bitcx_aipb_menu_link" href="edit.php?post_type=bitcx_testimonial">
            <div class="bitcx_aipb_menu">
                <div class="bitcx_aipb_menu_icon">
                    <img class="testimonials_icon"
                        src="<?php echo esc_url(sanitize_url(plugin_dir_url(__FILE__) . '../images/testimonials.png')); ?>"
                        alt="Testimonials IMG">
                </div> <br>
                TESTIMONIALS
            </div>
        </a>

        <a class="bitcx_aipb_menu_link" href="edit.php?post_type=bitcx_aipb_cta">
            <div class="bitcx_aipb_menu">
                <div class="bitcx_aipb_menu_icon">
                    <img class="cta_icon" src="<?php echo esc_url(sanitize_url(plugin_dir_url(__FILE__) . '../images/cta.png')); ?>"
                        alt="CTA IMG">
                </div> <br>
                CTAS
            </div>
        </a>

    </div>
    <div class="bitcx_aipb_api_setting">
        <div>
            <form method="post" action="" style="margin:10px 0px">
                <label for="bitcx_aipb_ai_api_key">
                    <span>API SETTING </span>
                    <input type="password" size="70" id="bitcx_aipb_ai_api_key" name="bitcx_aipb_ai_api_key"
                        value="<?php echo esc_attr(sanitize_text_field(get_option('bitcx_aipb_ai_api_key'))); ?>" />
                </label>
                <input type="submit" name="bitcx_aipb_ai_save_api_key" class="button bitcx_aipb_save_btn"
                    value="Verify" />
                    <?php wp_nonce_field( 'bitcx_aipb_nonce_api_key_submit', 'nonce_api_key' ); ?>
            </form>
        </div>
        <span>Enter your OpenAI API Key above. Don't have one yet? Get it <a target="_blank"
                href="https://platform.openai.com/api-keys"> here.</a> <br> Note that this requires a premium key, not a free one.</span>
    </div>
    <div class="bitcx_aipb_shortcode">
        <label for="api_key">
            <span style="font-size:18px ">SHORTCODE</span>
            <input type="text" size="75" value="[bitcx_aipb_form]" readonly />
        </label>
    </div>

</div>