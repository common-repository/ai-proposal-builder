<?php if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="bitcx_aipb_proposal_form">
    <form action="" method="POST" class="bitcx_aipb_pr-form">
        <div class="bitcx_aipb_pr-form-group intial_info">
            <div class="client_name">
                <label class="bitcx_aipb_pr-form-label" for="client_name">Client Name:</label>
                <input type="text" name="clientName" id="bitcx_aipb_client_name"
                    class="bitcx_aipb_pr-input-clientName bitcx_aipb_pr-input" required>
            </div>
            <div class="freelancer_name">
                <label class="bitcx_aipb_pr-form-label" for="client_name">Freelancer Name:</label>
                <input type="text" name="clientName" id="bitcx_aipb_freelance_name"
                    class="bitcx_aipb_pr-input-freelanceName bitcx_aipb_pr-input" required>
            </div>
        </div>

        <div class="bitcx_aipb_pr-form-group">
            <label class="bitcx_aipb_pr-form-label" for="client_name">Job Title:</label>
            <input type="text" name="jobtitle" id="bitcx_aipb_job_title"
                class="bitcx_aipb_pr-input-jobtitle bitcx_aipb_pr-input" required>
        </div>

        <div class="bitcx_aipb_pr-form-group" style="margin-bottom:0px !important">
            <label class="bitcx_aipb_pr-form-label" for="problem_desc">Job Description <p>(in your own words)</p>
            </label>
            <textarea name="problemDesc1" id="bitcx_aipb_problem_desc"
                class="bitcx_aipb_pr-textarea bitcx_aipb_pr-input" required></textarea>
            <p id="bitcx_aipb_ai_response"></p>
        </div>
        <div class="generate-btn-area">
            <div class="generate-button">
                <button type="button" id="bitcx_aipb_getSolution" class="bitcx_aipb_pr_btn">Generate
                    Proposal</button>
            </div>
            <div id="bitcx_aipb_loadingSpinner" style="display: none;">
                <img src="<?php echo esc_url(sanitize_url(plugin_dir_url(__FILE__) . '../images/loader.gif')); ?>" alt="Loader GIF"
                    style="width:48px;height:48px;">
                <!-- Animation -->
            </div>
        </div>
        <div class="bitcx_aipb_pr-form-group">
            <label class="bitcx_aipb_pr-form-label" for="solution">Your Solution:</label>
            <textarea name="solution" id="bitcx_aipb_solution"
                class="bitcx_aipb_pr-textarea bitcx_aipb_pr-input" required></textarea>
        </div>
        <div class="bitcx_aipb_pr-portfolio-cat-group">
            <label class="bitcx_aipb_pr-form-label bitcx_aipb_pr_urgent_margin" for="problem_desc">Select
                Portoflio Type:</label>
            <?php if(!empty($portfolio_cats)) : ?>
            <select name="portfolio_cats" class="bitcx_aipb_pr_select" id="bitcx_aipb_portfolio_cats">
                <option value="" selected> Select Category </option>
                <?php foreach ($portfolio_cats as $key => $portfolio_cat) { ?>
                <option value="<?php echo esc_attr(sanitize_text_field($portfolio_cat->slug)); ?>">
                    <?php echo esc_html(sanitize_text_field($portfolio_cat->name)); ?> </option>
                <?php } ?>
            </select>
            <?php else: ?>
            <h4 class="bitcx_aipb_danger_heading">No Portfolio Categories are Available</h4>
            <?php endif;?>
        </div>
        <div class="bitcx_aipb_pr-form-group bitcx_aipb_pr-portfolio-group">
            <?php if($portfolio_items->have_posts()) : ?>
            <div class="bitcx_aipb_pr_portfolio_items swiper bitcx_swiper">
                <div class="swiper-wrapper">
                    <?php while ( $portfolio_items->have_posts() ) : $portfolio_items->the_post();?>
                    <?php
                        $url = wp_get_attachment_url(get_post_thumbnail_id(esc_attr(get_the_ID())), 'thumbnail');
                        $portfolio_link = get_post_meta( esc_attr(get_the_ID()), 'bitcx_portfolio_item_link', true);
                        $portfolio_cats = get_the_terms( esc_attr(get_the_ID()), 'bitcx_portfolio_category');
                        $portfolio_cat_array = [];
                        if(!empty($portfolio_cats)){
                            foreach ($portfolio_cats as $cat) {
                                $portfolio_cat_array[] = $cat->slug;
                            }
                        };
                    ?>
                    <div class="swiper-slide bitcx_aipb_item" data-cat="<?php echo esc_attr(sanitize_text_field(implode(",", $portfolio_cat_array))); ?>">
                        <div class="bitcx_aipb_checkbox_field">
                            <input type="checkbox" id="checkbox1" data-title="<?php echo esc_attr(sanitize_title(the_title()));?>"
                                data-img="<?php echo esc_url(sanitize_url($url));?>"
                                data-desc="<?php echo esc_attr(sanitize_text_field(get_the_content()));?>"
                                data-url="<?php echo esc_url(sanitize_url($portfolio_link));?>" name="bitcx_aipb_portfolioChoice"
                                class="bitcx_aipb_form-check-input"
                                id="bitcx_aipb_portfolio-choice-<?php echo esc_attr(get_the_ID());?>"
                                value="portfolio-<?php echo esc_attr(sanitize_key(get_the_ID()));?>" />
                        </div>
                        <div class="bitcx_aipb_checkbox_context">
                            <?php if($url): ?>
                                <img src="<?php echo esc_url(sanitize_url($url));?>" alt="featured-image" class="img-fluid">
                            <?php endif; ?>
                            <div>
                                <h3><a target="_blank" href="<?php echo esc_url(sanitize_url($portfolio_link));?>"><?php esc_html(the_title());?></a></h3>
                            </div>
                        </div>
                    </div>
                    <!--  -->
                    <?php endwhile;?>
                </div>
                <!-- Add navigation buttons -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
            <?php else : ?>
            <h4 class="bitcx_aipb_danger_heading">No Portfolio Items are Available</h4>
            <?php endif;?>
        </div>
        <div class="bitcx_aipb_pr-form-group bitcx_aipb_testimonials-group">
            <label class="bitcx_aipb_pr-form-label" for="problem_desc">Select Testimonials</label>
            <?php if($testimonial_items->have_posts()) :
                while ( $testimonial_items->have_posts() ) : $testimonial_items->the_post();?>
            <div class="bitcx_aipb_pr-display-flex">
                <div>
                    <input type="checkbox" data-title="<?php echo esc_attr(sanitize_title(the_title()));?>"
                        data-desc="<?php echo esc_attr(sanitize_text_field(get_the_content()));?>" name="bitcx_aipb_testiChoice"
                        class="bitcx_aipb_form-check-input2"
                        id="bitcx_aipb_testi-choice-<?php echo esc_attr(sanitize_key(get_the_ID())) ?>"
                        value="testi-<?php echo esc_attr(sanitize_key(get_the_ID()));?>" />
                </div>
                <div class="bitcx_aipb_pr_full_width">
                    <div>
                        <label
                            for="bitcx_aipb_testi-choice-<?php echo esc_attr(sanitize_key(get_the_ID()));?>"><?php echo esc_html(sanitize_text_field(wp_trim_words( get_the_content(), 24, '...' )));?></label>
                        <label for="bitcx_aipb_testi-choice-<?php echo esc_attr(sanitize_key(get_the_ID()));?>"
                            class="bitcx_aipb_clientName"><?php echo esc_html(sanitize_title(the_title()));?></label>
                    </div>
                </div>
            </div>
            <?php endwhile; else : ?>
            <h4 class="bitcx_aipb_danger_heading">No Testimonial Items are Available</h4>
            <?php endif; ?>
        </div>
        <div class="bitcx_aipb_pr-form-group">
            <label class="bitcx_aipb_pr-form-label" for="bitcx_aipb_problem_question">Your Questions
                (Optional)</label>
            <textarea name="problemDesc" id="bitcx_aipb_problem_question"
                class="bitcx_aipb_pr-textarea bitcx_aipb_pr-input" required></textarea>
        </div>
        <div class="bitcx_aipb_pr-form-group bitcx_aipb_ctas-group">
            <label class="bitcx_aipb_pr-form-label" for="problem_desc">Select CTAs</label>
            <?php if($cta_items->have_posts()) :
                while ( $cta_items->have_posts() ) : $cta_items->the_post();?>
            <div>
                <div class="bitcx_aipb_pr-display-flex">
                    <div>
                        <input type="radio" data-title="<?php echo esc_attr(sanitize_title(get_the_title()));?>"
                            data-desc="<?php echo esc_attr(sanitize_text_field(get_the_content()));?>" name="bitcx_aipb_ctaChoice"
                            class="bitcx_aipb_form-radio-input2"
                            id="bitcx_aipb_cta-choice-<?php echo esc_attr(get_the_ID());?>"
                            value="cta-<?php echo esc_attr(get_the_ID());?>" />
                    </div>
                    <div>
                        <label
                            for="bitcx_aipb_cta-choice-<?php echo esc_attr((get_the_ID()));?>"><?php echo esc_html(sanitize_text_field(wp_trim_words( get_the_content(), 24, '...' )));?></label>
                        <!-- <label for="bitcx_aipb_cta-choice-<?php echo esc_attr(sanitize_key(get_the_ID()));?>"
                            class="bitcx_aipb_clientName"><?php esc_html(sanitize_title(the_title()));?></label> -->
                    </div>
                </div>
            </div>
            <?php endwhile; else : ?>
            <h4 class="bitcx_aipb_danger_heading">No CTA Items are Available</h4>
            <?php endif;?>
        </div>

        <div id="bitcx_aipb_pr_result">
            <p class='bitcx_aipb_danger_heading'></p>
        </div>
        <div class="bitcx_aipb_pr_text_end bitcx_aipb_pr_urgent_margin">
            <button type="submit" class="bitcx_aipb_pr_btn" id="bitcx_aipb_get_proposal"
                name="getproposal">View Proposal</button>
        </div>
    </form>
    <div id="bitcx_aipb_pr_output" class="bitcx_aipb_pr_d_none">
        <div class="bitcx_aipb_pr_output">
            <div id="bitcx_aipb_pr_popup">
                <div class="bitcx_aipb_closs-popup bitcx_aipb_pr_text_end">
                    <button class="bitcx_aipb_pr_btn" id="bitcx_aipb_copy_button">Copy</button>
                    <span class="bitcx_aipb_cross_icon">&#10539;</span>
                </div>
                <div class="bitcx_aipb_popup_content" id="bitcx_aipb_popup_content"></div>
                <div class="bitcx_aipb_Brouchure_btn_div bitcx_aipb_pr_text_end bitcx_aipb_pr_d_none"
                    id="bitcx_aipb_dwnld_brochure">
                    <button class="bitcx_aipb_pr_btn">Download Brouchure</button>
                </div>
            </div>
        </div>
    </div>
</div>
