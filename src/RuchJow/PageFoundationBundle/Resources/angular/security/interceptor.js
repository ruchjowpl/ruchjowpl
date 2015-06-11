angular.module('ruchJow.security.interceptor', ['ruchJow.security.retryQueue'])

// This http interceptor listens for authentication failures
    .factory('securityInterceptor', ['$injector', '$q', 'ruchJowSecurityRetryQueue', function($injector, $q, queue) {
        return {
            responseError: function (rejection) {
                if (rejection.status === 401) {
                    // The request bounced because it was not authorized - add a new request to the retry queue
                    return queue.pushRetryFn(function retryRequest() {
                        // We must use $injector to get the $http service to prevent circular dependency
                        return $injector.get('$http')(rejection.config);
                    }, 'unauthorized-server');
                }

                return $q.reject(rejection);
            }
        };



        //return function(promise) {
        //    // Intercept failed requests
        //    return promise.then(null, function(originalResponse) {
        //        if (originalResponse.status === 401) {
        //            // The request bounced because it was not authorized - add a new request to the retry queue
        //            promise = queue.pushRetryFn(function retryRequest() {
        //                // We must use $injector to get the $http service to prevent circular dependency
        //                return $injector.get('$http')(originalResponse.config);
        //            }, 'unauthorized-server');
        //        }
        //        return promise;
        //    });
        //};
    }])

// We have to add the interceptor to the queue as a string because the interceptor depends upon service instances that are not available in the config block.
    .config(['$httpProvider', function($httpProvider) {
        $httpProvider.interceptors.push('securityInterceptor');
    }]);