(function (angular) {
    'use strict';

    angular.module('ruchJow.config.security', [
        'ruchJow.security',
        'ruchJow.security.translations',
        'ruchJow.user'
    ])
        .config(['ruchJowSecurityProvider', function(provider) {
            provider.setUserServiceName('ruchJowUser');
        }])
        // Security set symfony data urls.
        .config(['ruchJowSecuritySymfonyDataProvider', function (provider) {
            provider.setAuthDataUrl(Routing.generate('page_foundation_cif_auth_form_data'));
            provider.setUserRolesUrl(Routing.generate('page_foundation_cif_user_roles'));
        }])
    ;

})(angular);