(function () {
    "use strict";

    angular.module('ruchJow.ctrls.jowEvents', ['fr.angUtils.data'])
        .config(['frDataProvider', function (frDataProvider) {
            frDataProvider.register(
                'jowEvents:get',
                ['limit', 'offset', function (limit, offset) {
                    var urlParams = {
                        limit: limit || 0
                    };

                    if (offset) {
                        urlParams.offset = offset;
                    }

                    return {
                        url: Routing.generate('app_cif_jow_events_list', urlParams),
                        method: 'GET'
                    };
                }],
                ['$q', 'response', function ($q, response) {
                    var data = response.data;

                    if (
                        data.status === undefined
                        || data.status !== 'success'
                    ) {
                        return $q.reject(data.message);
                    }

                    if (
                        !data.hasOwnProperty('data')
                        || !angular.isObject(data.data)
                    ) {
                        return $q.reject('Incorrect response format.')
                    }

                    return data.data;
                }]
            )
        }])
        .controller('JowEventsController', ['$scope', 'frData', function ($scope, frData) {

            $scope.events = [];

            console.log('Controller');

            $scope.getEvents = function (limit, offset) {
                console.log('getEvents');

                frData.getParametrized('jowEvents:get', { limit: limit, offset: offset}).then(function (events) {
                    $scope.events = events;
                });
            }
        }]);
})();