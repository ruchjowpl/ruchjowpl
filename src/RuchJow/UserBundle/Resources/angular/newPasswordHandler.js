/**
 * Created by grest on 9/10/14.
 */

angular.module('ruchJow.user.newPasswordHandler', ['ruchJow.homepageActions', 'ruchJow.security', 'ruchJow.user.translations'])
    .config(['ruchJowHomepageActionsProvider',  function (homepageActionsProvider) {
        homepageActionsProvider.register('password_reset', 'ruchJowSecurity.setNewPassword');
    }]);