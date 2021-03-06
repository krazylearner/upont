angular.module('upont')
    .controller('Foyer_Playlist_Ctrl', ['$scope', '$rootScope', '$http', 'youtube', 'stats', 'members', 'Paginate', function($scope, $rootScope, $http, youtube, stats, members, Paginate) {
        $('#focus-input').focus();
        $scope.youtube = youtube;
        $scope.stats = stats;
        $scope.predicate = 'volume';
        $scope.reverse = true;

        $scope.reload = function() {
            Paginate.first($scope.youtube).then(function(response){
                $scope.youtube = response.data;
            });
        };

        $scope.post = function(link) {
            if (!link.match(/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/)) {
                alertify.error('Ce n\'est pas une vidéo YouTube !');
                return;
            }

            $http.post(apiPrefix + 'youtubes', {name: 'Lien Youtube Foyer', link: link}).then(function(){
                $scope.link = '';
                alertify.success('Yeah !');
                $scope.reload();
            });
        };

        $scope.delete = function(youtube) {
            alertify.confirm('Veux-tu vraiment faire ça ?', function(e) {
                if (e) {
                    $http.delete(apiPrefix + 'youtubes/' + youtube.slug).then(function(){
                        $scope.reload();
                    });
                }
            });
        };
    }])
    .config(['$stateProvider', function($stateProvider) {
        $stateProvider
            .state('root.users.assos.foyer-playlist', {
                url: '/c-est-ton-foyer',
                templateUrl: 'controllers/users/assos/foyer-playlist.html',
                controller: 'Foyer_Playlist_Ctrl',
                data: {
                    title: 'Playlist foyer - uPont',
                    top: true
                },
                resolve: {
                    youtube: ['Paginate', function(Paginate) {
                        return Paginate.get('youtubes?sort=-date', 20);
                    }],
                    stats: ['$resource', function($resource) {
                        return $resource(apiPrefix + 'statistics/foyer').get().$promise;
                    }],
                    members: ['$resource', function($resource) {
                        return $resource(apiPrefix + 'clubs/foyer/users').query().$promise;
                    }]
                }
            });
    }]);
