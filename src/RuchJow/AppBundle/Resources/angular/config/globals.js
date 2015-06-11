(function (angular) {
    'use strict';

    angular.module('ruchJow.config.globals', [
        'ui.router',
        'ruchJow.messages',
        'ruchJow.security',
        'ruchJow.basicServices',
        'ruchJow.points',
        'ruchJow.statistics',
        'ruchJow.feedback'
    ])

        // UI ROUTER ($state, $stateParams)
        .run(['$rootScope', '$state', '$stateParams', function ($rootScope, $state, $stateParams) {

        }])

        // MESSAGES ($showMessage, $sendMessage)
        .config(['ruchJowPartialsProvider', 'ruchJowMessagesProvider', function (ruchJowPartialsProvider, ruchJowMessagesProvider) {
            ruchJowMessagesProvider.setShowMessageTemplateUrl(ruchJowPartialsProvider.getUrl('messagesModalMessage','app'));
            ruchJowMessagesProvider.setSendMessageTemplateUrl(ruchJowPartialsProvider.getUrl('messagesModalSendMessage','app'));
        }])

        // USER PROFILE
        .config(['ruchJowPartialsProvider', 'ruchJowUserProfileProvider', function (ruchJowPartialsProvider, ruchJowUserProfileProvider) {
            ruchJowUserProfileProvider.setProfileExpireTime(60);
            ruchJowUserProfileProvider.setShowProfileTemplateUrl(ruchJowPartialsProvider.getUrl('userModalProfile','app'));
        }])
        .run([
            '$rootScope',
            '$state',
            '$stateParams',
            'ruchJowSecurity',
            'frNumberFormat',
            'ruchJowConstants',
            'ruchJowPartials',
            'ruchJowPoints',
            'ruchJowStatistics',
            'ruchJowFeedbackModal',
            'ruchJowUserProfile',
            'ruchJowMessages',
            function ($rootScope, $state, $stateParams, ruchJowSecurity, frNumberFormat, ruchJowConstants, ruchJowPartials, ruchJowPoints, ruchJowStatistics, ruchJowFeedbackModal, ruchJowUserProfile, ruchJowMessages) {

                // States
                $rootScope.$state = $state;
                $rootScope.$stateParams = $stateParams;

                // Security
                $rootScope.security = ruchJowSecurity;

                // Constants
                $rootScope.getConstant = ruchJowConstants.get;

                // Partials
                $rootScope.getPartial = ruchJowPartials;

                // Points
                $rootScope.getPoints = ruchJowPoints.getPoints;
                $rootScope.getPointsDef = ruchJowPoints.getPointsDef;

                // Statistics
                $rootScope.getStatistics = ruchJowStatistics.get;

                // frNumberFormat
                $rootScope.frNumberFormat = frNumberFormat.format;

                // Feedback
                $rootScope.feedback = ruchJowFeedbackModal.open;

                // Profile
                $rootScope.$showModalProfile = ruchJowUserProfile.showProfile;

                // Messages
                $rootScope.$showMessage = ruchJowMessages.showMessage;
                $rootScope.$sendMessage = ruchJowMessages.sendMessageModal;

            }
        ])
        // Helper functions
        .run(['$rootScope', function ($rootScope) {
            $rootScope.getTypeOf = function (v) {
                return Object.prototype.toString.call(v).slice(8,-1);
            };

            $rootScope.isUndefined = function (v) {
                return typeof v === 'undefined';
            };

            $rootScope.isDefined = function (v) {
                return typeof v !== 'undefined';
            };

            $rootScope.isArray = function (v) {
                return $rootScope.getTypeOf(v) === 'Array';
            };

            $rootScope.isObject = function (v) {
                return $rootScope.getTypeOf(v) === 'Object';
            };

            $rootScope.isNull = function (v) {
                return $rootScope.getTypeOf(v) === 'Null';
            };

            $rootScope.isString = function (v) {
                return typeof v === 'string';
            };

            $rootScope.isNumber = function (v) {
                return typeof v === 'number';
            };

            $rootScope.inArray = function (needle, haystack) {
                return haystack.indexOf(needle) !== -1;
            };
        }])
    ;


})(angular);