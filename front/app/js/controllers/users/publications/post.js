angular.module('upont')
    .controller('Publications_Post_Ctrl', ['$scope', '$rootScope', '$http', '$stateParams', 'Achievements', 'Upload', function($scope, $rootScope, $http, $stateParams, Achievements, Upload) {
        // Fonctions relatives à la publication
        var club = {name: 'Au nom de...'};
        $scope.display = true;

        // Si on est sur une page d'assos
        if ($stateParams.slug !== null && $stateParams.slug !== undefined) {
            // Par défaut on n'affiche pas le module
            $scope.display = false;
            for (var key in $rootScope.clubs) {
                // Si on appartien au club, on affiche avec le club préséléctionné
                if ($rootScope.clubs[key].club !== undefined && $rootScope.clubs[key].club.slug == $stateParams.slug) {
                    club = $rootScope.clubs[key].club;
                    $scope.display = true;
                }
            }
        }

        // Si l'utilisateur est un exterieur de l'administration
        if ($rootScope.hasRight('ROLE_EXTERIEUR'))
            club = $rootScope.clubs[0].club;

        var init = function() {
            $scope.focus = false;
            $scope.post = {
                entry_method: 'Entrée libre',
                text: '',
                start_date: '',
                end_date: '',
                shotgun_date: ''
            };
            $scope.type = 'news';
            $scope.club = club;
            $scope.toggle = false;

            if ($rootScope.hasRight('ROLE_EXTERIEUR'))
                $scope.placeholder = 'Texte de la news';
            else
                $scope.placeholder = 'Que se passe-t-il d\'intéressant dans ton asso ?';

            $scope.postFiles = {}
        };
        init();

        $scope.changeType = function(type) {
            $scope.type = type;

            switch (type) {
                case 'news':
                    if ($rootScope.hasRight('ROLE_EXTERIEUR'))
                        $scope.placeholder = 'Texte de la news';
                    else
                        $scope.placeholder = 'Que se passe-t-il d\'intéressant dans ton asso ?';
                    break;
                case 'event':
                    $scope.placeholder = 'Description de l\'événement';
                    break;
            }
        };

        $scope.toggleSelect = function() {
            $scope.toggle = !$scope.toggle;
        };

        $scope.changeClub = function(club) {
            $scope.club = club;
            $scope.toggle = false;
        };


        $scope.selectFiles = function (files) {
            $scope.postFiles = files;
        }

        $scope.publish = function(post, files) {
            var params  = {text: nl2br(post.text)};

            if (!$scope.modify) {
                if ($scope.club != club) {
                    params.authorClub = $scope.club.slug;
                } else {
                    if ($rootScope.hasRight('ROLE_EXTERIEUR')) {
                        params.authorClub = $rootScope.clubs[0].club.slug;
                    } else {
                        alertify.error('Tu n\'as pas choisi avec quelle assos publier');
                        return;
                    }
                }
            }

            if ($scope.postFiles && !$scope.modify) {
                params.uploadedFiles = $scope.postFiles;
            }

            switch ($scope.type) {
                case 'news':
                    params.name = post.name;

                    Upload.upload({
                        method: "POST",
                        url: apiPrefix + 'newsitems',
                        data: params
                    })
                    .success(function(data){
                        $rootScope.$broadcast('newNewsitem');
                        Achievements.check();
                        alertify.success('News publiée');
                        init();
                    }).error(function(){
                        alertify.error('Formulaire vide ou mal rempli');
                    });
                    break;
                case 'event':
                    params.name = post.name;
                    params.place = post.place;
                    params.entryMethod = post.entry_method;
                    params.startDate = moment(post.start_date).unix();
                    params.endDate = moment(post.end_date).unix();

                    if (!post.start_date || !post.end_date) {
                        alertify.error('Il faut préciser une date de début et de fin');
                        return;
                    }

                    if (params.startDate >= params.endDate) {
                        alertify.error('La date de début doit être avant la date de fin');
                        return;
                    }

                    if (post.entry_method == 'Shotgun') {
                        params.shotgunDate = moment(post.shotgun_date).unix();
                        params.shotgunLimit = post.shotgun_limit;
                        params.shotgunText = post.shotgun_text;

                        if (!post.shotgun_date) {
                            alertify.error('Il faut préciser une date de shotgun');
                            return;
                        }
                        if (params.shotgunDate >= params.startDate) {
                            alertify.error('La date de shotgun doit être avant la date de début');
                            return;
                        }
                    }

                    if (!$scope.modify){
                        Upload.upload({
                            method: "POST",
                            url: apiPrefix + 'events',
                            data: params
                        })
                        .success(function(data){
                            $rootScope.$broadcast('newEvent');
                            Achievements.check();
                            init();
                            alertify.success('Événement publié');
                        }).error(function(){
                            alertify.error('Formulaire vide ou mal rempli');
                        });
                    } else {
                        Upload.upload({
                            method: "PATCH",
                            url: apiPrefix + 'events/' + post.slug,
                            data: params
                        })
                        .success(function(data){
                            $rootScope.$broadcast('newEvent');
                            alertify.success('Événement modifié');
                            init();
                            $scope.modify = false;
                        }).error(function(){
                            alertify.error('Formulaire vide ou mal rempli');
                        });
                    }
                    break;
                default:
                    alertify.error('Type de publication non encore pris en charge');
            }
        };

        $scope.modify = false;
        $scope.$on('modifyEvent', function(event, post) {
            $scope.modify = true;
            $scope.changeType('event');
            $rootScope.$broadcast('newEvent');

            // Fix date to javascript timestamp
            post.start_date = post.start_date*1000;
            post.end_date = post.end_date*1000;
            if (post.shotgun_date) {
                post.shotgun_date = post.shotgun_date*1000;
            }

            $scope.post = post;
            window.scrollTo(0, 0);
        });
    }]);
