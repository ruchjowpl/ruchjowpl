(function (angular) {
    'use strict';

    angular.module('ruchJow.ctrls.announcements', ['ruchJow.rss'])
        .controller('AnnouncementsCtrl', ['$scope', 'ruchJowRss', function ($scope, ruchJowRss) {
            $scope.status = 'loading';

            ruchJowRss.getFeed('announcements', true).then(function (feed) {
                $scope.feed = feed.slice(0, 3);
                $scope.status = 'success';

            }, function () {
                $scope.status = 'error';
            });

            $scope.date = function (value) {
                return new Date(value);
            };
            $scope.description = function (item) {
                var result = item.description.__cdata.split('<p>');
                result = result[1].replace('</p>','');
                result = result+' <a href="'+item.link+'" target="_blank">czytaj dalej</a>';
                return result;
            };
        }]);

})(angular);