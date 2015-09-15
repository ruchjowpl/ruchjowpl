(function () {
    'use strict';

    angular.module('ruchJow.facebook', ['ruchJow.parameters'])
        .provider('facebook', facebook);

    function facebook() {
        var provider =  {
            $get: $get
        };

        $get.$inject = ['$q', '$http', '$window', 'ruchJowParameters'];

        function $get($q, $http, $window, ruchJowParameters) {
            var service = {
                fb: null,
                checkLogin: checkLoginState,
                connect: connect,
                get: get
            };

            // init FB
            if ($window.FB) {

                init($window.FB);
                service.fb = $q.when($window.FB);
            } else {

                var deferred = $q.defer();
                service.fb = deferred.promise.then(function (FB) {
                    init(FB);

                    return FB;
                });

                $window.fbAsyncInit = function () {
                    deferred.resolve(FB);
                }
            }

            function checkLoginState(allowLogin) {

                var promise = service.fb.then(function (FB) {
                    var deferred = $q.defer();

                    FB.getLoginStatus(function (response) {
                        deferred.resolve(response);
                    });

                    return deferred.promise;
                }).then(function (response) {

                    if (response.status !== 'connected') {
                        return $q.reject(response);
                    }

                    return response;
                });

                if (allowLogin) {
                    return promise.then(null, function (response) {

                        if (response.status === 'unknown') {
                            return connect().then(function () {
                                return checkLoginState(false);
                            })
                        }
                    });
                }

                return promise;
            }

            function connect(email) {
                return service.fb.then(function (FB) {
                    var deferred = $q.defer();

                    FB.login(function (response) {
                        deferred.resolve(response);
                    }, {scope: 'public_profile' + (email ? ',email' :'')});

                    return deferred.promise;
                }).then(function (response) {
                    if (response.authResponse) {
                        return response;
                    } else {
                        return $q.reject('Fb error');
                    }
                });
            }

            function get() {
                return service.fb;
            }

            function init(FB) {
                FB.init({
                    appId: ruchJowParameters('facebook_client_id'),
                    cookie: true,
                    xfbml: true,
                    version: 'v2.4'
                });
            }

            return service;
        }

        return provider;
    }
})();