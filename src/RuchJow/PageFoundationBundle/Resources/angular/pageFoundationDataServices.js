/**
 * App  data services.
 */


angular.module('ruchJow.pfound.data', [/*'gd.tools', 'ngRoute'*/])
    .factory('ruchJowUserAsyncValidators', ['$http', '$q', function ($http, $q) {

        var validate = function (field, value) {
            var canceler = $q.defer();

            var config = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                method: 'POST',
                url: Routing.generate('page_foundation_cif_user_field_value_unique', { field: field }),
                data: JSON.stringify(value),
                timeout: canceler.promise
            };

            var promise = $http(config).then(function (request) {
                if (request.data === true || request.data === 'true') {
                    return $q.defer().resolve();
                }

                return $q.reject();
            });

            promise.canceler = canceler;

            return promise;
        };

        return {
            nickUnique: function (value) {
                return validate('nick', value);
            },
            emailUnique: function (value) {
                return validate('email', value);
            },
            cancel: function (promise) {
                if (promise.canceler) {
                    promise.canceler.resolve();
                }
            }
        };
    }])
    .factory('ruchJowFindCountries', ['$http', '$q', function ($http, $q) {

        var getCountries = function (value) {
            var canceler = $q.defer();

            var config = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                method: 'POST',
                url: Routing.generate('territorial_units_ajax_countries'),
                data: JSON.stringify(value),
                timeout: canceler.promise
            };

            var promise = $http(config).then(function (request) {
                return request.data;
            });

            promise.canceler = canceler;

            return promise;
        };

        return {
            getCountries: function (value) {
                return getCountries(value);
            },
            cancel: function (promise) {
                if (promise.canceler) {
                    promise.canceler.resolve();
                }
            }
        };
    }])
    .factory('ruchJowFindCommunes', ['$http', '$q', function ($http, $q) {

        var getCommunes = function (value) {
            var canceler = $q.defer();

            var config = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                method: 'POST',
                url: Routing.generate('territorial_units_ajax_communes'),
                data: JSON.stringify(value),
                timeout: canceler.promise
            };

            var promise = $http(config).then(function (request) {
                return request.data;
            });

            promise.canceler = canceler;

            return promise;
        };

        return {
            getCommunes: function (value) {
                return getCommunes(value);
            },
            cancel: function (promise) {
                if (promise.canceler) {
                    promise.canceler.resolve();
                }
            }
        };
    }])
    .factory('ruchJowFindOrganisations', ['$http', '$q', function ($http, $q) {

        var getOrganisations = function (value) {
            var canceler = $q.defer();

            var config = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                method: 'POST',
                url: Routing.generate('user_ajax_organisations'),
                data: JSON.stringify(value),
                timeout: canceler.promise
            };

            var promise = $http(config).then(function (request) {
                return request.data;
            });

            promise.canceler = canceler;

            return promise;
        };

        return {
            getOrganisations: function (value) {
                return getOrganisations(value);
            },
            cancel: function (promise) {
                if (promise.canceler) {
                    promise.canceler.resolve();
                }
            }
        };
    }])
    .factory('ruchJowUserAsyncPasswordValidators', ['$http', '$q', function ($http, $q) {

        var validate = function (field, value) {
            var canceler = $q.defer();

            var config = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                method: 'POST',
                url: Routing.generate('page_foundation_cif_user_password_correct', { field: field }),
                data: JSON.stringify(value),
                timeout: canceler.promise
            };

            var promise = $http(config).then(function (request) {
                if (request.data === true || request.data === 'true') {
                    return $q.defer().resolve();
                }

                return $q.reject();
            });

            promise.canceler = canceler;

            return promise;
        };

        return {
            passwordCheck: function (value) {
                return validate('password', value);
            },
            cancel: function (promise) {
                if (promise.canceler) {
                    promise.canceler.resolve();
                }
            }
        };
    }]);