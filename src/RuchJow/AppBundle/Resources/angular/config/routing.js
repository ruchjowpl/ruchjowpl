(function (angular) {
    'use strict';

    angular.module('ruchJow.config.routing', [
        'ruchJow.basicServices',
        'ruchJow.states'
    ])
        .config(['ruchJowPartialsProvider', function (partialsProvider) {
            partialsProvider.registerRoute('app', 'app_partial');
            partialsProvider.setDefaultRoute('app');
        }])
        .config(['$urlRouterProvider', function ($urlRouterProvider) {
            $urlRouterProvider
                //.when('/', function () { return true; })
                //.when(/\/action\/.+/, function () { return true; })
                .when('/user', '/user/data')
                //.when('/user/challenges', '/user/challenges/invite_friends')
                .otherwise('/');
        }])
        .config(['ruchJowStatesProvider', 'ruchJowPartialsProvider', function (ruchJowStatesProvider, ruchJowPartialsProvider) {
            ruchJowStatesProvider
                .state('main', {
                    url: '/',
                    templateUrl: 'homepage.html',
                    controller: 'HomepageCtrl',
                    data: {
                        pageTitle: 'RuchJOW.pl - Walczymy o Jednomandatowe Okręgi Wyborcze. Poprzyj zmiany!'
                    }
                })
                .state('main.action', {
                    url: '^/action/{action}',
                    controller: 'ActionsCtrl'
                })

                .state('sponsors', {
                    url: '/sponsors',
                    templateUrl: Routing.generate('app_partial', { template: 'sponsors' }),
                    data: {
                        pageTitle: 'Sponsorzy - zobacz kto wsparł naszą akcję - ruchJOW.pl'
                    }
                })

                //.state('our_mission', {
                //    url: '/our_mission',
                //    templateUrl: Routing.generate('app_partial', { template: 'ourMission' })
                //})

                //.state('why_jow', {
                //    url: '/why_jow',
                //    templateUrl: Routing.generate('app_partial', { template: 'whyJow' })
                //})

                .state('about', {
                    url: '/about',
                    templateUrl: Routing.generate('app_partial', { template: 'about' }),
                    data: {
                        pageTitle: 'O akcji - zobacz o co walczymy i poprzyj zmiany - RuchJOW.pl'
                    }
                })

                .state('ranks', {
                    url: '/ranks',
                    templateUrl: Routing.generate('app_partial', { template: 'ranks' }),
                    controller: 'RanksFullCtrl',
                    data: {
                        pageTitle: 'Rankingi - najbardziej aktywne regiony i użytkownicy - ruchJOW.pl'
                    }
                })



                .state('points_info', {
                    url: '/points_info',
                    templateUrl: Routing.generate('app_partial', { template: 'pointsInfo' }),
                    controller: 'PointsInfoCtrl'
                })

                .state('faq', {
                    url: '/faq',
                    templateUrl: Routing.generate('app_partial', { template: 'faq' })
                })

                .state('user', {
                    url: '/user',
                    templateUrl: Routing.generate('app_partial', { template: 'userAccount' }),
                    controller: 'UserAccountCtrl'
                }, ['ROLE_REGISTERED_USER'])
                .state('user.data', {
                    url: '/data',
                    templateUrl: ruchJowPartialsProvider.getUrl('userAccount.userData','app'),
                    controller: 'UserDataCtrl'
                }, ['ROLE_REGISTERED_USER'])
                .state('user.history', {
                    url: '/history',
                    templateUrl: ruchJowPartialsProvider.getUrl('userAccount.history','app'),
                    controller: 'UserHistoryCtrl'
                }, ['ROLE_REGISTERED_USER'])
                .state('user.messages', {
                    url: '/messages',
                    templateUrl: ruchJowPartialsProvider.getUrl('userAccount.messages','app'),
                    controller: 'UserMessagesCtrl'
                }, ['ROLE_REGISTERED_USER'])


                .state('profile', {
                    url: '/profile/{username}',
                    templateUrl: ruchJowPartialsProvider.getUrl('userProfile','app'),
                    controller: 'JeUserProfileCtrl'
                })


                .state('challenges', {
                    url: '/challenges',
                    templateUrl: ruchJowPartialsProvider.getUrl('challenges','app'),
                    controller: 'ChallengesCtrl'
                })
                .state('challenges.invite_friends', {
                    url: '/invite_friends',
                    templateUrl: ruchJowPartialsProvider.getUrl('supportForm.invite_friends','app'),
                    controller: 'ChallengesInviteFriendsCtrl'
                }/*, ['ROLE_REGISTERED_USER']*/)
                .state('challenges.organise_event', {
                    url: '/organise_event',
                    templateUrl: ruchJowPartialsProvider.getUrl('supportForm.organise_event','app'),
                    controller: 'ChallengesOrganiseEventCtrl'
                }/*, ['ROLE_REGISTERED_USER']*/)
                .state('challenges.organise_referendum_point', {
                    url: '/organise_referendum_point',
                    templateUrl: ruchJowPartialsProvider.getUrl('supportForm.organise_referendum_point','app'),
                    controller: 'ChallengesOrganiseReferendumPointCtrl'
                }/*, ['ROLE_REGISTERED_USER']*/)
                .state('challenges.distribute_leaflets', {
                    url: '/distribute_leaflets',
                    templateUrl: ruchJowPartialsProvider.getUrl('supportForm.distribute_leaflets','app'),
                    controller: 'ChallengesDistributeLeafletsCtrl'
                }/*, ['ROLE_REGISTERED_USER']*/)
                .state('challenges.make_donation', {
                    url: '/make_donation',
                    templateUrl: ruchJowPartialsProvider.getUrl('supportForm.make_donation','app'),
                    controller: 'ChallengesMakeDonationCtrl',
                    ruchJowScrollId: 'body_wrapper'
                }/*, ['ROLE_REGISTERED_USER']*/)

                .state('contact', {
                    url: '/contact',
                    templateUrl: ruchJowPartialsProvider.getUrl('contact','app')/*,
                     controller: 'ChallengesCtrl'*/
                })
            ;

        }])
    ;

})(angular);