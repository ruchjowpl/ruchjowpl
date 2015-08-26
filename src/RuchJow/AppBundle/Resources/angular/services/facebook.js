(function () {
    'use strict';

    angular.module('ruchJow.facebook', ['ruchJow.parameters'])
        .provider('facebook', facebook);

    function facebook() {
        var provider =  {
            $get: $get
        };

        $get.$inject = ['$q', '$window', 'ruchJowParameters'];

        function $get($q, $window, ruchJowParameters) {
            var service = {
                fb: null,
                checkLogin: checkLoginState,
                login: login,
                get: get
            };

            // init FB
            if ($window.FB) {

                init($window.FB);
                service.fb = $q.when($window.FB);
            } else {

                var deferred = $q.defer();
                service.fb = deferred.promise.then(function (FB) {
                    console.log('znaleziony');
                    init(FB);

                    return FB;
                });

                $window.fbAsyncInit = function () {
                    console.log('fbAsyncInit');
                    console.log(FB);
                    deferred.resolve(FB);
                }
            }

            function checkLoginState() {
                service.fb.then(function (FB) {
                    var deferred = $q.defer();

                    console.log('check login');
                    console.log(FB);

                    var getstatus  = FB.getLoginStatus(function (response) {
                        console.log('facebook returned:');
                        console.log(response);
                        deferred.resolve(response);
                    });

                    console.log(getstatus);

                    return deferred.promise;
                }).then(function (response) {
                    console.log('Facebook check login state:');
                    console.log(response);
                });
            }

            function login() {
                service.fb.then(function (FB) {
                    var deferred = $q.defer();

                    FB.login(function (response) {
                        deferred.resolve(response);
                    }, {scope: 'public_profile,email'});

                    return deferred.promise;
                }).then(function (response) {
                    console.log('Facebook login response:');
                    console.log(response);
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