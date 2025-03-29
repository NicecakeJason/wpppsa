/**
 * JavaScript для форм бронирования SunApartament
 * 
 * Этот скрипт нужно подключить в WordPress через функцию wp_enqueue_script
 * Предварительно должны быть подключены flatpickr и его русская локализация
 */

jQuery(document).ready(function($) {
    console.log('Инициализация скрипта бронирования...');
    
    // Проверка наличия flatpickr
    if (typeof flatpickr === 'undefined') {
        console.error('Ошибка: flatpickr не загружен. Проверьте подключение библиотеки.');
        alert('Календарь не загружен. Пожалуйста, обновите страницу.');
        return;
    }
 
    // Проверка элементов формы на странице
    if ($('#checkin_date_display').length === 0 || $('#checkout_date_display').length === 0) {
        console.error('Ошибка: элементы формы не найдены на странице');
        return;
    }
 
    console.log('Подготовка календаря...');
    
    // Встроенная русская локализация для независимости от внешних файлов
    var Russian = {
        weekdays: {
            shorthand: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            longhand: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"]
        },
        months: {
            shorthand: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
            longhand: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"]
        },
        firstDayOfWeek: 1,
        ordinal: function() {
            return "";
        },
        rangeSeparator: " — ",
        weekAbbreviation: "Нед.",
        scrollTitle: "Прокрутите для увеличения",
        toggleTitle: "Нажмите для переключения",
        amPM: ["ДП", "ПП"],
        yearAriaLabel: "Год",
        time_24hr: true
    };
 
    // Проверяем, можем ли мы использовать официальную локализацию
    if (flatpickr.l10ns && flatpickr.l10ns.ru) {
        console.log('Используем официальную русскую локализацию');
        flatpickr.localize(flatpickr.l10ns.ru);
    } else if (flatpickr.l10n && flatpickr.l10n.ru) {
        console.log('Используем устаревшую официальную русскую локализацию');
        flatpickr.localize(flatpickr.l10n.ru);
    } else {
        // Используем нашу встроенную локализацию
        console.log('Используем встроенную русскую локализацию');
        flatpickr.localize(Russian);
    }
 
    // Создаем input для flatpickr прямо рядом с первым полем формы
    var $container = $('#checkin_date_display').parent();
    var $flatpickrInput = $('<input>')
        .attr({
            'type': 'text',
            'id': 'flatpickr_date_range',
            'class': 'flatpickr-input'
        })
        .css({
            'position': 'absolute',
            'opacity': '0',
            'height': '0',
            'width': '0'
        });
    
    $container.append($flatpickrInput);
    
    // Определяем, является ли устройство мобильным
    function isMobileDevice() {
        return window.innerWidth < 768;
    }
    
    // Функция для исправления отображения дней недели на мобильных устройствах
    function fixWeekdaysDisplay() {
        if (isMobileDevice()) {
            setTimeout(function() {
                // Убедимся, что контейнер дней недели имеет правильную ширину
                $('.flatpickr-weekdaycontainer').css({
                    'width': '100%',
                    'display': 'flex',
                    'justify-content': 'space-around'
                });
                
                // Установим правильный размер для каждого дня недели
                $('.flatpickr-weekday').css({
                    'width': 'calc(100% / 7)',
                    'font-size': '80%',
                    'padding': '0',
                    'margin': '0',
                    'text-align': 'center'
                });
            }, 100);
        }
    }
    
    // Инициализация календаря с принудительной локализацией
    console.log('Создание календаря flatpickr...');
    var fp = flatpickr("#flatpickr_date_range", {
        mode: "range",
        dateFormat: "d.m.Y",
        minDate: "today",
        locale: Russian, // Принудительно используем русскую локализацию
        disableMobile: true,
        showMonths: isMobileDevice() ? 1 : 2, // На мобильных показываем 1 месяц, на десктопе - 2
        monthSelectorType: "static",
        time_24hr: true,
        minRange: 7, // Минимальный период 7 ночей
        onChange: function(selectedDates, dateStr) {
            console.log('Даты изменены:', dateStr);
            if (selectedDates.length === 2) {
                var startDate = selectedDates[0];
                var endDate = selectedDates[1];
                
                // Проверка минимального периода
                var difference = Math.round((endDate - startDate) / (1000 * 60 * 60 * 24));
                console.log('Разница в днях:', difference);
                if (difference < 7) {
                    alert('Минимальный срок бронирования - 7 ночей.');
                    this.clear();
                    return;
                }
                
                // Форматирование дат
                var formatDate = function(date) {
                    var day = ('0' + date.getDate()).slice(-2);
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var year = date.getFullYear();
                    return day + '.' + month + '.' + year;
                };
                
                var checkinFormatted = formatDate(startDate);
                var checkoutFormatted = formatDate(endDate);
                
                console.log('Даты заезда/выезда:', checkinFormatted, checkoutFormatted);
                
                // Обновление полей формы
                $('#checkin_date').val(checkinFormatted);
                $('#checkout_date').val(checkoutFormatted);
                
                // Обновление отображаемого текста
                $('#checkin_date_display .date-placeholder').text(checkinFormatted);
                $('#checkout_date_display .date-placeholder').text(checkoutFormatted);
                
                // Добавляем классы для визуального обозначения выбранных дат
                $('#checkin_date_display, #checkout_date_display').addClass('date-selected');
            }
        },
        onOpen: function() {
            console.log('Календарь открыт');
        },
        onClose: function() {
            console.log('Календарь закрыт');
        },
        onReady: function() {
            console.log('Календарь готов к использованию');
            fixWeekdaysDisplay();
        }
    });
    
    // Обработчик изменения размера окна для адаптивного отображения календаря
    $(window).on('resize', function() {
        // Если календарь уже инициализирован, обновляем настройки при изменении размера
        if (fp && fp.config) {
            var shouldBeMobile = isMobileDevice();
            var currentIsMobile = fp.config.showMonths === 1;
            
            // Если изменился тип устройства, пересоздаем календарь
            if (shouldBeMobile !== currentIsMobile) {
                console.log('Устройство изменилось с ' + (currentIsMobile ? 'мобильного на десктоп' : 'десктопа на мобильное'));
                fp.set('showMonths', shouldBeMobile ? 1 : 2);
                
                // После изменения количества месяцев, нужно перерисовать календарь
                setTimeout(function() {
                    fp.redraw();
                    // Исправляем отображение дней недели после перерисовки
                    fixWeekdaysDisplay();
                }, 100);
            }
        }
    });
    
    // Обработчик клика на элементы выбора даты
    $('#checkin_date_display, #checkout_date_display').on('click', function() {
        console.log('Клик по элементу выбора даты:', $(this).attr('id'));
        // Убеждаемся, что календарь не перекрывается другими элементами
        $('.flatpickr-calendar').css('z-index', '99999');
        // Принудительно открываем календарь с небольшой задержкой
        setTimeout(function() {
            try {
                fp.open();
                console.log('Команда открытия календаря выполнена');
            } catch (e) {
                console.error('Ошибка при открытии календаря:', e);
            }
        }, 50);
    });
    
    // Добавляем стили для выбранных дат
    $('<style>')
        .text('.date-selected { background-color: #f0f8ff; border-color: #4a90e2; } .date-selected .date-placeholder { color: #333; }')
        .appendTo('head');
    
    // Настройка счетчиков гостей
    function setupCounter($minusButton, $plusButton, $display, $hiddenField, minValue) {
        var count = parseInt($hiddenField.val()) || minValue;
        $display.text(count);
        
        $minusButton.on('click', function() {
            if (count > minValue) {
                count--;
                $display.text(count);
                $hiddenField.val(count);
            }
        });
        
        $plusButton.on('click', function() {
            if (count < 10) { // Максимум 10 гостей
                count++;
                $display.text(count);
                $hiddenField.val(count);
            }
        });
    }
    
    console.log('Настройка счетчиков гостей...');
    
    // Настройка счетчика взрослых
    setupCounter(
        $('.btn-minus[data-target="guest_count"]'),
        $('.btn-plus[data-target="guest_count"]'),
        $('#guest_count_display'),
        $('#guest_count'),
        1
    );
    
    // Настройка счетчика детей
    setupCounter(
        $('.btn-minus[data-target="children_count"]'),
        $('.btn-plus[data-target="children_count"]'),
        $('#children_count_display'),
        $('#children_count'),
        0
    );
    
    // Валидация формы перед отправкой
    $('#sunapartament-booking-form').on('submit', function(e) {
        var checkinDate = $('#checkin_date').val();
        var checkoutDate = $('#checkout_date').val();
        
        if (!checkinDate || !checkoutDate) {
            e.preventDefault();
            alert('Пожалуйста, выберите даты заезда и выезда.');
            return false;
        }
        
        return true;
    });
    
    console.log('Инициализация формы бронирования завершена');
 });