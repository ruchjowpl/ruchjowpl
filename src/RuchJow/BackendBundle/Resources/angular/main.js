/**
 * Created by grest on 2/16/14.
 */

angular.module('ruchJow.backend', [
    'ruchJow.backend.config.globals'
])
    .directive('toNumber', function () {
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, ctrl) {
                ctrl.$parsers.push(function (value) {
                    value.replace(' ', '').replace(',', '.');

                    return parseFloat(value || '');
                });
            }
        };
    });



angular.element(document).ready(function() {
    angular.bootstrap(document, ['ruchJow.backend']);
});