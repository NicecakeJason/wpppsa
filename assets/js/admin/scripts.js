jQuery(document).ready(function($) {
    // Upload images
    $('.upload-gallery-images').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var customUploader = wp.media({
            title: 'Select Images',
            library: {
                type: 'image'
            },
            button: {
                text: 'Use these images'
            },
            multiple: true
        }).on('select', function() {
            var attachments = customUploader.state().get('selection').map(function(attachment) {
                attachment.toJSON();
                return attachment.id;
            });

            var galleryIds = $('#sunapartament_gallery_ids').val();
            if (galleryIds) {
                galleryIds = galleryIds.split(',');
            } else {
                galleryIds = [];
            }

            galleryIds = galleryIds.concat(attachments);
            $('#sunapartament_gallery_ids').val(galleryIds.join(','));

            var galleryHtml = '';
            attachments.forEach(function(id) {
                galleryHtml += '<li class="d-flex justify-content-between">' + wp.media.attachment(id).get('url') + ' <a href="#" class="remove-image btn btn-danger" data-image-id="' + id + '">X</a></li>';
            });

            $('.sunapartament-gallery-images').append(galleryHtml);
        }).open();
    });

    // Remove image
    $('.sunapartament-gallery-images').on('click', '.remove-image', function(e) {
        e.preventDefault();

        var imageId = $(this).data('image-id');
        var galleryIds = $('#sunapartament_gallery_ids').val().split(',');

        galleryIds = galleryIds.filter(function(id) {
            return id != imageId;
        });

        $('#sunapartament_gallery_ids').val(galleryIds.join(','));
        $(this).parent().remove();
    });
});










jQuery(document).ready(function($) {
    // Загрузка иконки
    $(document).on('click', '.upload-icon-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var target = button.data('target');

        var customUploader = wp.media({
            title: 'Выберите иконку',
            button: {
                text: 'Использовать эту иконку'
            },
            multiple: false
        }).on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            $('#' + target).val(attachment.url);
        }).open();
    });

    // Добавление нового удобства
    $('#add-amenity').on('click', function() {
        var index = $('.amenity').length;
        var html = `
        <div class="amenity">
        <div class=" d-flex justify-content-between">
            <p>
                <label class="form-label" for="sunapartament_amenity_name_${index}">Название удобства:</label>
                <input class="form-control" type="text" id="sunapartament_amenity_name_${index}" name="sunapartament_additional_amenities[${index}][name]">
            </p>
            <p  >
                <label class="form-label" for="sunapartament_amenity_icon_${index}">Иконка:</label>
                <input class="form-control mb-2" type="text" id="sunapartament_amenity_icon_${index}" name="sunapartament_additional_amenities[${index}][icon]">
                <button type="button" class="upload-icon-button btn btn-primary" data-target="sunapartament_amenity_icon_${index}">Загрузить иконку</button>
            </p>
            <p>
                <label class="form-label" for="sunapartament_amenity_category_${index}">Категория:</label>
                <select class="form-select" id="sunapartament_amenity_category_${index}" name="sunapartament_additional_amenities[${index}][category]">
                    <option value="beds">Кровати</option>
                    <option value="internet">Интернет</option>
                    <option value="furniture">Мебель</option>
                    <option value="bathroom">Ванная комната</option>
                    <option value="kitchen">Кухня</option>
                    <option value="video">Видео/аудио</option>
                    <option value="electronics">Электроника</option>
                    <option value="area">Внутренний двор и вид из окна</option>
                    <option value="other">Прочее</option>
                </select>
            </p>
            <button type="button" class="remove-amenity btn btn-danger mb-2">X</button>
        </div>
            
        </div>
        
        `;
        $('#sunapartament-additional-amenities').append(html);
    });

    // Удаление удобства
    $('#sunapartament-additional-amenities').on('click', '.remove-amenity', function() {
        $(this).closest('.amenity').remove();
    });
});




jQuery(document).ready(function($) {
    jQuery(document).ready(function($) {
        // Получаем текущий месяц (1-12)
        const month = new Date().getMonth() + 1;
    
        // Используем данные из объекта sunApartamentPrices
        const prices = sunApartamentPrices;
    
        // Обновляем текст элемента с текущей ценой
        const priceElement = $('#current-price');
        if (priceElement.length) {
            const currentPrice = prices[month] || 'Цена не указана';
            priceElement.text('Текущая цена: ' + currentPrice);
        }
    });
});





