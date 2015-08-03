(function () {
    'use strict';

    angular.module('ruchJow.tuData', ['fr.angUtils.data'])
        .config(['frDataProvider', function (frDataProvider) {
            frDataProvider.register(
                'tu.search:communeDistrictRegion',
                ['search', function (search) {
                    return {
                        url: Routing.generate('territorial_units_ajax_search_tu'),
                        method: 'POST',
                        data: JSON.stringify(search)
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
                        return $q.reject('Incorrect response.')
                    }

                    return data.data;
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
                        return $q.reject('Incorrect response.')
                    }

                    return data.data;
                }]
            );
        }]);

})();
