<?php
/*
Plugin Name: Conference Manager
Description: Plugin to manage conferences, documents, and custom conference pages.
Version: 1.0.0
Author: Your Name
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Đăng ký Custom Post Type
function cm_register_conference_cpt() {
    $labels = array(
        'name'               => __('Conferences', 'conference-manager'),
        'singular_name'      => __('Conference', 'conference-manager'),
        'menu_name'          => __('Conferences', 'conference-manager'),
        'add_new'            => __('Add New', 'conference-manager'),
        'add_new_item'       => __('Add New Conference', 'conference-manager'),
        'edit_item'          => __('Edit Conference', 'conference-manager'),
        'new_item'           => __('New Conference', 'conference-manager'),
        'view_item'          => __('View Conference', 'conference-manager'),
        'search_items'       => __('Search Conferences', 'conference-manager'),
        'not_found'          => __('No conferences found', 'conference-manager'),
        'not_found_in_trash' => __('No conferences found in Trash', 'conference-manager'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'conference'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array('title', 'editor', 'thumbnail'),
        'show_in_rest'       => true,
    );

    register_post_type('conference', $args);
}
add_action('init', 'cm_register_conference_cpt');

// Đăng ký custom fields cho thông tin hội nghị
function cm_register_conference_meta() {
    register_post_meta('conference', 'cm_time', array(
        'type'         => 'string',
        'description'  => __('Conference Time', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
    ));

    register_post_meta('conference', 'cm_location', array(
        'type'         => 'string',
        'description'  => __('Conference Location', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
    ));

    register_post_meta('conference', 'cm_description', array(
        'type'         => 'string',
        'description'  => __('Conference Description', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'cm_register_conference_meta');

// Đăng ký meta cho danh sách tài liệu
function cm_register_documents_meta() {
    register_post_meta('conference', 'cm_documents', array(
        'type'         => 'array',
        'description'  => __('Conference Documents', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => [],
    ));
}
add_action('init', 'cm_register_documents_meta');

// Đăng ký meta cho thiết lập trang hội nghị (bao gồm mục lục)
function cm_register_page_settings_meta() {
    register_post_meta('conference', 'cm_toc_order', array(
        'type'         => 'array',
        'description'  => __('Table of Contents Order', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => [],
    ));

    register_post_meta('conference', 'cm_toc_names', array(
        'type'         => 'array',
        'description'  => __('Table of Contents Names', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => [],
    ));

    register_post_meta('conference', 'cm_background', array(
        'type'         => 'string',
        'description'  => __('Conference Page Background', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
    ));

    register_post_meta('conference', 'cm_background_style', array(
        'type'         => 'string',
        'description'  => __('Background Style', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => 'fill',
    ));

    register_post_meta('conference', 'cm_alignment', array(
        'type'         => 'string',
        'description'  => __('Table of Contents Alignment', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => 'center',
    ));

    register_post_meta('conference', 'cm_toc_position', array(
        'type'         => 'string',
        'description'  => __('Table of Contents Position', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => 'middle',
    ));

    register_post_meta('conference', 'cm_toc_font_size', array(
        'type'         => 'string',
        'description'  => __('Table of Contents Font Size', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => '16px',
    ));

    register_post_meta('conference', 'cm_toc_font_family', array(
        'type'         => 'string',
        'description'  => __('Table of Contents Font Family', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => 'Arial',
    ));

    register_post_meta('conference', 'cm_toc_color', array(
        'type'         => 'string',
        'description'  => __('Table of Contents Color', 'conference-manager'),
        'single'       => true,
        'show_in_rest' => true,
        'default'      => '#000000',
    ));
}
add_action('init', 'cm_register_page_settings_meta');

// Tạo metabox cho thông tin hội nghị
function cm_add_conference_metabox() {
    add_meta_box(
        'cm_conference_details',
        __('Conference Details', 'conference-manager'),
        'cm_conference_details_callback',
        'conference',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cm_add_conference_metabox');

// Callback để hiển thị metabox thông tin hội nghị
function cm_conference_details_callback($post) {
    wp_nonce_field('cm_save_conference_details', 'cm_conference_nonce');
    $time = get_post_meta($post->ID, 'cm_time', true);
    $location = get_post_meta($post->ID, 'cm_location', true);
    $description = get_post_meta($post->ID, 'cm_description', true);
    ?>
    <p>
        <label for="cm_time"><?php _e('Time', 'conference-manager'); ?></label><br>
        <input type="text" id="cm_time" name="cm_time" value="<?php echo esc_attr($time); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cm_location"><?php _e('Location', 'conference-manager'); ?></label><br>
        <input type="text" id="cm_location" name="cm_location" value="<?php echo esc_attr($location); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cm_description"><?php _e('Description', 'conference-manager'); ?></label><br>
        <textarea id="cm_description" name="cm_description" style="width: 100%; height: 100px;"><?php echo esc_textarea($description); ?></textarea>
    </p>
    <?php
}

// Lưu dữ liệu thông tin hội nghị
function cm_save_conference_details($post_id) {
    if (!isset($_POST['cm_conference_nonce']) || !wp_verify_nonce($_POST['cm_conference_nonce'], 'cm_save_conference_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['cm_time'])) {
        update_post_meta($post_id, 'cm_time', sanitize_text_field($_POST['cm_time']));
    }
    if (isset($_POST['cm_location'])) {
        update_post_meta($post_id, 'cm_location', sanitize_text_field($_POST['cm_location']));
    }
    if (isset($_POST['cm_description'])) {
        update_post_meta($post_id, 'cm_description', sanitize_textarea_field($_POST['cm_description']));
    }
}
add_action('save_post', 'cm_save_conference_details');

// Tạo metabox cho tài liệu
function cm_add_documents_metabox() {
    add_meta_box(
        'cm_conference_documents',
        __('Conference Documents', 'conference-manager'),
        'cm_conference_documents_callback',
        'conference',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cm_add_documents_metabox');

// Callback để hiển thị metabox tài liệu
function cm_conference_documents_callback($post) {
    wp_nonce_field('cm_save_conference_documents', 'cm_documents_nonce');
    $documents = get_post_meta($post->ID, 'cm_documents', true);
    if (!is_array($documents)) {
        $documents = [];
    }
    ?>
    <div id="cm-documents-manager">
        <p>
            <button type="button" class="button" id="cm-upload-document"><?php _e('Upload Document', 'conference-manager'); ?></button>
        </p>
        <ul id="cm-documents-list" style="list-style: none; padding: 0;">
            <?php foreach ($documents as $index => $doc): ?>
                <li class="cm-document-item" data-index="<?php echo $index; ?>">
                    <input type="hidden" name="cm_documents[<?php echo $index; ?>][url]" value="<?php echo esc_attr($doc['url']); ?>">
                    <input type="text" name="cm_documents[<?php echo $index; ?>][name]" value="<?php echo esc_attr($doc['name']); ?>" placeholder="<?php _e('Document Name', 'conference-manager'); ?>" style="width: 50%;">
                    <span><?php echo esc_html($doc['type']); ?></span>
                    <a href="#" class="cm-remove-document button" style="color: red;"><?php _e('Remove', 'conference-manager'); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

// Lưu dữ liệu tài liệu
function cm_save_conference_documents($post_id) {
    if (!isset($_POST['cm_documents_nonce']) || !wp_verify_nonce($_POST['cm_documents_nonce'], 'cm_save_conference_documents')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['cm_documents']) && is_array($_POST['cm_documents'])) {
        $documents = [];
        foreach ($_POST['cm_documents'] as $doc) {
            if (!empty($doc['url']) && !empty($doc['name'])) {
                $documents[] = [
                    'url'  => esc_url_raw($doc['url']),
                    'name' => sanitize_text_field($doc['name']),
                    'type' => wp_check_filetype(basename($doc['url']))['ext'],
                ];
            }
        }
        update_post_meta($post_id, 'cm_documents', $documents);
    } else {
        delete_post_meta($post_id, 'cm_documents');
    }
}
add_action('save_post', 'cm_save_conference_documents');

// Tạo metabox cho thiết lập trang hội nghị (bao gồm mục lục)
function cm_add_page_settings_metabox() {
    add_meta_box(
        'cm_conference_page_settings',
        __('Conference Page Settings', 'conference-manager'),
        'cm_conference_page_settings_callback',
        'conference',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cm_add_page_settings_metabox');

// Callback để hiển thị metabox thiết lập trang với mục lục và tùy chỉnh
function cm_conference_page_settings_callback($post) {
    wp_nonce_field('cm_save_page_settings', 'cm_page_settings_nonce');
    $toc_order = get_post_meta($post->ID, 'cm_toc_order', true);
    if (!is_array($toc_order)) {
        $toc_order = [];
    }
    $toc_names = get_post_meta($post->ID, 'cm_toc_names', true);
    if (!is_array($toc_names)) {
        $toc_names = [];
    }
    $background = get_post_meta($post->ID, 'cm_background', true);
    $background_style = get_post_meta($post->ID, 'cm_background_style', true);
    $alignment = get_post_meta($post->ID, 'cm_alignment', true);
    $toc_position = get_post_meta($post->ID, 'cm_toc_position', true);
    $toc_font_size = get_post_meta($post->ID, 'cm_toc_font_size', true);
    $toc_font_family = get_post_meta($post->ID, 'cm_toc_font_family', true);
    $toc_color = get_post_meta($post->ID, 'cm_toc_color', true);
    $documents = get_post_meta($post->ID, 'cm_documents', true);
    if (!is_array($documents)) {
        $documents = [];
    }
    ?>
    <div id="cm-page-settings">
        <h4><?php _e('Table of Contents', 'conference-manager'); ?></h4>
        <p><?php _e('Drag and drop to reorder the table of contents.', 'conference-manager'); ?></p>
        <ul id="cm-toc-list" style="list-style: none; padding: 0;">
            <?php foreach ($toc_order as $index => $doc_index): ?>
                <?php if (isset($documents[$doc_index])): ?>
                    <li class="cm-toc-item" data-index="<?php echo $index; ?>">
                        <input type="hidden" name="cm_toc_order[<?php echo $index; ?>]" value="<?php echo esc_attr($doc_index); ?>">
                        <input type="text" name="cm_toc_names[<?php echo $index; ?>]" value="<?php echo esc_attr(isset($toc_names[$index]) ? $toc_names[$index] : $documents[$doc_index]['name']); ?>" placeholder="<?php _e('Custom Name', 'conference-manager'); ?>" style="width: 50%;">
                        <span><?php echo esc_html($documents[$doc_index]['type']); ?></span>
                        <a href="#" class="cm-remove-toc button" style="color: red;"><?php _e('Remove', 'conference-manager'); ?></a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <p>
            <button type="button" class="button" id="cm-add-toc-item"><?php _e('Add to Table of Contents', 'conference-manager'); ?></button>
        </p>

        <h4><?php _e('Background Image', 'conference-manager'); ?></h4>
        <p>
            <button type="button" class="button" id="cm-upload-background"><?php _e('Upload Background', 'conference-manager'); ?></button>
            <input type="hidden" id="cm_background" name="cm_background" value="<?php echo esc_attr($background); ?>">
            <?php if ($background): ?>
                <img src="<?php echo esc_url($background); ?>" style="max-width: 200px; display: block; margin-top: 10px;">
            <?php endif; ?>
        </p>

        <h4><?php _e('Background Style', 'conference-manager'); ?></h4>
        <p>
            <select name="cm_background_style">
                <option value="fill" <?php selected($background_style, 'fill'); ?>><?php _e('Fill', 'conference-manager'); ?></option>
                <option value="stretch" <?php selected($background_style, 'stretch'); ?>><?php _e('Stretch', 'conference-manager'); ?></option>
                <option value="repeat" <?php selected($background_style, 'repeat'); ?>><?php _e('Repeat', 'conference-manager'); ?></option>
                <option value="center" <?php selected($background_style, 'center'); ?>><?php _e('Center', 'conference-manager'); ?></option>
            </select>
        </p>

        <h4><?php _e('Table of Contents Alignment', 'conference-manager'); ?></h4>
        <p>
            <select name="cm_alignment">
                <option value="left" <?php selected($alignment, 'left'); ?>><?php _e('Left', 'conference-manager'); ?></option>
                <option value="center" <?php selected($alignment, 'center'); ?>><?php _e('Center', 'conference-manager'); ?></option>
                <option value="right" <?php selected($alignment, 'right'); ?>><?php _e('Right', 'conference-manager'); ?></option>
            </select>
        </p>

        <h4><?php _e('Table of Contents Position', 'conference-manager'); ?></h4>
        <p>
            <select name="cm_toc_position">
                <option value="top" <?php selected($toc_position, 'top'); ?>><?php _e('Top', 'conference-manager'); ?></option>
                <option value="middle" <?php selected($toc_position, 'middle'); ?>><?php _e('Middle', 'conference-manager'); ?></option>
                <option value="bottom" <?php selected($toc_position, 'bottom'); ?>><?php _e('Bottom', 'conference-manager'); ?></option>
            </select>
        </p>

        <h4><?php _e('Table of Contents Font Settings', 'conference-manager'); ?></h4>
        <p>
            <label for="cm_toc_font_size"><?php _e('Font Size', 'conference-manager'); ?></label><br>
            <input type="text" id="cm_toc_font_size" name="cm_toc_font_size" value="<?php echo esc_attr($toc_font_size); ?>" placeholder="e.g., 16px" style="width: 100px;">
        </p>
        <p>
            <label for="cm_toc_font_family"><?php _e('Font Family', 'conference-manager'); ?></label><br>
            <input type="text" id="cm_toc_font_family" name="cm_toc_font_family" value="<?php echo esc_attr($toc_font_family); ?>" placeholder="e.g., Arial" style="width: 200px;">
        </p>
        <p>
            <label for="cm_toc_color"><?php _e('Color', 'conference-manager'); ?></label><br>
            <input type="color" id="cm_toc_color" name="cm_toc_color" value="<?php echo esc_attr($toc_color); ?>" style="width: 100px;">
        </p>
    </div>
    <?php
}

// Lưu dữ liệu thiết lập trang (bao gồm mục lục và tùy chỉnh)
function cm_save_page_settings($post_id) {
    if (!isset($_POST['cm_page_settings_nonce']) || !wp_verify_nonce($_POST['cm_page_settings_nonce'], 'cm_save_page_settings')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Lưu thứ tự mục lục
    if (isset($_POST['cm_toc_order']) && is_array($_POST['cm_toc_order'])) {
        $toc_order = array_map('intval', array_keys($_POST['cm_toc_order'])); // Lấy chỉ số của tài liệu
        update_post_meta($post_id, 'cm_toc_order', $toc_order);
    } else {
        delete_post_meta($post_id, 'cm_toc_order');
    }

    // Lưu tên tùy chỉnh cho mục lục
    if (isset($_POST['cm_toc_names']) && is_array($_POST['cm_toc_names'])) {
        $toc_names = [];
        foreach ($_POST['cm_toc_names'] as $index => $name) {
            $toc_names[$index] = sanitize_text_field($name);
        }
        update_post_meta($post_id, 'cm_toc_names', $toc_names);
    } else {
        delete_post_meta($post_id, 'cm_toc_names');
    }

    // Lưu hình nền
    if (isset($_POST['cm_background'])) {
        update_post_meta($post_id, 'cm_background', esc_url_raw($_POST['cm_background']));
    }

    // Lưu kiểu hiển thị hình nền
    if (isset($_POST['cm_background_style'])) {
        update_post_meta($post_id, 'cm_background_style', sanitize_text_field($_POST['cm_background_style']));
    }

    // Lưu căn chỉnh mục lục
    if (isset($_POST['cm_alignment'])) {
        update_post_meta($post_id, 'cm_alignment', sanitize_text_field($_POST['cm_alignment']));
    }

    // Lưu vị trí mục lục
    if (isset($_POST['cm_toc_position'])) {
        update_post_meta($post_id, 'cm_toc_position', sanitize_text_field($_POST['cm_toc_position']));
    }

    // Lưu cỡ chữ
    if (isset($_POST['cm_toc_font_size'])) {
        update_post_meta($post_id, 'cm_toc_font_size', sanitize_text_field($_POST['cm_toc_font_size']));
    }

    // Lưu font chữ
    if (isset($_POST['cm_toc_font_family'])) {
        update_post_meta($post_id, 'cm_toc_font_family', sanitize_text_field($_POST['cm_toc_font_family']));
    }

    // Lưu màu chữ
    if (isset($_POST['cm_toc_color'])) {
        update_post_meta($post_id, 'cm_toc_color', sanitize_hex_color($_POST['cm_toc_color']));
    }
}
add_action('save_post', 'cm_save_page_settings');

// Đăng ký scripts và styles
function cm_enqueue_admin_scripts($hook) {
    global $post_type;
    if ($post_type !== 'conference' || !in_array($hook, ['post.php', 'post-new.php'])) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('cm-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', ['jquery', 'jquery-ui-sortable'], '1.0.2', true);
    wp_enqueue_style('cm-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', [], '1.0.2');

    // Truyền dữ liệu documents sang JavaScript
    $documents = get_post_meta(get_the_ID(), 'cm_documents', true);
    if (!is_array($documents)) {
        $documents = [];
    }
    wp_localize_script('cm-admin-js', 'cmData', [
        'documents' => $documents,
        'nonce'     => wp_create_nonce('cm_page_settings_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'cm_enqueue_admin_scripts');

// Thêm vào cuối file conference-manager.php
function cm_enqueue_frontend_styles() {
    wp_enqueue_style('cm-frontend-css', plugin_dir_url(__FILE__) . 'assets/css/frontend.css', [], '1.0.0');
}
add_action('wp_enqueue_scripts', 'cm_enqueue_frontend_styles');

// Thêm vào cuối file conference-manager.php
function cm_enqueue_frontend_scripts() {
    wp_enqueue_script('cm-frontend-js', plugin_dir_url(__FILE__) . 'assets/js/frontend.js', ['jquery'], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'cm_enqueue_frontend_scripts');