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
            updateHiddenTocField();
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

    $("#toc-builder-list").sortable({ handle: ".handle", stop: function() { updateHiddenTocField(); } }).disableSelection();
    $('#toc-builder-wrapper').on('change keyup', 'input[type="text"]', function() { updateHiddenTocField(); });
    $('#toc-builder-list').on('click', '.remove-toc-item', function() { if (confirm('Bạn có chắc muốn xóa mục này khỏi mục lục?')) { $(this).closest('li').remove(); updateHiddenTocField(); } });
    
    // --- BỔ SUNG LẠI LOGIC CÒN THIẾU ---
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
    // --- KẾT THÚC BỔ SUNG ---

    $('#confirm-add-document-btn').on('click', function() {
        var selectedIndex = $('#document-selector').val();
        if (!selectedIndex) { alert('Vui lòng chọn một tài liệu.'); return; }
        var docName = $('#cm-documents-list li').eq(selectedIndex).find('span').first().text();
        var newItem = '<li data-doc-index="' + selectedIndex + '"><span class="dashicons dashicons-menu handle"></span><input type="text" value="' + docName + '" placeholder="' + docName + '"><button type="button" class="button button-link-delete remove-toc-item"><span class="dashicons dashicons-trash"></span></button></li>';
        $('#toc-builder-list').append(newItem);
        $('#document-selector-wrapper').slideUp();
        updateHiddenTocField();
    });
    $('#cancel-add-document-btn').on('click', function() { $('#document-selector-wrapper').slideUp(); });


    // -------------------------------------------------------------------------
    // ---- PHẦN 5: REPEATER CHO SUB-LOGO TEXTS ----
    // -------------------------------------------------------------------------
    $('#cm-add-repeater-item').on('click', function() {
        var wrapper = $('#cm-sub-logo-texts-wrapper');
        var index = wrapper.find('.cm-repeater-item').length;
        var fontSelectHTML = $('#cm_logo_title_font_family').clone().prop('outerHTML');
        fontSelectHTML = fontSelectHTML
            .replace('name="cm_logo_title_font_family"', 'name="cm_sub_logo_texts[' + index + '][font_family]"')
            .replace('id="cm_logo_title_font_family"', 'id="cm_sub_logo_texts_' + index + '_font_family"');
        // var newItemHTML =
        //     '<div class="cm-repeater-item" style="border: 1px dashed #ccc; padding: 10px; margin-top: 10px; position: relative;">' +
        //         '<button type="button" class="button button-link-delete cm-remove-repeater-item" style="position: absolute; top: 5px; right: 5px; color: #a00;">Remove</button>' +
        //         '<p><label><strong>Text Content:</strong></label><br><input type="text" name="cm_sub_logo_texts[' + index + '][text]" style="width:100%;" /></p>' +
        //         '<div style="display: flex; gap: 10px; align-items: flex-end;">' +
        //             '<p style="flex:2;"><label>Font Family:</label><br>' + fontSelectHTML + '</p>' +
        //             '<p style="flex:1;"><label>Font Size:</label><br><input type="text" name="cm_sub_logo_texts[' + index + '][font_size]" /></p>' +
        //             '<p style="flex:1;"><label>Font Weight:</label><br><input type="text" name="cm_sub_logo_texts[' + index + '][font_weight]" /></p>' +
        //             '<p style="flex:1;"><label>Font Color:</label><br><input type="color" name="cm_sub_logo_texts[' + index + '][font_color]" /></p>' +
        //             '<p style="flex:1;"><label>Alignment:</label><br>' +
        //                 '<select name="cm_sub_logo_texts[' + index + '][alignment]">' +
        //                     '<option value="left">Left</option>' +
        //                     '<option value="center">Center</option>' +
        //                     '<option value="right">Right</option>' +
        //                 '</select>' +
        //             '</p>' +
        //         '</div>' +
        //     '</div>';
        var newItemHTML =
    '<div class="cm-repeater-item" style="border: 1px dashed #ccc; padding: 10px; margin-top: 10px; position: relative;">' +
        '<button type="button" class="button button-link-delete cm-remove-repeater-item" style="position: absolute; top: 5px; right: 5px; color: #a00;">Remove</button>' +
        '<p><label><strong>Text Content:</strong></label><br><input type="text" name="cm_sub_logo_texts[' + index + '][text]" style="width:100%;" /></p>' +
        
        // BẮT ĐẦU KHỐI HIỂN THỊ TRÊN CÙNG 1 HÀNG
        '<div style="display: flex; gap: 15px; align-items: flex-end;">' +
            '<p style="flex:2; margin:0;"><label>Font Family:</label><br>' + fontSelectHTML + '</p>' +
            '<p style="flex:1; margin:0;"><label>Font Size:</label><br><input type="text" name="cm_sub_logo_texts[' + index + '][font_size]" style="width:100%;" /></p>' +
            '<p style="flex:1; margin:0;"><label>Font Weight:</label><br><input type="text" name="cm_sub_logo_texts[' + index + '][font_weight]" style="width:100%;" /></p>' +
            '<p style="flex:1; margin:0;"><label>Font Color:</label><br><input type="color" name="cm_sub_logo_texts[' + index + '][font_color]" style="width:100%;" /></p>' +
            '<p style="flex:1; margin:0;"><label>Alignment:</label><br>' +
                '<select name="cm_sub_logo_texts[' + index + '][alignment]" style="width:100%;">' +
                    '<option value="left">Left</option>' +
                    '<option value="center" selected>Center</option>' + // Mặc định là center
                    '<option value="right">Right</option>' +
                '</select>' +
            '</p>' +
        '</div>' +
        // KẾT THÚC KHỐI
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
     $(document).on('click', '.editor-post-publish-button, .editor-post-save-draft', function() {
        // Gọi hàm update ngay trước khi WordPress thực hiện hành động lưu.
        updateHiddenTocField();
    });

    // -------------------------------------------------------------------------
    // ---- PHẦN 7: TẠO VÀ TẢI MÃ QR CODE ----
    // -------------------------------------------------------------------------

    // Kiểm tra xem có phần tử để chứa QR code không
    if ($('#qrcode-container').length > 0) {
        // Lấy đường dẫn trang từ biến cm_data mà PHP đã truyền sang
        var pageUrl = cm_data.page_url;

        // Tạo mã QR
        var qrcode = new QRCode(document.getElementById("qrcode-container"), {
            text: pageUrl,
            width: 256,
            height: 256,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Xử lý nút Download
        $('#download-qr-btn').on('click', function(e) {
            e.preventDefault();
            // Tìm thẻ <img> bên trong container mã QR
            var qrImage = $('#qrcode-container').find('img').attr('src');
            if (qrImage) {
                // Gán đường dẫn ảnh vào nút download và tự động click
                var link = document.createElement('a');
                link.href = qrImage;
                link.download = 'conference-qrcode.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    }

});