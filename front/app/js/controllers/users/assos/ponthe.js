angular.module('upont')
    .controller('Ponthe_Ctrl', ['$rootScope', '$scope', 'clubs', function($rootScope, $scope, clubs) {
        $scope.clubs = clubs;
        $rootScope.displayTabs = true;
    }])
    .config(['$stateProvider', function($stateProvider) {
        $stateProvider
            .state('root.users.assos.ponthe', {
                url: '/ponthe',
                templateUrl: 'controllers/users/assos/ponthe.html',
                controller: 'Ponthe_Ctrl',
                data: {
                    title: 'Galleries Ponthé - uPont',
                    top: true
                },
                resolve: {
                    clubs: ['$resource', function($resource) {
                        return $resource(apiPrefix + 'clubs?sort=fullName').query().$promise;
                    }]
                }
            });
    }])
