/**
 * Created by grest on 2/16/14.
 */

angular.module('ruchJow.backend.config.routing', [
    'ruchJow.tools',
    'ruchJow.basicServices',
    'ui.router',
    'ui.utils',

    'ruchJow.backend.ctrls.referendumPoints',
    'ruchJow.backend.ctrls.jowEvents'
])
    .config(['ruchJowPartialsProvider', function (partialsProvider) {
        partialsProvider.registerRoute('backend', 'backend_partial');
        partialsProvider.setDefaultRoute('backend');
    }])
    .config(['$urlRouterProvider', function ($urlRouterProvider) {
        $urlRouterProvider
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
            .state('referendumPoints', {
                url: '/referendum_points',
                templateUrl: ruchJowPartialsProvider.getUrl('referendumPoints','backend'),
                controller: 'BackendReferendumPointsCtrl'
            })
            .state('jowEvents', {
                url: '/jow_events',
                templateUrl: ruchJowPartialsProvider.getUrl('jowEvents','backend'),
                controller: 'BackendJowEventsCtrl'
            })
        ;
    }])
    .run(function ($rootScope, $state, $stateParams) {
        $rootScope.$state = $state;
        $rootScope.$stateParams = $stateParams;
    })
;