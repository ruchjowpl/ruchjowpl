(function () {
    'use strict';

    angular.module('ruchJow.symfony.security', [])
        // This http interceptor listens for authentication failures
        .provider('symfonyTokenInterceptor', function () {
            var xsrfHeaderName = 'X-XSRF-TOKEN';

            var provider = {
                setXsrfHeaderName: function (name) {
                    xsrfHeaderName = name;
                },
                $get: ['$injector', '$q', function($injector, $q) {

                    return {
                        responseError: function (rejection) {
                            if (rejection.status === 400
                                && rejection.headers(xsrfHeaderName)
                                && !rejection.config.hasOwnProperty('symfonyTokenRetry')
                            ) {
                                rejection.config.symfonyTokenRetry = true;

                                return $injector.get('$http')(rejection.config);
                            }

                            return $q.reject(rejection);
                        }
                    };
                }]
            };

            return provider;
        })

        // We have to add the interceptor to the queue as a string because the interceptor depends upon service instances that are not available in the config block.
        .config(['$httpProvider', function($httpProvider) {
            $httpProvider.interceptors.push('symfonyTokenInterceptor');
        }]);

})();
