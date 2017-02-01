angular.module('upont').factory('Permissions', ['StorageService', '$rootScope', '$resource', 'jwtHelper', '$analytics', function(StorageService, $rootScope, $resource, jwtHelper, $analytics) {
    remove = function() {
        $rootScope.isLogged = false;
        $rootScope.isAdmin = false;
        $rootScope.isAdmissible = false;
        $rootScope.isExterieur = false;
        StorageService.remove('token');
        StorageService.remove('droits');
    };

    // Charge les permissions à partir du token stocké dans le Storage
    load = function() {
        if (StorageService.get('token') && !jwtHelper.isTokenExpired(StorageService.get('token'))) {
            var token = jwtHelper.decodeToken(StorageService.get('token'));
            $rootScope.isLogged = true;
            $rootScope.isAdmin = StorageService.get('droits').indexOf('ROLE_ADMIN') != -1;
            $rootScope.isAdmissible = StorageService.get('droits').indexOf('ROLE_ADMISSIBLE') != -1;
            $rootScope.isExterieur = StorageService.get('droits').indexOf('ROLE_EXTERIEUR') != -1;

            var username = token.username;
            $rootScope.username = username;
            $analytics.setUsername(username);
            Raven.setUserContext({
                username: username
            });
            // On récupère les données utilisateur
            $resource(apiPrefix + 'users/:slug', {slug: username}).get(function(data){
                $rootScope.me = data;
            });

            // On récupère les clubs de l'utilisateurs pour déterminer ses droits de publication
            $resource(apiPrefix + 'users/:slug/clubs', {slug: username }).query(function(data){
                $rootScope.clubs = data;
            });
        } else {
            remove();
        }
    };

    return {
        // Vérifie si l'utilisateur a les droits sur un club
        hasClub: function(slug) {
            if ($rootScope.isAdmin)
                return true;

            for (var i = 0; i < $rootScope.clubs.length; i++) {
                if ($rootScope.clubs[i].club.slug == slug)
                    return true;
            }
            return false;
        },

        // Vérifie si l'utilisateur a les droits sur un role
        hasRight: function(role) {
            if (StorageService.get('droits') === null)
                return false;
            // Ces rôles là ne doivent pas être répercutés aux admins
            if (role == 'ROLE_EXTERIEUR' || role == 'ROLE_ADMISSIBLE')
                return StorageService.get('droits').indexOf(role) != -1;
            if (StorageService.get('droits').indexOf('ROLE_ADMIN') != -1)
                return true;
            // Le modo a tous les droits sauf ceux de l'admin
            if (StorageService.get('droits').indexOf('ROLE_MODO') != -1 && role != 'ROLE_ADMIN')
                return true;
            return StorageService.get('droits').indexOf(role) != -1;
        },

        load: function() {
            load();
        },

        set: function(token, roles) {
            StorageService.set('token', token);
            StorageService.set('droits', roles);
            load();
        },

        remove: function() {
            remove();
        },

        username: function() {
            return jwtHelper.decodeToken(StorageService.get('token')).username;
        }
    };
}]);
