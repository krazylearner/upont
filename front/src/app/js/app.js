import angular from 'angular';

import alertify from 'alertifyjs';
import Highcharts from 'highcharts';
import moment from 'moment';
import Raven from 'raven-js';

import 'typeface-open-sans';

import 'upont/css/themes/classic.less';

if (!location.origin)
    location.origin = location.protocol + '//' + location.host;

// Configuration de la langue
moment.locale('fr', {
    week: {
        dow: 1 // Monday is the first day of the week
    },
    calendar: {
        sameDay: '[Aujourd\'hui à] LT',
        nextDay: '[Demain à] LT',
        nextWeek: 'dddd [à] LT',
        lastDay: '[Hier à] LT',
        lastWeek: 'dddd [dernier]',
        sameElse: '[Le] DD MMM [à] LT'
    }
});

Highcharts.setOptions({
    lang: {
        months: [
            'janvier',
            'février',
            'mars',
            'avril',
            'mai',
            'juin',
            'juillet',
            'août',
            'septembre',
            'octobre',
            'novembre',
            'décembre'
        ],
        weekdays: [
            'Dimanche',
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi'
        ],
        shortMonths: [
            'Jan',
            'Fev',
            'Mar',
            'Avr',
            'Mai',
            'Juin',
            'Juil',
            'Aout',
            'Sept',
            'Oct',
            'Nov',
            'Déc'
        ],
        loading: 'Chargement en cours...',
        resetZoom: 'Réinitialiser le zoom',
        resetZoomTitle: 'Réinitialiser le zoom au niveau 1:1',
        thousandsSep: ' ',
        decimalPoint: ',',
        drillUpText: 'Retour à {series.name}'
    }
});

alertify.defaults = {
    // dialogs defaults
    autoReset: true,
    basic: false,
    closable: true,
    closableByDimmer: true,
    frameless: false,
    maintainFocus: true, // <== global default not per instance, applies to all dialogs
    maximizable: true,
    modal: true,
    movable: true,
    moveBounded: false,
    overflow: true,
    padding: true,
    pinnable: true,
    pinned: true,
    preventBodyShift: false, // <== global default not per instance, applies to all dialogs
    resizable: true,
    startMaximized: false,
    transition: 'pulse',

    // notifier defaults
    notifier: {
        // auto-dismiss wait time (in seconds)
        delay: 5,
        // default position
        position: 'bottom-right',
        // adds a close button to notifier messages
        closeButton: false
    },

    // language resources
    glossary: {
        // dialogs default title
        title: 'uPont',
        // ok button text
        ok: 'OK',
        // cancel button text
        cancel: 'Annuler'
    },

    // theme settings
    theme: {
        // class name attached to prompt dialog input textbox.
        input: 'ajs-input',
        // class name attached to ok button
        ok: 'ajs-ok',
        // class name attached to cancel button
        cancel: 'ajs-cancel'
    }
};

// Raven.config('https://c1de7d0cdfb14286a0d21efb3c0da318@sentry.io/124785').addPlugin(Raven.Plugins.Angular).install();

angular.module('upontConfig', []);

// Export issue in these libs
require('angulartics-piwik');

const upont = angular.module('upont', [
    // $resource
    require('angular-resource'),
    // JWT Auth
    require('angular-jwt'),
    // Routing
    require('angular-ui-router'),

    // Additionnal | filters
    require('angular-filter'),

    // Analytics
    require('angulartics'),
    'angulartics.piwik',

    // Infinite scrolling
    require('ng-infinite-scroll'),

    // Calendar
    require('angular-ui-bootstrap'),
    require('angular-bootstrap-calendar'),

    // Datetime picker
    require('bootstrap-ui-datetime-picker'),

    // Rich text editor
    require('ng-quill'),

    // Youtube integration
    require('angular-youtube-embed'),

    // File upload
    require('ng-file-upload'),

    // 'ngRaven',
    // 'ngSanitize',
    // 'ngTouch',
    // 'mgcrea.ngStrap',
    // 'naif.base64',
    // require('angular-animate'),

    // root configuration
    'upontConfig'
]);

import {API_PREFIX} from './config/constants';

(function() {
    // Get Angular's $http module.
    var initInjector = angular.injector(['ng']);
    var $http = initInjector.get('$http');

    $http.get(API_PREFIX + 'config').then(function(success) {
        // Define a 'upontConfig' module.
        angular.module('upontConfig', []).constant('upontConfig', success.data);

        // Start upont manually.
        angular.element(document).ready(function() {
            angular.bootstrap(document, ['upont']);

            var $rootScope = angular.element(document.querySelector('[ng-app]') || document).injector().get('$rootScope');

            $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {
                console.log('$stateChangeStart to ' + toState.to + '- fired when the transition begins. toState,toParams : \n', toState, toParams);
            });

            $rootScope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams) {
                console.log('$stateChangeError - fired when an error occurs during transition.');
                console.log(arguments);
            });

            $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
                console.log('$stateChangeSuccess to ' + toState.name + '- fired once the state transition is complete.');
            });

            $rootScope.$on('$viewContentLoaded', function(event) {
                console.log('$viewContentLoaded - fired after dom rendered', event);
            });

            $rootScope.$on('$stateNotFound', function(event, unfoundState, fromState, fromParams) {
                console.log('$stateNotFound ' + unfoundState.to + '  - fired when a state cannot be found by its name.');
                console.log(unfoundState, fromState, fromParams);
            });
        });
    });
})();

require('./services/Achievements');
require('./services/Migration');
require('./services/Paginate');
require('./services/Permissions');
require('./services/Ponthub');
require('./services/Scroll');
require('./services/StorageService');

require('./config/config');
require('./config/themes');
import './config/quill';

require('./directives/chart');
require('./directives/flex');
require('./directives/likes');
require('./directives/panel');
require('./directives/ribbon');
require('./directives/search');
require('./directives/state-active');
require('./directives/svg-image');
require('./directives/text-overflow');
require('./directives/user');

require('./filters/courseHour');
require('./filters/formatDate');
require('./filters/formatDuration');
require('./filters/formatPosition');
require('./filters/formatSize');
require('./filters/fromNow');
require('./filters/match');
require('./filters/numberFixedLen');
require('./filters/promoFilter');
require('./filters/reverse');
require('./filters/thumb');
require('./filters/trustAsHtml');
require('./filters/ucfirst');
require('./filters/urlFile');

import Router from 'upont/js/config/router';
upont.config(Router);