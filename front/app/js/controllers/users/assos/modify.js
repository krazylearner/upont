angular.module('upont')
    .controller('Assos_Modify_Ctrl', ['$scope', '$http', '$state', 'club', function($scope, $http, $state, club) {
        $scope.club = club;
        $scope.showIcons = false;
        $scope.faIcons = faIcons;
        $scope.search = '';
        $scope.searchResults = [];
        var clubSlug = club.name;

        $scope.submitClub = function(name, fullName, icon, image, banner) {
            var params = {
                'name' : name,
                'fullName' : fullName,
                'icon' : icon,
            };

            if (image) {
                params.image = image.base64;
            }

            if (banner) {
                params.banner = banner.base64;
            }

            $http.patch(apiPrefix + 'clubs/' + $scope.club.slug, params).success(function(){
                // On recharge le club pour être sûr d'avoir la nouvelle photo
                if (clubSlug == name) {
                    $http.get(apiPrefix + 'clubs/' + $scope.club.slug).success(function(data){
                        $scope.club = data;
                    });
                } else {
                    alertify.alert('Le nom court du club ayant changé, il est nécéssaire de recharger la page du club...');
                    $state.go('root.users.clubs.liste');
                }
                alertify.success('Modifications prises en compte !');
            });
        };

        $scope.setIcon = function(icon) {
            $scope.club.icon = icon;
        };

        $scope.searchUser = function(string) {
            if (string === '') {
                $scope.searchResults = [];
            } else {
                $http.post(apiPrefix + 'search', {search: 'User/' + string}).success(function(data){
                    $scope.searchResults = data.users;
                });
            }
        };

        $scope.addMember = function(slug, name) {
            // On vérifie que la personne n'est pas déjà membre
            for (var i = 0; i < $scope.members.length; i++) {
                if ($scope.members[i].user.username == slug) {
                    alertify.error('Déjà membre du club !');
                    return;
                }
            }

            alertify.prompt('Rôle :', function(e, role){
                if (e) {
                    $http.post(apiPrefix + 'clubs/' + $scope.club.slug + '/users/' + slug, {role: role}).success(function(data){
                        alertify.success(name + ' a été ajouté(e) !');
                        $scope.reloadMembers();
                    });
                }
            });
        };

        $scope.removeMember = function(slug) {
            $http.delete(apiPrefix + 'clubs/' + $scope.club.slug + '/users/' + slug).success(function(data){
                alertify.success('Membre supprimé !');
                $scope.reloadMembers();
            });
        };

        $scope.reloadMembers = function() {
            $http.get(apiPrefix + 'clubs/' + $scope.club.slug + '/users').success(function(data){
                $scope.members = data;
            });
        };
    }])
    .config(['$stateProvider', function($stateProvider) {
        $stateProvider
            .state('root.users.assos.simple.modify', {
                url: '/gestion',
                controller: 'Assos_Modify_Ctrl',
                templateUrl: 'views/users/assos/modify.html',
                data: {
                    title: 'Gestion - uPont',
                    top: true
                },
            });
    }]);