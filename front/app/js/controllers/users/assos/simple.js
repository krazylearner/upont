angular.module('upont')
    .controller('Assos_Simple_Ctrl', ['$scope', 'club', 'members', function($scope, club, members) {
        $scope.club = club;
        $scope.members = members;
        $scope.promo = '017';
    }])
    .config(['$stateProvider', function($stateProvider) {
        $stateProvider
            .state('root.users.assos.simple', {
                url: '/:slug',
                abstract: true,
                controller: 'Assos_Simple_Ctrl',
                templateUrl: 'views/users/assos/simple.html',
                resolve: {
                    club: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'clubs/:slug').get({
                            slug: $stateParams.slug
                        }).$promise;
                    }],
                    members: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'clubs/:slug/users').query({
                            slug: $stateParams.slug
                        }).$promise;
                    }]
                }
            });
    }]);
