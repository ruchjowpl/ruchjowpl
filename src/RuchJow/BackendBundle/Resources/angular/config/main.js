/**
 * Created by grest on 2/16/14.
 */

angular.module('ruchJow.backend.config.globals', [
    'ruchJow.backend.config.routing',

    'pascalprecht.translate',
    'ruchJow.basicServices',


    'fr.angUtils',
    'ruchJow.tools',
    'ui.bootstrap',

    'ui.router',
    'ui.utils',


    'ruchJow.backend.ctrls.index',
    'ruchJow.backend.translations',
    'ruchJow.symfony.security'
])
    .config(['$httpProvider', 'symfonyTokenInterceptorProvider', function ($httpProvider, symfonyTokenInterceptorProvider) {
        $httpProvider.defaults.xsrfCookieName = 'XSRF-TOKEN-ANG-RJ';
        $httpProvider.defaults.xsrfHeaderName = 'X-XSRF-TOKEN-ANG-RJ';
        symfonyTokenInterceptorProvider.setXsrfHeaderName('X-XSRF-TOKEN-ANG-RJ');
    }])
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