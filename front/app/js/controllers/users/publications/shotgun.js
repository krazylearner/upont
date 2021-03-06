angular.module('upont')
    .controller('Shotgun_Ctrl', ['$scope', '$resource', '$http', 'event', 'shotgun', function($scope, $resource, $http, event, shotgun) {
        $scope.event = event;
        $scope.shotgun = shotgun;
        $scope.shotgunned = false;
        $scope.motivation = '';
        $scope.isLoading = false;

        $scope.shotgunEvent = function(){
            if(!$scope.isLoading) {
                if ($scope.motivation === '') {
                    $scope.motivation = 'Shotgun !';
                }

                $scope.isLoading = true;
                $http.post(apiPrefix + 'events/' + $scope.event.slug + '/shotgun', {motivation: $scope.motivation})
                    .then(function(){
                        $resource(apiPrefix + 'events/' + $scope.event.slug + '/shotgun').get(function(data){
                            $scope.shotgun = data;
                            $scope.shotgunned = true;
                        });
                        $scope.isLoading = false;
                    }, function(){
                        $scope.isLoading = false;
                    });
            }
        };

        $scope.deleteShotgun = function(){
            alertify.confirm('Attention c\'est définitif !', function(e) {
                if (e) {
                    $http.delete(apiPrefix + 'events/' + $scope.event.slug + '/shotgun').then(function(response){
                        $scope.shotgun = response.data;
                        $scope.shotgunned = false;
                        alertify.success('Nickel ! Ta place sera redistribuée aux prochains sur la liste d\'attente ;)');
                    });
                }
            });
        };
    }])
    .config(['$stateProvider', function($stateProvider) {
        $stateProvider
            .state('root.users.shotgun', {
                url: 'shotgun/:slug',
                templateUrl: 'controllers/users/publications/shotgun.html',
                controller: 'Shotgun_Ctrl',
                data: {
                    top: true
                },
                resolve: {
                    event: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'events/:slug').get({
                            slug: $stateParams.slug
                        }).$promise;
                    }],
                    shotgun: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'events/:slug/shotgun').get({
                            slug: $stateParams.slug
                        }).$promise;
                    }]
                }
            });
    }]);
