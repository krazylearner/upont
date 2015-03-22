angular.module('upont').factory('Ponthub', function() {
    return {
        isPopular: function(count, category) {
            switch (category) {
                case 'films':
                    return count > 15;
                case 'jeux':
                    return count > 5;
                case 'logiciels':
                    return count > 10;
                case 'musiques':
                    return count > 3;
                case 'autres':
                    return count > 10;
                case 'series':
                    return count > 10;
            }
            return false;
        },

        cat: function(category) {
            switch (category) {
                case 'films':
                    return 'movies';
                case 'jeux':
                    return'games';
                case 'logiciels':
                    return'softwares';
                case 'musiques':
                    return 'albums';
                case 'autres':
                    return 'others';
                case 'series':
                    return 'series';
            }
        }
    };
});
