(function (angular) {
    'use strict';

    angular.module('ruchJow.rss', [])
        .provider('ruchJowRss', [function () {

            var registeredSources = {};

            var provider = {

                registerSource: function (name, url) {
                    if (registeredSources.hasOwnProperty(name)) {
                        throw new Error('Source "' +  name + '" already registered.');
                    }

                    registeredSources[name] = {
                        url: url
                    };
                },

                $get: ['$q', '$http', function ($q, $http) {

                    var x2js;

                    var service = {
                        getFeed: function(name, force) {
                            if (!registeredSources.hasOwnProperty(name)) {
                                throw new Error('Source "' + name + '" is not registered.');
                            }

                            var source = registeredSources[name];

                            if (source.promise) {
                                return promise;
                            }

                            var time = Math.floor((new Date()).getTime() / 1000);

                            if (typeof force === 'number') {
                                force = typeof source.time === 'undefined' || (time - source.time >= force);
                            }

                            if (typeof source.data !== 'undefined' && !force) {
                                return $q.when(source.data);
                            }

                            source.time = time;

                            var httpConfig = {
                                method: 'GET',
                                url: source.url,
                                transformResponse: function (value) {
                                    x2js = x2js || new X2JS();
                                    var data = x2js.xml_str2json(value);

                                    if (!data || !data.rss || !data.rss.channel) {
                                        return $q.reject();
                                    }

                                    return data && data.rss && data.rss.channel && data.rss.channel.item || {};
                                }
                            };

                            source.promise = $http(httpConfig).then(function (Response) {
                                return Response.data;
                            });

                            source.promise['finally'](function () {
                                delete source.promise;
                            });

                            return source.promise;
                        }
                    };

                    return service;

                }]
            };

            return provider;

        }]);
})(angular);
