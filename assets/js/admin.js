// Sử dụng trình bao bọc an toàn của WordPress
// Toàn bộ code sẽ nằm trong hàm này, cho phép chúng ta sử dụng ký hiệu `$` một cách an toàn.
jQuery(document).ready(function($) {

    // --- PHẦN LOGIC CHO TABS ---
    $('.cm-tabs-container .nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.cm-tabs-container .nav-tab').removeClass('nav-tab-active');
        $('.cm-tabs-container .tab-content').removeClass('active');
        $(this).addClass('nav-tab-active');
        var activeTab = $(this).attr('href');
        $(activeTab).addClass('active');
    });
    // Kích hoạt tab đầu tiên khi tải trang
    $('.cm-tabs-container .tab-content').first().addClass('active');

    // -------------------------------------------------------------------------
    // ---- PHẦN 1: UPLOAD VÀ QUẢN LÝ TÀI LIỆU ----
    // -------------------------------------------------------------------------
    var file_frame;
    $('#upload_document_button').on('click', function(event) {
        event.preventDefault();
        if (file_frame) {
            file_frame.open();
            return;
        }
        file_frame = wp.media({
            title: 'Chọn hoặc Tải lên Tài liệu',
            button: { text: 'Sử dụng tài liệu này' },
            multiple: true
        });
        file_frame.on('select', function() {
            var attachments = file_frame.state().get('selection').toJSON();
            attachments.forEach(function(attachment) {
                var docIndex = $('#cm-documents-list li').length;
                var listItem = '<li>' +
                    '<input type="hidden" name="cm_documents[' + docIndex + '][name]" value="' + attachment.title + '">' +
                    '<input type="hidden" name="cm_documents[' + docIndex + '][url]" value="' + attachment.url + '">' +
                    '<span>' + attachment.title + '</span>' +
                    ' (<a href="' + attachment.url + '" target="_blank">Xem</a>)' +
                    ' <button type="button" class="button button-link-delete remove-document">Xóa</button>' +
                    '</li>';
                $('#cm-documents-list').append(listItem);
            });
        });
        file_frame.open();
    });

    $('#cm-documents-list').on('click', '.remove-document', function() {
        if (confirm('Bạn có chắc muốn xóa tài liệu này?')) {
            var docIndexToRemove = $(this).closest('li').index();
            $(this).parent('li').remove();
            $('#toc-builder-list li[data-doc-index="' + docIndexToRemove + '"]').remove();
            $('#cm-documents-list li').each(function(newIndex) {
                var oldIndex = $(this).find('input[name^="cm_documents"]').attr('name').match(/\[(\d+)\]/)[1];
                if (oldIndex != newIndex) {
                    $(this).find('input[name^="cm_documents"]').each(function() {
                        $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, '[' + newIndex + ']'));
                    });
                    $('#toc-builder-list li[data-doc-index="' + oldIndex + '"]').attr('data-doc-index', newIndex);
                }
            });
            // Không cần gọi updateHiddenTocField() ở đây nữa
        }
    });

    // -------------------------------------------------------------------------
    // ---- PHẦN 2: UPLOAD BACKGROUND ----
    // -------------------------------------------------------------------------
    $('#upload_background_button').on('click', function(e) {
        e.preventDefault();
        var image_frame;
        if (image_frame) { image_frame.open(); return; }
        image_frame = wp.media({
            title: 'Chọn ảnh nền',
            multiple: false,
            library: { type: 'image' }
        });
        image_frame.on('select', function() {
            $('#cm_background').val(image_frame.state().get('selection').first().toJSON().url);
        });
        image_frame.open();
    });

    // -------------------------------------------------------------------------
    // ---- PHẦN 3: UPLOAD LOGO ----
    // -------------------------------------------------------------------------
    $('#upload_logo_button').on('click', function(e) {
        e.preventDefault();
        var logo_frame;
        if (logo_frame) { logo_frame.open(); return; }
        logo_frame = wp.media({
            title: 'Chọn hoặc Tải lên Logo',
            multiple: false,
            library: { type: 'image' }
        });
        logo_frame.on('select', function() {
            var media_attachment = logo_frame.state().get('selection').first().toJSON();
            $('#cm_logo_url').val(media_attachment.url);
        });
        logo_frame.open();
    });

    // -------------------------------------------------------------------------
    // ---- PHẦN 4: XÂY DỰNG MỤC LỤC (TOC) ----
    // -------------------------------------------------------------------------
    function updateHiddenTocField() {
        var tocItems = [];
        $('#toc-builder-list li').each(function() {
            tocItems.push({
                doc_index: $(this).data('doc-index'),
                name: $(this).find('input[type="text"]').val()
            });
        });
        $('#cm_toc_items_hidden').val(JSON.stringify(tocItems));
    }

    // Các sự kiện chỉ cần xử lý giao diện, không cần gọi updateHiddenTocField()
    $("#toc-builder-list").sortable({ handle: ".handle" }).disableSelection();
    $('#toc-builder-list').on('click', '.remove-toc-item', function() { if (confirm('Bạn có chắc muốn xóa mục này khỏi mục lục?')) { $(this).closest('li').remove(); } });
    $('#confirm-add-document-btn').on('click', function() {
        var selectedIndex = $('#document-selector').val();
        if (!selectedIndex) { alert('Vui lòng chọn một tài liệu.'); return; }
        var docName = $('#cm-documents-list li').eq(selectedIndex).find('span').first().text();
        var newItem = '<li data-doc-index="' + selectedIndex + '"><span class="dashicons dashicons-menu handle"></span><input type="text" value="' + docName + '" placeholder="' + docName + '"><button type="button" class="button button-link-delete remove-toc-item"><span class="dashicons dashicons-trash"></span></button></li>';
        $('#toc-builder-list').append(newItem);
        $('#document-selector-wrapper').slideUp();
    });
    $('#add-to-toc-btn').on('click', function() {
        var selector = $('#document-selector');
        selector.empty().append('<option value="">-- Chọn một tài liệu --</option>');
        var availableDocs = [];
        $('#cm-documents-list li').each(function(index) { availableDocs.push({ index: index, name: $(this).find('span').first().text() }); });
        if (availableDocs.length === 0) { alert('Vui lòng upload tài liệu trước.'); return; }
        var currentTocDocIndexes = [];
        $('#toc-builder-list li').each(function() { currentTocDocIndexes.push($(this).data('doc-index')); });
        var added = false;
        availableDocs.forEach(function(doc) {
            if ($.inArray(doc.index, currentTocDocIndexes) === -1) {
                selector.append('<option value="' + doc.index + '">' + doc.name + '</option>');
                added = true;
            }
        });
        if (!added) { alert('Tất cả tài liệu đã được thêm.'); return; }
        $('#document-selector-wrapper').slideDown();
    });
    $('#cancel-add-document-btn').on('click', function() { $('#document-selector-wrapper').slideUp(); });

    // -------------------------------------------------------------------------
    // ---- PHẦN 5: REPEATER CHO SUB-LOGO TEXTS ----
    // -------------------------------------------------------------------------
    $('#cm-add-repeater-item').on('click', function() {
        var wrapper = $('#cm-sub-logo-texts-wrapper');
        var index = wrapper.find('.cm-repeater-item').length;

        var newItemHTML =
            '<div class="cm-repeater-item" style="border: 1px dashed #ccc; padding: 10px; margin-top: 10px;">' +
            '<button type="button" class="button button-link-delete cm-remove-repeater-item" style="float: right; color: #a00;">Remove</button>' +
            '<p><label><strong>Text Content:</strong></label><br><input type="text" name="cm_sub_logo_texts[' + index + '][text]" style="width:100%;" /></p>' +
            '<div style="display: flex; gap: 10px;">' +
            '<p style="flex:2;"><label>Font Family:</label><br><input type="text" name="cm_sub_logo_texts[' + index + '][font_family]" /></p>' +
            '<p style="flex:1;"><label>Font Size:</label><br><input type="text" name="cm_sub_logo_texts[' + index + '][font_size]" /></p>' +
            '<p style="flex:1;"><label>Font Weight:</label><br><input type="text" name="cm_sub_logo_texts[' + index + '][font_weight]" /></p>' +
            '<p style="flex:1;"><label>Font Color:</label><br><input type="color" name="cm_sub_logo_texts[' + index + '][font_color]" /></p>' +
            '<p style="flex:1;"><label>Alignment:</label><br>' +
            '<select name="cm_sub_logo_texts[' + index + '][alignment]">' +
            '<option value="left">Left</option>' +
            '<option value="center">Center</option>' +
            '<option value="right">Right</option>' +
            '</select>' +
            '</p>' +
            '</div>' +
            '</div>';

        wrapper.append(newItemHTML);
    });

    $('#cm-sub-logo-texts-wrapper').on('click', '.cm-remove-repeater-item', function() {
        if (confirm('Are you sure you want to remove this text line?')) {
            $(this).closest('.cm-repeater-item').remove();
            $('#cm-sub-logo-texts-wrapper .cm-repeater-item').each(function(i) {
                $(this).find('input, select').each(function() {
                    var oldName = $(this).attr('name');
                    if (oldName) {
                        var newName = oldName.replace(/\[\d+\]/, '[' + i + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        }
    });
    
    // -------------------------------------------------------------------------
    // ---- PHẦN 6: CẬP NHẬT TRƯỚC KHI LƯU (QUAN TRỌNG NHẤT) ----
    // -------------------------------------------------------------------------
    $('form#post').on('submit', function() {
        // Gọi hàm update ngay trước khi form được gửi đi.
        updateHiddenTocField();
    });

});