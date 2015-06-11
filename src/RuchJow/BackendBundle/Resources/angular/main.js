/**
 * Created by grest on 2/16/14.
 */

angular.module('ruchJow.backend', [
    'fr.angUtils',
    'ruchJow.tools',
    'ui.bootstrap',
    'ruchJow.basicServices',
    'ui.router',
    'ui.utils',
    'pascalprecht.translate',

    'ruchJow.backend.ctrls.index',
    'ruchJow.backend.translations'
])
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider
            .preferredLanguage('pl')
            .fallbackLanguage('en');
    }])
    .config(['ruchJowConstantsProvider', function (constantsProvider) {
        constantsProvider
            .set('SITE_NAME', 'Ruch JOW - BACKEND');
    }])
    .config(['$interpolateProvider', function($interpolateProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');
    }])
    .config(['frNumberFormatProvider', function (numberProvider) {
        numberProvider.registerFormat('default', {
            decSeparator: ',',
            thousandsSeparator: ' '
        });
        numberProvider.registerFormat('cash_no_unit', {
            decPlaces: 2,
            decRequiredPlaces: 0,
            unit: ''
        });
        numberProvider.registerFormat('cash', {
            decPlaces: 2,
            decRequiredPlaces: 0,
            unit: ' zł'
        });
        numberProvider.registerFormat('cash_fixed', {
            decPlaces: 2,
            decRequiredPlaces: 2,
            unit: ' zł'
        });
    }])
    .config(['ruchJowPartialsProvider', function (partialsProvider) {
        partialsProvider.registerRoute('backend', 'backend_partial');
        partialsProvider.setDefaultRoute('backend');
    }])
    .config(['$urlRouterProvider', function ($urlRouterProvider) {
        $urlRouterProvider
            //.when('/', function () { return true; })
            //.when(/\/action\/.+/, function () { return true; })
            //.when('/user', '/user/data')
            //.when('/user/challenges', '/user/challenges/invite_friends')
            .otherwise('/');
    }])
    .config(['$stateProvider', 'ruchJowPartialsProvider', function ($stateProvider, ruchJowPartialsProvider) {
        $stateProvider
            .state('main', {
                url: '/',
                templateUrl: 'overview.html'
            })
            .state('feedback', {
                url: '/feedback',
                templateUrl: ruchJowPartialsProvider.getUrl('feedback','backend'),
                controller: 'BackendFeedbackCtrl'
            })
            .state('userUsers', {
                url: '/users',
                templateUrl: ruchJowPartialsProvider.getUrl('users','backend'),
                controller: 'BackendUsersCtrl'
            })
            .state('userUser', {
                url: '/user/:username',
                templateUrl: ruchJowPartialsProvider.getUrl('user','backend'),
                controller: 'BackendUserCtrl'
            })
            .state('tasks', {
                url: '/tasks',
                templateUrl: ruchJowPartialsProvider.getUrl('tasks','backend'),
                controller: 'BackendTasksCtrl'
            })
        ;
    }])
    .run(function ($rootScope, $state, $stateParams) {
        $rootScope.$state = $state;
        $rootScope.$stateParams = $stateParams;
    })
    .run(['$rootScope', 'ruchJowConstants', function ($rootScope, ruchJowConstants) {
        $rootScope.ruchJowGetConstant = function (name, defValue) {
            return ruchJowConstants.get(name, defValue);
        };
    }])

    .factory('ruchJowIsGranted', ['$http', function ($http) {

        var roles = {};
        var routeName = 'backend_cif_user_roles';
        var config = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            //method: 'POST',
            url: Routing.generate(routeName)
        };

        $http(config).then(function (response) {
            if (response.data) {
                for (var role in response.data) {
                    if (response.data.hasOwnProperty(role)) {
                        roles[role] = !!response.data[role];
                    }
                }
            }
        });

        return function isGranted(role) {
            return roles.hasOwnProperty(role) && roles[role];
        };
    }])
    .run(['$rootScope', 'ruchJowIsGranted', function ($rootScope, ruchJowIsGranted) {
        $rootScope.ruchJowIsGranted = ruchJowIsGranted;
    }])

    // Bootstrap ui datepicker fix.
    .directive('datepickerPopup', function (){
        return {
            restrict: 'EAC',
            require: 'ngModel',
            link: function(scope, element, attr, controller) {
                //remove the default formatter from the input directive to prevent conflict
                controller.$formatters.shift();
            }
        }
    })
;


angular.element(document).ready(function() {
    angular.bootstrap(document, ['ruchJow.backend']);
});