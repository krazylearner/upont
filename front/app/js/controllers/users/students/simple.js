angular.module('upont')
    .controller('Students_Simple_Ctrl', ['$rootScope', '$scope', 'user', 'foyer', 'ponthub', 'clubs', function($rootScope, $scope, user, foyer, ponthub, clubs) {
        $scope.user = user;
        $scope.foyer = foyer;
        $scope.displayFoyer = empty(foyer.error);
        $scope.ponthub = ponthub;
        $scope.displayPonthub = empty(ponthub.error);
        $scope.clubs = clubs;

        if (empty(foyer.error)) {
            // Définition des graphes Highcharts
            var beers = [];
            for(var key in foyer.perBeer) {
                /*jslint evil: true */
                beers.push(eval(foyer.perBeer[key]));
            }
            var liters = [];
            for(key in foyer.stackedLiters) {
                /*jslint evil: true */
                liters.push(eval(foyer.stackedLiters[key]));
            }

            $scope.chartBeers = {
                chart: {
                    renderTo: 'beers',
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: true,
                },
                credits: {
                    enabled: false,
                },
                exporting: {
                    enabled: false,
                },
                title: {
                    text: 'Bières préférées',
                },
                subtitle: {
                    text: 'Dis moi ce que tu bois, je te dirai qui tu es...',
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            formatter: function() {
                            return '<b>'+ this.point.name +'</b> : '+ this.y;
                            }
                        }
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Nombre de bières',
                    data: beers
                }]
            };

            $scope.chartLiters = {
                chart: {
                    renderTo: 'liters',
                    type: 'area',
                },
                credits: {
                    enabled: false,
                },
                exporting: {
                    enabled: false,
                },
                title: {
                    text: 'Litres ingérés',
                },
                subtitle: {
                    text: 'Tss tss...',
                },
                legend: {
                    enabled: false
                },
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        month: '%b %e',
                        year: '%b'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Volume (L)'
                    },
                    min: 0
                },
                tooltip: {
                    pointFormat: 'Volume : <strong>{point.y:,.1f}L</strong>',
                    dateTimeLabelFormats: {
                        month: '%b %e',
                        day: '%A %e %B',
                        year: '%b'
                    }
                },
                series: [{ name: 'Volume ingéré', data: liters}]
            };
        }

        if (empty(ponthub.error)) {
            // Définition des graphes Highcharts
            $scope.chartRepartition = {
                chart: {
                    renderTo: 'repartition',
                    type: 'pyramid',
                },
                credits: {
                    enabled: false,
                },
                exporting: {
                    enabled: false,
                },
                title: {
                    text: 'Répartition des téléchargements',
                },
                subtitle: {
                    text: 'Adepte de séries ou gamer ?',
                },
                plotOptions: {
                    series: {
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b> ({point.y:,.0f})',
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                            softConnector: true,
                        }
                    }
                },
                legend: {
                    enabled: false,
                },
                series: [{name: 'Téléchargements', data: ponthub.repartition}],
            };

            $scope.chartTimeline = {
                chart: {
                    renderTo: 'timeline',
                    type: 'area',
                    zoomType: 'xy',
                },
                credits: {
                    enabled: false,
                },
                exporting: {
                    enabled: false,
                },
                title: {
                    text: 'Téléchargements cumulés'
                },
                subtitle: {
                    text: '= niveau d\'oisiveté',
                },
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        month: '%b %e',
                        year: '%b'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Téléchargements'
                    },
                },
                plotOptions: {
                    area: {
                        stacking: 'normal',
                        lineColor: '#666666',
                        lineWidth: 1,
                        marker: {
                            lineWidth: 1,
                            lineColor: '#666666'
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    valueSuffix: ' téléchargements'
                },
                series: ponthub.timeline,
            };

            $scope.chartHipster = {
                chart: {
                    renderTo: 'hispter',
                    type: 'gauge',
                    plotBackgroundColor: null,
                    plotBackgroundImage: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                },
                title: {
                    text: 'Hipsteromètre'
                },
                subtitle: {
                    text: 'Plus tu télécharges des fichiers que personne ne va chercher, plus t\'es bon',
                },
                credits: {
                    enabled: false,
                },
                exporting: {
                    enabled: false,
                },
                pane: {
                    startAngle: -150,
                    endAngle: 150,
                    background: [{
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#FFF'],
                                [1, '#333']
                            ]
                        },
                        borderWidth: 0,
                        outerRadius: '109%'
                    }, {
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#333'],
                                [1, '#FFF']
                            ]
                        },
                        borderWidth: 1,
                        outerRadius: '107%'
                    }, {
                        // default background
                    }, {
                        backgroundColor: '#DDD',
                        borderWidth: 0,
                        outerRadius: '105%',
                        innerRadius: '103%'
                    }]
                },

                yAxis: {
                    min: 0,
                    max: 200,

                    minorTickInterval: 'auto',
                    minorTickWidth: 1,
                    minorTickLength: 10,
                    minorTickPosition: 'inside',
                    minorTickColor: '#666',

                    tickPixelInterval: 30,
                    tickWidth: 2,
                    tickPosition: 'inside',
                    tickLength: 10,
                    tickColor: '#666',
                    labels: {
                        step: 2,
                        rotation: 'auto'
                    },
                    title: {
                        text: 'HpStR'
                    },
                    plotBands: [{
                        from: 0,
                        to: 120,
                        color: '#55BF3B' // green
                    }, {
                        from: 120,
                        to: 160,
                        color: '#DDDF0D' // yellow
                    }, {
                        from: 160,
                        to: 200,
                        color: '#DF5353' // red
                    }]
                },
                series: [{
                    name: 'Hipsteritude',
                    data: [ponthub.hipster],
                    tooltip: {
                        valueSuffix: ' HpStR'
                    }
                }]
            };
        }
    }])
    .config(['$stateProvider', function($stateProvider) {
        $stateProvider
            .state('root.users.students.simple', {
                url: '/:slug',
                templateUrl: 'views/users/students/simple.html',
                controller: 'Students_Simple_Ctrl',
                resolve: {
                    user: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'users/:slug').get({
                            slug: $stateParams.slug
                        }).$promise;
                    }],
                    foyer: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'foyer/statistics/:slug').get({
                            slug: $stateParams.slug
                        }).$promise;
                    }],
                    ponthub: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'ponthub/statistics/:slug').get({
                            slug: $stateParams.slug
                        }).$promise;
                    }],
                    clubs: ['$resource', '$stateParams', function($resource, $stateParams) {
                        return $resource(apiPrefix + 'users/:slug/clubs').query({
                            slug: $stateParams.slug
                        }).$promise;
                    }]
                },
                data: {
                    title: 'Profil - uPont',
                    top: true
                }
            });
    }]);