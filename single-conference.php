<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo str_replace(['&lt;br/&gt;', '&lt;br&gt;', '<br>', '<br/>'], ' ', esc_html(get_the_title())); ?></title>
    <?php wp_head(); ?>
    <style>
        html, body {
            margin: 0; padding: 0; width: 100%; height: 100%; font-family: sans-serif;
        }
        .conference-container {
            width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background-size: cover; background-position: center center; background-attachment: fixed;
            position: relative; box-sizing: border-box;
        }
        .logo-title-container {
            position: absolute;
            z-index: 10;
            padding: 20px;
            width: 100%;
            box-sizing: border-box; 
        }
        .conference-logo {
            height: auto;
            display: block;
        }
        .conference-logo-title {
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }
        .conference-sub-logo-texts p {
            margin: 5px 0;
            padding: 0;
        }
        .logo-top-left { top: 0; left: 0; text-align: left; }
        .logo-top-center { top: 0; left: 50%; transform: translateX(-50%); text-align: center; }
        .logo-top-right { top: 0; right: 0; text-align: right; }
        .logo-bottom-left { bottom: 0; left: 0; text-align: left; }
        .logo-bottom-right { bottom: 0; right: 0; text-align: right; }
        .logo-top-center .conference-logo, .logo-top-center .conference-logo-title { margin-left: auto; margin-right: auto;}
        .logo-top-right .conference-logo, .logo-top-right .conference-logo-title { margin-left: auto; margin-right: 0;}
        .logo-bottom-right .conference-logo, .logo-bottom-right .conference-logo-title { margin-left: auto; margin-right: 0;}
        .conference-toc-wrapper {
            width: 100%; box-sizing: border-box; padding: 20px 5%;
            background-color: transparent; position: relative;
        }
        .conference-toc-wrapper ul { list-style: none; padding: 0; margin: 0 auto; max-width: 960px; }
        .conference-toc-wrapper ul li { margin: 10px 0; }
        .conference-toc-wrapper ul li a {
            text-decoration: none; display: block; padding: 15px; background-color: rgba(0, 0, 0, 0.2);
            border-radius: 5px; transition: all 0.3s ease; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .conference-toc-wrapper ul li a:hover { transform: scale(1.02); background-color: rgba(0, 0, 0, 0.4); }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .conference-logo-title { font-size: 48px !important; }
            .sub-logo-text-item { font-size: 28px !important; }
            .conference-logo { max-width: 100px !important; }
            .conference-toc-wrapper ul li a { padding: 12px; font-size: 16px !important; }
            .logo-title-container { padding: 10px; }
        }
    </style>
