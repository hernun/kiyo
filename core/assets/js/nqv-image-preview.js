$(function(){
    const images = $('img').filter('modalized');

    $('body').on({
        click:function(){
            const src = $(this).data('src') ? $(this).data('src'):$(this).attr('src');
            let previewContainer = $('#image-preview');
            if(!previewContainer.length) {
                previewContainer = $('<div id="image-preview-container" />');
                previewBody = $('<div id="image-preview-body" />');
                previewHeader = $('<div id="image-preview-header" />');
                previewClose = $('<div id="close" />').text('X');
                $('<img id="image-preview" />').appendTo(previewBody);
                
                previewClose.appendTo(previewHeader);
                previewHeader.appendTo(previewContainer);
                previewBody.appendTo(previewContainer);
                previewContainer.appendTo('body');
            }
            const image = $('#image-preview');

            waitin.show();
            image.hide();

            image.attr('src',src);
            // Cargar imagen
            image.attr('src', src).off('load').on('load', function() {
                setTimeout(function(){
                    waitin.hide();
                    image.fadeIn(100);
                },1000)
            });
        }
    },'img.modalized');

    $('body').on({
        click: function() {
            $('#image-preview-container').fadeOut(function(){
                $(this).remove();
            })
        }
    },'#close')
})