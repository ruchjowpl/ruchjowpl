
angular.module('ruchJow.security.symfonyData', [])
    .provider('ruchJowSecuritySymfonyData', [
        function () {

            var urls = {};

            var provider = {

                setAuthDataUrl: function (url, method) {
                    urls['authData'] = {
                        url: url,
                        method: method || 'GET'
                    };
                },
                setUserRolesUrl: function (url, method) {
                    urls['userRoles'] = {
                        url: url,
                        method: method || 'GET'
                    };
                },
                $get: ['$http', '$q', function ($http, $q) {
                    var data = {},
                        promises = {};

                    var get = function (name, force) {
                        if (!urls[name]) {
                            return $q.when({});
                        }

                        if (promises[name]) {
                            return promises[name];
                        }

                        if (!data[name] || force) {
                            var config = {
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                url: urls[name].url,
                                method: urls[name].method
                            };

                            promises[name] = $http(config).
                                then(function (response) {

                                    data[name] = response.data === 'null' ?
                                        // We need to fix problem with json null considered as string 'null'.
                                        null : response.data;

                                    return data[name];
                                })
                                ['finally'](function () {
                                    promises[name] = null;
                                });

                            return promises[name];
                        }

                        return $q.when(data[name]);
                    };

                    var service = {

                        getAuthFormData: function (force) {
                            return get('authData', force);
                        },
                        getUserRoles: function (force) {
                            return get('userRoles', force);
                        }

                    };

                    return service;
                }]
            };

            return provider;
        }
    ]);