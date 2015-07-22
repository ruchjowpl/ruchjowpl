(function (angular) {
    'use strict';

    angular.module('ruchJow.config.security', [
        'ruchJow.security',
        'ruchJow.security.translations',
        'ruchJow.user',
        'ruchjow.symfony.security'
    ])
        .config(['ruchJowSecurityProvider', function(provider) {
            provider.setUserServiceName('ruchJowUser');
        }])
        // Security set symfony data urls.
        .config(['ruchJowSecuritySymfonyDataProvider', function (provider) {
            provider.setAuthDataUrl(Routing.generate('page_foundation_cif_auth_form_data'));
            provider.setUserRolesUrl(Routing.generate('page_foundation_cif_user_roles'));
        }])
        .config(['$httpProvider', 'symfonyTokenInterceptorProvider', function ($httpProvider, symfonyTokenInterceptorProvider) {
            $httpProvider.defaults.xsrfCookieName = 'XSRF-TOKEN-ANG-RJ';
            $httpProvider.defaults.xsrfHeaderName = 'X-XSRF-TOKEN-ANG-RJ';
            symfonyTokenInterceptorProvider.setXsrfHeaderName('X-XSRF-TOKEN-ANG-RJ');
        }])
    ;

})(angular);