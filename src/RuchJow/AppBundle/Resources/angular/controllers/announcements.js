(function (angular) {
    'use strict';

    angular.module('ruchJow.ctrls.announcements', ['ruchJow.rss'])
        .controller('AnnouncementsCtrl', ['$scope', 'ruchJowRss', function ($scope, ruchJowRss) {
            $scope.status = 'loading';

            ruchJowRss.getFeed('announcements', true).then(function (feed) {
                $scope.feed = feed;
                $scope.status = 'success';
            }, function () {
                $scope.status = 'error';
            });

            $scope.date = function (value) {
                return new Date(value);
            }
        }]);

})(angular);