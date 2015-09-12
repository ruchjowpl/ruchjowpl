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

            function checkLoginState(allowLogin) {

                var promise = service.fb.then(function (FB) {
                    var deferred = $q.defer();

                    console.log('check login');

                    FB.getLoginStatus(function (response) {
                        console.log('check login state: facebook returned:');
                        console.log(response);
                        deferred.resolve(response);
                    });

                    return deferred.promise;
                }).then(function (response) {
                    console.log('check login state: parse response');

                    if (response.status !== 'connected') {
                        console.log('state !== connected - rejected (' + status + ')');
                        return $q.reject(response);
                    }

                    console.log('state === connected - pass response');
                    return response;
                });

                if (allowLogin) {
                    return promise.then(null, function (response) {
                        console.log('check login status - reject - try again');
                        console.log(response);

                        if (response.status === 'unknown') {
                            console.log('connect');

                            return connect().then(function () {
                                console.log('connect successful');
                                return checkLoginState(false);
                            }, function () {
                                console.log('connect failed');
                            })
                        }
                    });
                }

                return promise;
            }

            function connect(email) {
                console.log('1');
                return service.fb.then(function (FB) {
                    console.log('2');
                    var deferred = $q.defer();


                    console.log(FB);
                    FB.login(function (response) {
                        console.log('asdfsdfsdf');

                        deferred.resolve(response);
                    }, {scope: 'public_profile' + (email ? ',email' :'')});

                    return deferred.promise;
                }).then(function (response) {
                    console.log('3');
                    if (response.authResponse) {
                        console.log('4');
                        return response;
                    } else {
                        console.log('5');
                        return $q.reject('Fb error');
                    }
                }, function () {
                    console.log('6');
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