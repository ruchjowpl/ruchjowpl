/**
 * Created by grest on 2/16/14.
 */

angular.module('ruchJowApp', [
    'ruchJow.config',
    'ruchJow.basicServices',        // Includes semicalc Constants and Messages.

    'ruchJow.ctrls.index',            // Controllers for frontend
    'ruchJow.tools',
    'fr.angUtils',

    'ruchJow.translations',
    'ui.bootstrap',

    'ruchJow.user',
    'ruchJow.user.confirmation',      // User confirmation handler action.
    'ruchJow.user.referralLinkHandler',   // Referral link handler action.
    'ruchJow.user.newPasswordHandler',   // New password action handler action.
    'ruchJow.user.preSignedRegister',    // open registration form with nick and e-mail fields filled with stored values.

    'ruchJow.googleAnalytics',    // Initializes googleAnalytics and sends pageview info each time $location changes

    'ruchJow.ranks',
    'ruchJow.transferujPl',
    'ruchJow.feedback'

])
    .config(['ruchJowConstantsProvider', function (constantsProvider) {
        constantsProvider
            .set('SITE_NAME', 'Ruch JOW');
    }])
    .config(['ruchJowDefaultTitleProvider', function (ruchJowDefaultTitleProvider) {
        ruchJowDefaultTitleProvider.set('RuchJOW.pl - Walczymy o Jednomandatowe Okręgi Wyborcze. Poprzyj zmiany!');
    }])
    .config(['$interpolateProvider', function($interpolateProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');
    }])
    .config(['ruchJowAnchorScrollProvider', function (ruchJowAnchorScrollProvider) {
        ruchJowAnchorScrollProvider.setYOffset(10);
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

    .config(['ruchJowHomepageActionsProvider', function (ruchJowHomepageActionsProvider) {
        ruchJowHomepageActionsProvider.setRedirection('main');
    }])

    .config(['ruchJowUserProvider', function (ruchJowUserProvider) {
        ruchJowUserProvider.addSocialLinkDefinition(
            'facebook',
            'http://www.facebook.com/',
            '^([?\\w\\-.\\/])+(\\?id=(?=\\d.*))?$',
            'Facebook'
        );

        ruchJowUserProvider.addSocialLinkDefinition(
            'twitter',
            'http://twitter.com/',
            '^([?\\w\\-.\\/])+(\\?id=(?=\\d.*))?$',
            'Twitter'
        );

        ruchJowUserProvider.addSocialLinkDefinition(
            'google_plus',
            'http://plus.google.com/',
            '^([?\\w\\-.\/])+(\\?id=(?=\\d.*))?$',
            'Google+'
        );

        ruchJowUserProvider.addSocialLinkDefinition(
            'youtube',
            'http://www.youtube.com/user/',
            '^([?\\w\\-.\\/])+(\\?id=(?=\\d.*))?$',
            'youTube'
        );
    }])
    .config(['ruchJowTransferujPlProvider', function (ruchJowTransferujPlProvider) {
        ruchJowTransferujPlProvider.
            addUser(
                'default',
                15885,
                { encodeUrl: Routing.generate('ruch_jow_transferuj_pl_encode') },
                true
            );
    }])
    .config(['ruchJowFeedbackProvider', function (ruchJowFeedbackProvider) {
        ruchJowFeedbackProvider.setCreateUrl(Routing.generate('feedback_ajax_create'));
    }])

    .config(['ruchJowGoogleAnalyticsProvider', function (gaProvider) {
        gaProvider.setWebPropertyId('UA-62959990-1');
    }])
    .run(['ruchJowGoogleAnalytics', '$rootScope', function (ruchJowGoogleAnalytics, $rootScope) {
        $rootScope.ruchJowGoogleAnalytics = ruchJowGoogleAnalytics;
    }])
    .run(['$rootScope', 'ruchJowAnchorScroll', function ($rootScope, ruchJowAnchorScroll) {
        $rootScope.ruchJowAnchorScroll = ruchJowAnchorScroll;
    }])
    .run(['$rootScope', 'ruchJowAnchorScroll', '$timeout', function ($rootScope, ruchJowAnchorScroll, $timeout) {
        $rootScope.$on('$stateChangeSuccess', function (event, unfoundState) {
            if (unfoundState.hasOwnProperty('ruchJowScrollId')) {
                $timeout(function () {
                    ruchJowAnchorScroll(unfoundState.ruchJowScrollId);
                }, 0);
            }
        });
    }])
;


angular.element(document).ready(function() {
    angular.bootstrap(document, ['ruchJowApp']);
});