</head>
<body <?php body_class(); ?>>
    <?php if (have_posts()) : while (have_posts()) : the_post();
        // --- Phần lấy dữ liệu PHP ---
        $documents = get_post_meta(get_the_ID(), 'cm_documents', true);
        $toc_items = get_post_meta(get_the_ID(), 'cm_toc_items', true);
        $logo_url = get_post_meta(get_the_ID(), 'cm_logo_url', true);
        $logo_position = get_post_meta(get_the_ID(), 'cm_logo_position', true) ?: 'top-left';
        $logo_size = get_post_meta(get_the_ID(), 'cm_logo_size', true);
        $logo_title_size = get_post_meta(get_the_ID(), 'cm_logo_title_font_size', true);
        $logo_title_family = get_post_meta(get_the_ID(), 'cm_logo_title_font_family', true);
        $logo_title_weight = get_post_meta(get_the_ID(), 'cm_logo_title_font_weight', true);
        $logo_title_color = get_post_meta(get_the_ID(), 'cm_logo_title_color', true);
        $logo_title_padding = get_post_meta(get_the_ID(), 'cm_logo_title_padding', true);
        $sub_logo_texts = get_post_meta(get_the_ID(), 'cm_sub_logo_texts', true);
        $background = get_post_meta(get_the_ID(), 'cm_background', true);
        $background_style_option = get_post_meta(get_the_ID(), 'cm_background_style', true);
        $alignment = get_post_meta(get_the_ID(), 'cm_alignment', true) ?: 'center';
        $toc_position = get_post_meta(get_the_ID(), 'cm_toc_position', true) ?: 'middle';
        $toc_padding = get_post_meta(get_the_ID(), 'cm_toc_padding', true);
        $toc_font_size = get_post_meta(get_the_ID(), 'cm_toc_font_size', true);
        $toc_font_family = get_post_meta(get_the_ID(), 'cm_toc_font_family', true);
        $toc_color = get_post_meta(get_the_ID(), 'cm_toc_color', true) ?: '#ffffff';

        // --- Xử lý style ---
        $container_style = '';
        if ($background) {
             $container_style .= "background-image: url('" . esc_url($background) . "');";
             switch ($background_style_option) {
                case 'stretch': $container_style .= 'background-size: 100% 100%;'; break;
                case 'repeat': $container_style .= 'background-size: auto; background-repeat: repeat;'; break;
                case 'center': $container_style .= 'background-size: contain; background-repeat: no-repeat;'; break;
                default: $container_style .= 'background-size: cover;'; break;
            }
        }
        $logo_inline_style = !empty($logo_size) ? 'width: ' . esc_attr($logo_size) . ';' : 'width: 150px;';
        
        $logo_title_style = '';
        if (!empty($logo_title_size)) $logo_title_style .= 'font-size: ' . esc_attr($logo_title_size) . ';';
        // SỬA LỖI FONT: Quay lại cách viết đơn giản và ổn định
        if (!empty($logo_title_family)) $logo_title_style .= 'font-family: ' . esc_attr($logo_title_family) . ';';
        if (!empty($logo_title_weight)) $logo_title_style .= 'font-weight: ' . esc_attr($logo_title_weight) . ';';
        if (!empty($logo_title_color)) $logo_title_style .= 'color: ' . esc_attr($logo_title_color) . ';';
        if (!empty($logo_title_padding)) $logo_title_style .= 'padding-top: ' . esc_attr($logo_title_padding) . ';';
        
        $toc_wrapper_style = '';
        if (!empty($toc_padding)) {
            $toc_wrapper_style = 'padding-top: ' . esc_attr($toc_padding) . ' !important;';
        }
        
    ?>
    <div class="conference-container" style="<?php echo esc_attr($container_style); ?>">
        
        <div class="logo-title-container <?php echo esc_attr('logo-' . $logo_position); ?>">
            <?php if ($logo_url) : ?>
                <img src="<?php echo esc_url($logo_url); ?>" class="conference-logo" style="<?php echo $logo_inline_style; ?>" alt="Conference Logo">
            <?php endif; ?>
            
            <h1 class="conference-logo-title" style="<?php echo esc_attr($logo_title_style); ?>">
                <?php echo str_replace(['&lt;br/&gt;', '&lt;br&gt;'], '<br>', esc_html(get_the_title())); ?>
            </h1>

            <div class="conference-sub-logo-texts">
                <?php
                if (!empty($sub_logo_texts) && is_array($sub_logo_texts)) {
                    foreach ($sub_logo_texts as $text_item) {
                        $style = '';
                        // SỬA LỖI FONT: Quay lại cách viết đơn giản và ổn định
                        if (!empty($text_item['font_family'])) $style .= 'font-family: ' . esc_attr($text_item['font_family']) . ';';
                        if (!empty($text_item['font_size'])) $style .= 'font-size: ' . esc_attr($text_item['font_size']) . 'px;';
                        if (!empty($text_item['font_weight'])) $style .= 'font-weight: ' . esc_attr($text_item['font_weight']) . ';';
                        if (!empty($text_item['font_color'])) $style .= 'color: ' . esc_attr($text_item['font_color']) . ';';
                        if (!empty($text_item['alignment'])) $style .= 'text-align: ' . esc_attr($text_item['alignment']) . ';';
                        ?>
                        <p class="sub-logo-text-item" style="<?php echo $style; ?>">
                            <?php echo esc_html($text_item['text']); ?>
                        </p>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <?php if (!empty($toc_items) && is_array($toc_items) && !empty($documents)) : ?>
            <div class="conference-toc-wrapper toc-position-<?php echo esc_attr($toc_position); ?>" style="<?php echo esc_attr($toc_wrapper_style); ?>">
                 <ul style="text-align: <?php echo esc_attr($alignment); ?>;">
                    <?php 
                    foreach ($toc_items as $item) {
                        $doc_index = $item['doc_index'];
                        $display_name = $item['name'];
                        if (isset($documents[$doc_index])) {
                            $doc_url = $documents[$doc_index]['url'];
                            $link_style = '';
                            if (!empty($toc_font_size)) $link_style .= 'font-size: ' . esc_attr($toc_font_size) . ';';
                            // SỬA LỖI FONT: Quay lại cách viết đơn giản và ổn định
                            if (!empty($toc_font_family)) $link_style .= 'font-family: ' . esc_attr($toc_font_family) . ';';
                            if (!empty($toc_color)) $link_style .= 'color: ' . esc_attr($toc_color) . ';';
                            ?>
                            <li>
                                <a href="<?php echo esc_url($doc_url); ?>" target="_blank" style="<?php echo esc_attr($link_style); ?>">
                                    <?php echo esc_html($display_name); ?>
                                </a>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?php endwhile; endif; ?>
    <?php wp_footer(); ?>
</body>
</html>