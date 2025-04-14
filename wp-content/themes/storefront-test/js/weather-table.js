(function($) {
    $(document).ready(function() {
        let timeoutId;
        
        // Обработчик поиска
        function performSearch() {
            const searchTerm = $('#citySearch').val();
            
            $.ajax({
                url: weatherTableParams.ajaxurl,
                type: 'GET',
                data: {
                    action: 'weather_table_search',
                    search: searchTerm,
                    security: weatherTableParams.nonce
                },
                beforeSend: function() {
                    $('#weatherTableContainer').addClass('loading');
                },
                success: function(response) {
                    $('#weatherTableContainer').html(response);
                },
                complete: function() {
                    $('#weatherTableContainer').removeClass('loading');
                }
            });
        }

        // Поиск при вводе
        $('#citySearch').on('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(performSearch, 500);
        });

        // Поиск по кнопке
        $('#searchButton').on('click', performSearch);
    });
})(jQuery);