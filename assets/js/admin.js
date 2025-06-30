jQuery(document).ready(function($) {
    // Khởi tạo Media Uploader cho tài liệu
    var documentUploader;
    $('#cm-upload-document').on('click', function(e) {
        e.preventDefault();
        if (documentUploader) {
            documentUploader.open();
            return;
        }

        documentUploader = wp.media({
            title: 'Select Document',
            button: { text: 'Select' },
            multiple: false,
            library: {
                type: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                       'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                       'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                       'image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime']
            }
        });

        documentUploader.on('select', function() {
            var attachment = documentUploader.state().get('selection').first().toJSON();
            var index = $('#cm-documents-list li').length;
            var listItem = '<li class="cm-document-item" data-index="' + index + '">' +
                           '<input type="hidden" name="cm_documents[' + index + '][url]" value="' + attachment.url + '">' +
                           '<input type="text" name="cm_documents[' + index + '][name]" placeholder="Document Name" style="width: 50%;">' +
                           '<span>' + attachment.subtype + '</span>' +
                           '<a href="#" class="cm-remove-document button" style="color: red;">Remove</a>' +
                           '</li>';
            $('#cm-documents-list').append(listItem);
        });

        documentUploader.open();
    });

    // Xóa tài liệu
    $('#cm-documents-list').on('click', '.cm-remove-document', function(e) {
        e.preventDefault();
        $(this).closest('li').remove();
    });

    // Khởi tạo Media Uploader cho hình nền
    var backgroundUploader;
    $('#cm-upload-background').on('click', function(e) {
        e.preventDefault();
        if (backgroundUploader) {
            backgroundUploader.open();
            return;
        }

        backgroundUploader = wp.media({
            title: 'Select Background Image',
            button: { text: 'Select' },
            multiple: false,
            library: { type: ['image/jpeg', 'image/png', 'image/gif'] }
        });

        backgroundUploader.on('select', function() {
            var attachment = backgroundUploader.state().get('selection').first().toJSON();
            $('#cm_background').val(attachment.url);
            $('#cm-page-settings img').remove();
            $('#cm-page-settings').append('<img src="' + attachment.url + '" style="max-width: 200px; display: block; margin-top: 10px;">');
        });

        backgroundUploader.open();
    });

    // Kéo thả mục lục
    $('#cm-toc-list').sortable({
        update: function(event, ui) {
            $('#cm-toc-list li').each(function(index) {
                $(this).find('input[name*="[name]"]').attr('name', 'cm_toc_names[' + index + ']');
                $(this).find('input[name*="[order]"]').attr('name', 'cm_toc_order[' + index + ']');
            });
        }
    });

    // Thêm mục vào mục lục
    $('#cm-add-toc-item').on('click', function(e) {
        e.preventDefault();
        var index = $('#cm-toc-list li').length;
        var documents = cmData.documents;
        if (documents.length > 0) {
            var docIndex = index % documents.length; // Lặp lại nếu hết tài liệu
            var listItem = '<li class="cm-toc-item" data-index="' + index + '">' +
                           '<input type="hidden" name="cm_toc_order[' + index + ']" value="' + docIndex + '">' +
                           '<input type="text" name="cm_toc_names[' + index + ']" value="' + documents[docIndex].name + '" placeholder="Custom Name" style="width: 50%;">' +
                           '<span>' + documents[docIndex].type + '</span>' +
                           '<a href="#" class="cm-remove-toc button" style="color: red;">Remove</a>' +
                           '</li>';
            $('#cm-toc-list').append(listItem);
        }
    });

    // Xóa mục khỏi mục lục
    $('#cm-toc-list').on('click', '.cm-remove-toc', function(e) {
        e.preventDefault();
        $(this).closest('li').remove();
    });
});