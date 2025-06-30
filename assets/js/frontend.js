jQuery(document).ready(function($) {
    $('.conference-toc a').on('click', function(e) {
        e.preventDefault();
        window.open($(this).attr('href'), '_blank');
    });

    $(window).resize(function() {
        var width = $(window).width();
        if (width <= 768) {
            $('.toc-top, .toc-bottom').css({ position: 'relative', top: 'auto', bottom: 'auto' });
        } else {
            $('.toc-top').css({ position: 'absolute', top: '16px' });
            $('.toc-bottom').css({ position: 'absolute', bottom: '16px' });
        }
    }).resize();
});