<?php
/**
 * Plugin Name: Conference Manager
 * Description: A plugin to create and manage conferences with document catalogs.
 * Version: 1.1
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// 1. Đăng ký Custom Post Type 'conference'
function cm_register_conference_post_type() {
    $labels = array(
        'name'               => _x( 'Conferences', 'post type general name' ),
        'singular_name'      => _x( 'Conference', 'post type singular name' ),
        'add_new'            => _x( 'Add New', 'conference' ),
        'add_new_item'       => __( 'Add New Conference' ),
        'edit_item'          => __( 'Edit Conference' ),
        'new_item'           => __( 'New Conference' ),
        'all_items'          => __( 'All Conferences' ),
        'view_item'          => __( 'View Conference' ),
        'search_items'       => __( 'Search Conferences' ),
        'not_found'          => __( 'No conferences found' ),
        'not_found_in_trash' => __( 'No conferences found in the Trash' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Conferences'
    );
    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => true,
        'show_in_rest'  => true,
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'menu_icon'     => 'dashicons-groups',
    );
    register_post_type( 'conference', $args );
}
add_action( 'init', 'cm_register_conference_post_type' );

// 2. Thêm các Meta Box
function cm_add_meta_boxes() {
    add_meta_box(
        'cm_details_meta_box',
        'Conference Details',
        'cm_display_details_meta_box',
        'conference',
        'normal',
        'high'
    );
    add_meta_box(
        'cm_documents_meta_box',
        'Conference Documents',
        'cm_display_documents_meta_box',
        'conference',
        'normal',
        'default'
    );
    add_meta_box(
        'cm_page_settings_meta_box',
        'Conference Page Settings',
        'cm_display_page_settings_meta_box', // Đây là hàm đã được cập nhật
        'conference',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'cm_add_meta_boxes' );

// 3. Hiển thị Meta Box cho "Conference Details"
function cm_display_details_meta_box($post) {
    // Lấy dữ liệu đã lưu
    $time = get_post_meta($post->ID, 'cm_time', true);
    $location = get_post_meta($post->ID, 'cm_location', true);
    
    // Thêm nonce field để bảo mật
    wp_nonce_field('cm_save_meta_box_data', 'cm_details_meta_box_nonce');
    ?>
    <p>
        <label for="cm_time">Time:</label><br>
        <input type="text" id="cm_time" name="cm_time" value="<?php echo esc_attr($time); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="cm_location">Location:</label><br>
        <input type="text" id="cm_location" name="cm_location" value="<?php echo esc_attr($location); ?>" style="width:100%;" />
    </p>
    <?php
}

// 4. Hiển thị Meta Box cho "Conference Documents"
function cm_display_documents_meta_box($post) {
    $documents = get_post_meta($post->ID, 'cm_documents', true) ?: [];
    ?>
    <div id="cm-documents-container">
        <ul id="cm-documents-list">
            <?php if (!empty($documents)) : ?>
                <?php foreach ($documents as $index => $doc) : ?>
                    <li>
                        <input type="hidden" name="cm_documents[<?php echo $index; ?>][name]" value="<?php echo esc_attr($doc['name']); ?>">
                        <input type="hidden" name="cm_documents[<?php echo $index; ?>][url]" value="<?php echo esc_url($doc['url']); ?>">
                        <span><?php echo esc_html($doc['name']); ?></span>
                        (<a href="<?php echo esc_url($doc['url']); ?>" target="_blank">Xem</a>)
                        <button type="button" class="button button-link-delete remove-document">Xóa</button>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <button type="button" id="upload_document_button" class="button">Upload Documents</button>
    </div>
    <?php
}

// 5. Hiển thị Meta Box cho "Conference Page Settings" (ĐÃ CẬP NHẬT)
function cm_display_page_settings_meta_box($post) {
    // Lấy tất cả dữ liệu meta đã lưu
    $documents = get_post_meta($post->ID, 'cm_documents', true) ?: [];
    $toc_items = get_post_meta($post->ID, 'cm_toc_items', true) ?: [];
    $background = get_post_meta($post->ID, 'cm_background', true);
    $background_style = get_post_meta($post->ID, 'cm_background_style', true);
    $logo_url = get_post_meta($post->ID, 'cm_logo_url', true);
    $logo_position = get_post_meta($post->ID, 'cm_logo_position', true);
    $logo_size = get_post_meta($post->ID, 'cm_logo_size', true);
    $logo_title_size = get_post_meta($post->ID, 'cm_logo_title_font_size', true);
    $logo_title_family = get_post_meta($post->ID, 'cm_logo_title_font_family', true);
    $logo_title_weight = get_post_meta($post->ID, 'cm_logo_title_font_weight', true);
    $logo_title_color = get_post_meta($post->ID, 'cm_logo_title_color', true);
    $logo_title_padding = get_post_meta($post->ID, 'cm_logo_title_padding', true);
    $alignment = get_post_meta($post->ID, 'cm_alignment', true);
    $toc_position = get_post_meta($post->ID, 'cm_toc_position', true);
    $toc_font_size = get_post_meta($post->ID, 'cm_toc_font_size', true);
    $toc_font_family = get_post_meta($post->ID, 'cm_toc_font_family', true);
    $toc_color = get_post_meta($post->ID, 'cm_toc_color', true);
    $sub_logo_texts = get_post_meta($post->ID, 'cm_sub_logo_texts', true);
    ?>

    <div class="cm-tabs-container">
        <h2 class="nav-tab-wrapper">
            <a href="#tab-toc-builder" class="nav-tab nav-tab-active">TOC Builder</a>
            <a href="#tab-logo-title" class="nav-tab">Logo & Title</a>
            <a href="#tab-sub-logo-text" class="nav-tab">Sub-logo Texts</a>
            <a href="#tab-background" class="nav-tab">Background</a>
            <a href="#tab-toc-style" class="nav-tab">TOC Style</a>
        </h2>

        <div id="tab-toc-builder" class="tab-content active">
            <h4>Table of Contents Builder</h4>
            <div id="toc-builder-wrapper">
                <p>Kéo thả để sắp xếp lại mục lục. Sửa tên hiển thị nếu cần.</p>
                <ul id="toc-builder-list">
                    <?php
                    if (!empty($toc_items)) {
                        foreach ($toc_items as $item) {
                            $doc_index = $item['doc_index'];
                            $custom_name = $item['name'];
                            if (isset($documents[$doc_index])) {
                                $original_name = $documents[$doc_index]['name'];
                                ?>
                                <li data-doc-index="<?php echo esc_attr($doc_index); ?>">
                                    <span class="dashicons dashicons-menu handle"></span>
                                    <input type="text" value="<?php echo esc_attr($custom_name); ?>" placeholder="<?php echo esc_attr($original_name); ?>" />
                                    <button type="button" class="button button-link-delete remove-toc-item"><span class="dashicons dashicons-trash"></span></button>
                                </li>
                                <?php
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
            <div id="add-toc-item-controls">
                <button type="button" id="add-to-toc-btn" class="button"><span class="dashicons dashicons-plus-alt"></span> Thêm tài liệu vào mục lục</button>
                <div id="document-selector-wrapper" style="display:none;">
                    <select id="document-selector"></select>
                    <button type="button" id="confirm-add-document-btn" class="button button-primary">Thêm</button>
                    <button type="button" id="cancel-add-document-btn" class="button">Hủy</button>
                </div>
            </div>
            <input type="hidden" name="cm_toc_items_hidden" id="cm_toc_items_hidden" value="">
        </div>

        <div id="tab-logo-title" class="tab-content">
            <h4>Logo Settings</h4>
            <p><label for="cm_logo_url">Logo Image:</label><br><input type="text" id="cm_logo_url" name="cm_logo_url" value="<?php echo esc_url($logo_url); ?>" style="width:70%;" /> <button type="button" id="upload_logo_button" class="button">Upload Logo</button></p>
            <p><label for="cm_logo_position">Logo Position:</label><br><select name="cm_logo_position" id="cm_logo_position">
                <option value="top-left" <?php selected($logo_position, 'top-left'); ?>>Top Left</option>
                <option value="top-center" <?php selected($logo_position, 'top-center'); ?>>Top Center</option>
                <option value="top-right" <?php selected($logo_position, 'top-right'); ?>>Top Right</option>
                <option value="bottom-left" <?php selected($logo_position, 'bottom-left'); ?>>Bottom Left</option>
                <option value="bottom-right" <?php selected($logo_position, 'bottom-right'); ?>>Bottom Right</option>
            </select></p>
            <p><label for="cm_logo_size">Logo Size (e.g., 150px, 100%):</label><br><input type="text" id="cm_logo_size" name="cm_logo_size" value="<?php echo esc_attr($logo_size); ?>" placeholder="Default: 150px" /></p>
            <hr>
            <h4>Title Settings</h4>
            <p><label for="cm_logo_title_font_size">Title Font Size (e.g., 24px):</label><br><input type="text" id="cm_logo_title_font_size" name="cm_logo_title_font_size" value="<?php echo esc_attr($logo_title_size); ?>" /></p>
            <p><label for="cm_logo_title_font_family">Title Font Family (e.g., Arial):</label><br><input type="text" id="cm_logo_title_font_family" name="cm_logo_title_font_family" value="<?php echo esc_attr($logo_title_family); ?>" /></p>
            <p><label for="cm_logo_title_font_weight">Title Font Weight:</label><br><?php $current_weight = $logo_title_weight; ?><select id="cm_logo_title_font_weight" name="cm_logo_title_font_weight">
                <option value="normal" <?php selected($current_weight, 'normal'); ?>>Normal</option>
                <option value="bold" <?php selected($current_weight, 'bold'); ?>>Bold</option>
                <option value="100" <?php selected($current_weight, '100'); ?>>100 (Thin)</option>
                <option value="200" <?php selected($current_weight, '200'); ?>>200</option>
                <option value="300" <?php selected($current_weight, '300'); ?>>300 (Light)</option>
                <option value="400" <?php selected($current_weight, '400'); ?>>400 (Normal)</option>
                <option value="500" <?php selected($current_weight, '500'); ?>>500</option>
                <option value="600" <?php selected($current_weight, '600'); ?>>600 (Semi-bold)</option>
                <option value="700" <?php selected($current_weight, '700'); ?>>700 (Bold)</option>
                <option value="800" <?php selected($current_weight, '800'); ?>>800</option>
                <option value="900" <?php selected($current_weight, '900'); ?>>900 (Black)</option>
            </select></p>
            <p><label for="cm_logo_title_color">Title Font Color:</label><br><input type="color" id="cm_logo_title_color" name="cm_logo_title_color" value="<?php echo esc_attr($logo_title_color); ?>" /></p>
            <p><label for="cm_logo_title_padding">Title Padding from Logo (e.g., 10px):</label><br><input type="text" id="cm_logo_title_padding" name="cm_logo_title_padding" value="<?php echo esc_attr($logo_title_padding); ?>" /></p>
        </div>

        <div id="tab-sub-logo-text" class="tab-content">
            <h4>Sub-logo Text Lines</h4>
            <div id="cm-sub-logo-texts-wrapper">
                <?php
                if (!empty($sub_logo_texts) && is_array($sub_logo_texts)) {
                    foreach ($sub_logo_texts as $index => $text_item) {
                        ?>
                        <div class="cm-repeater-item">
                            <button type="button" class="button button-link-delete cm-remove-repeater-item">Remove</button>
                            <p><label>Text Content:</label><br><input type="text" name="cm_sub_logo_texts[<?php echo $index; ?>][text]" value="<?php echo esc_attr($text_item['text'] ?? ''); ?>" style="width:100%;" /></p>
                            <p><label>Font Family:</label><br><input type="text" name="cm_sub_logo_texts[<?php echo $index; ?>][font_family]" value="<?php echo esc_attr($text_item['font_family'] ?? ''); ?>" /></p>
                            <p><label>Font Size:</label><br><input type="text" name="cm_sub_logo_texts[<?php echo $index; ?>][font_size]" value="<?php echo esc_attr($text_item['font_size'] ?? ''); ?>" /></p>
                            <p><label>Font Weight:</label><br><input type="text" name="cm_sub_logo_texts[<?php echo $index; ?>][font_weight]" value="<?php echo esc_attr($text_item['font_weight'] ?? ''); ?>" /></p>
                            <p><label>Font Color:</label><br><input type="color" name="cm_sub_logo_texts[<?php echo $index; ?>][font_color]" value="<?php echo esc_attr($text_item['font_color'] ?? ''); ?>" /></p>
                            <p>
                                <label>Alignment:</label><br>
                                <select name="cm_sub_logo_texts[<?php echo $index; ?>][alignment]">
                                    <option value="left" <?php selected($text_item['alignment'] ?? '', 'left'); ?>>Left</option>
                                    <option value="center" <?php selected($text_item['alignment'] ?? '', 'center'); ?>>Center</option>
                                    <option value="right" <?php selected($text_item['alignment'] ?? '', 'right'); ?>>Right</option>
                                </select>
                            </p>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" id="cm-add-repeater-item" class="button">Add Text Line</button>
        </div>

        <div id="tab-background" class="tab-content">
            <h4>Background Settings</h4>
            <p><label for="cm_background">Background Image:</label><br><input type="text" id="cm_background" name="cm_background" value="<?php echo esc_url($background); ?>" style="width:70%;" /> <button type="button" id="upload_background_button" class="button">Upload Image</button></p>
            <p><label for="cm_background_style">Background Style:</label><br><select name="cm_background_style" id="cm_background_style">
                <option value="fill" <?php selected($background_style, 'fill'); ?>>Fill</option>
                <option value="stretch" <?php selected($background_style, 'stretch'); ?>>Stretch</option>
                <option value="repeat" <?php selected($background_style, 'repeat'); ?>>Repeat</option>
                <option value="center" <?php selected($background_style, 'center'); ?>>Center</option>
            </select></p>
        </div>
        
        <div id="tab-toc-style" class="tab-content">
            <h4>Table of Contents (TOC) Style</h4>
            <p><label for="cm_toc_position">TOC Position:</label><br><select name="cm_toc_position" id="cm_toc_position">
                <option value="top" <?php selected($toc_position, 'top'); ?>>Top</option>
                <option value="middle" <?php selected($toc_position, 'middle'); ?>>Middle</option>
                <option value="bottom" <?php selected($toc_position, 'bottom'); ?>>Bottom</option>
            </select></p>
            <p><label for="cm_alignment">TOC Alignment:</label><br><select name="cm_alignment" id="cm_alignment">
                <option value="left" <?php selected($alignment, 'left'); ?>>Left</option>
                <option value="center" <?php selected($alignment, 'center'); ?>>Center</option>
                <option value="right" <?php selected($alignment, 'right'); ?>>Right</option>
            </select></p>
            <p><label for="cm_toc_font_size">Font Size (e.g., 16px):</label><br><input type="text" id="cm_toc_font_size" name="cm_toc_font_size" value="<?php echo esc_attr($toc_font_size); ?>" /></p>
            <p><label for="cm_toc_font_family">Font Family (e.g., Arial):</label><br><input type="text" id="cm_toc_font_family" name="cm_toc_font_family" value="<?php echo esc_attr($toc_font_family); ?>" /></p>
            <p><label for="cm_toc_color">Font Color:</label><br><input type="color" id="cm_toc_color" name="cm_toc_color" value="<?php echo esc_attr($toc_color); ?>" /></p>
        </div>
    </div>
    <?php
}

// 6. Lưu dữ liệu từ Meta Box (ĐÃ CẬP NHẬT)
function cm_save_meta_box_data($post_id) {
    // 1. Kiểm tra Nonce, quyền và các hành động tự động của WordPress
    if (!isset($_POST['cm_details_meta_box_nonce']) || !wp_verify_nonce($_POST['cm_details_meta_box_nonce'], 'cm_save_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (wp_is_post_revision($post_id)) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }

    // --- BẮT ĐẦU QUÁ TRÌNH LƯU TRỮ AN TOÀN ---

    // 2. Xử lý lưu Mục lục (TOC)
    if (isset($_POST['cm_toc_items_hidden'])) {
        if (empty($_POST['cm_toc_items_hidden'])) {
            delete_post_meta($post_id, 'cm_toc_items');
        } else {
            $toc_items_json = stripslashes($_POST['cm_toc_items_hidden']);
            $toc_items = json_decode($toc_items_json, true);
            $sanitized_toc_items = [];
            if (is_array($toc_items)) {
                foreach ($toc_items as $item) {
                    if (is_array($item) && isset($item['doc_index']) && isset($item['name'])) {
                        $sanitized_toc_items[] = [
                            'doc_index' => intval($item['doc_index']),
                            'name'      => sanitize_text_field($item['name'])
                        ];
                    }
                }
            }
            update_post_meta($post_id, 'cm_toc_items', $sanitized_toc_items);
        }
    }

    // 3. Xử lý lưu Repeater Text (Sub-logo Texts) - (PHẦN BỊ THIẾU TRƯỚC ĐÂY)
    if (isset($_POST['cm_sub_logo_texts']) && is_array($_POST['cm_sub_logo_texts'])) {
        $sanitized_sub_texts = [];
        foreach ($_POST['cm_sub_logo_texts'] as $text_item) {
            $sanitized_item = [];
            $sanitized_item['text'] = isset($text_item['text']) ? sanitize_text_field($text_item['text']) : '';
            $sanitized_item['font_family'] = isset($text_item['font_family']) ? sanitize_text_field($text_item['font_family']) : '';
            $sanitized_item['font_size'] = isset($text_item['font_size']) ? sanitize_text_field($text_item['font_size']) : '';
            $sanitized_item['font_weight'] = isset($text_item['font_weight']) ? sanitize_text_field($text_item['font_weight']) : '';
            $sanitized_item['font_color'] = isset($text_item['font_color']) ? sanitize_hex_color($text_item['font_color']) : '';
            $sanitized_item['alignment'] = isset($text_item['alignment']) ? sanitize_text_field($text_item['alignment']) : 'left';
            $sanitized_sub_texts[] = $sanitized_item;
        }
        update_post_meta($post_id, 'cm_sub_logo_texts', $sanitized_sub_texts);
    } else {
        delete_post_meta($post_id, 'cm_sub_logo_texts');
    }

    // 4. Xử lý lưu danh sách tài liệu
    if (isset($_POST['cm_documents']) && is_array($_POST['cm_documents'])) {
        update_post_meta($post_id, 'cm_documents', $_POST['cm_documents']);
    } else {
        delete_post_meta($post_id, 'cm_documents');
    }

    // 5. Xử lý lưu TẤT CẢ các trường cài đặt đơn lẻ còn lại
    $single_fields = [
        'cm_time', 'cm_location',
        'cm_logo_url', 'cm_logo_position', 'cm_logo_size',
        'cm_logo_title_font_size', 'cm_logo_title_font_family', 'cm_logo_title_font_weight', 'cm_logo_title_color', 'cm_logo_title_padding',
        'cm_background', 'cm_background_style',
        'cm_alignment', 'cm_toc_position', 'cm_toc_font_size', 'cm_toc_font_family', 'cm_toc_color'
    ];
    foreach ($single_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'cm_save_meta_box_data');

// 7. Enqueue scripts và styles cho trang admin
function cm_enqueue_admin_scripts($hook) {
    global $post_type;
    if (('post.php' == $hook || 'post-new.php' == $hook) && 'conference' == $post_type) {
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        // Enqueue jQuery UI Sortable
        wp_enqueue_script('jquery-ui-sortable');
        // THÊM DÒNG NÀY ĐỂ TẢI FILE CSS
        wp_enqueue_style('cm-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
        // Enqueue plugin's custom script
        wp_enqueue_script(
            'cm-admin-script',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            '1.1',
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'cm_enqueue_admin_scripts');

// 8. Tải template cho trang frontend
function cm_load_conference_template($template) {
    global $post;
    if ($post->post_type == 'conference' && is_singular()) {
        $plugin_template = plugin_dir_path(__FILE__) . 'single-conference.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'cm_load_conference_template');

?>