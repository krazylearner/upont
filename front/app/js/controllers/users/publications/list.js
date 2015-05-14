angular.module('upont')
    .controller('Publications_Ctrl', ['$scope', 'newsItems', 'events', 'messages', function($scope, newsItems, events, messages) {
        $scope.events = events;
        $scope.newsItems = newsItems;
        $scope.messages = messages;
    }])
    .controller('Publications_List_Ctrl', ['$scope', '$rootScope', '$resource', '$http', 'newsItems', 'events', 'Paginate', function($scope, $rootScope, $resource, $http, newsItems, events, Paginate) {
        $scope.events = events;
        $scope.newsItems = newsItems;

        $scope.next = function() {
            Paginate.next($scope.newsItems).then(function(data){
                $scope.newsItems = data;
            });
        };

        $scope.$on('newEvent', function(event, args) {
            Paginate.first($scope.events).then(function(data){
                $scope.events = data;
            });
        });

        $scope.$on('newNewsitem', function(event, args) {
            Paginate.first($scope.newsItems).then(function(data){
                $scope.newsItems = data;
            });
        });

        $scope.attend = function(publication){
            var i = $scope.events.data.indexOf(publication);
            // Si la personne attend déjà on ne fait qu'annuler le attend
            if ($scope.events.data[i].attend) {
                $http.delete(apiPrefix + 'events/' + $scope.events.data[i].slug + '/attend').success(function(data){
                    $scope.events.data[i].attend = false;
                    $scope.events.data[i].attendees--;
                });
            } else {
                $http.post(apiPrefix + 'events/' + $scope.events.data[i].slug + '/attend').success(function(data){
                    $scope.events.data[i].attend = true;
                    $scope.events.data[i].attendees++;

                    // Si la personne n'attendait aps avant
                    if ($scope.events.data[i].pookie) {
                        $scope.events.data[i].pookie = false;
                        $scope.events.data[i].pookies--;
                    }
                });
            }
        };

        $scope.pookie = function(publication){
            var i = $scope.events.data.indexOf(publication);
            // Si la personne pookie déjà on ne fait qu'annuler le pookie
            if ($scope.events.data[i].pookie) {
                $http.delete(apiPrefix + 'events/' + $scope.events.data[i].slug + '/decline').success(function(data){
                    $scope.events.data[i].pookie = false;
                    $scope.events.data[i].pookies--;
                });
            } else {
                $http.post(apiPrefix + 'events/' + $scope.events.data[i].slug + '/decline').success(function(data){
                    $scope.events.data[i].pookie = true;
                    $scope.events.data[i].pookies++;
                    alertify.success('Cet événement ne sera plus affiché par la suite. Tu pourras toujours le retrouver sur la page de l\'assos.');

                    // Si la personne était pookie avant
                    if ($scope.events.data[i].attend) {
                        $scope.events.data[i].attend = false;
                        $scope.events.data[i].attendees--;
                    }
                });
            }
        };

        $scope.showAttendees = function(publication){
            $http.get(apiPrefix + 'events/' + publication.slug + '/attendees').success(function(data){
                $scope.attendees = data;

                var string = '<strong>Personnes participant à l\'événement :</strong><br>';
                for (var i = 0; i < data.length; i++) {
                    if (data[i].username != $rootScope.me.username)
                        string += data[i].nick + ', ';
                }
                string = string.replace(/, $/, '');

                if (publication.attend)
                    string += publication.attendees == 1 ? 'Toi !' : ', toi !';

                alertify.alert(string);
            });
        };

        $scope.delete = function(post){
            var index = null;
            if (post.start_date) {
                index = $scope.events.data.indexOf(post);

                // On demande confirmation
                alertify.confirm('Veux-tu vraiment supprimer cet évènement ?', function(e){
                    if (e) {
                        $resource(apiPrefix + 'events/' + $scope.events.data[index].slug).delete(function() {
                            $scope.events.data.splice(index, 1);
                        });
                    }
                });
            } else {
                index = $scope.newsItems.data.indexOf(post);

                // On demande confirmation
                alertify.confirm('Veux-tu vraiment supprimer cette news ?', function(e){
                    if (e) {
                        $resource(apiPrefix + 'newsitems/' + $scope.newsItems.data[index].slug).delete(function() {
                            $scope.newsItems.data.splice(index, 1);
                        });
                    }
                });
            }
        };
    }])
    .config(['$stateProvider', function($stateProvider) {
        $stateProvider
            .state('root.users.publications', {
                url: '',
                templateUrl: 'views/users/publications/index.html',
                data: {
                    title: 'Accueil - uPont',
                    top: true
                },
                controller: 'Publications_Ctrl',
                resolve: {
                    newsItems: ['Paginate', function(Paginate) {
                        return Paginate.get('own/newsitems?sort=-date', 10);
                    }],
                    events: ['Paginate', function(Paginate) {
                        return Paginate.get('own/events');
                    }],
                    messages: ['Paginate', function(Paginate) {
                        return Paginate.get('newsitems?sort=-date&limit=10&name=null');
                    }]
                }
            });
    }]);