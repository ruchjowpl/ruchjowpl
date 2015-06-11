
angular.module('ruchJow.ctrls.challenges.inviteFriends', [])
    .controller('ChallengesInviteFriendsCtrl', [
        '$scope',
        function ($scope) {

        }
    ])
    .controller('ChallengesInviteFriendsBannersCtrl', [
        '$scope',
        'ruchJowSecurity',
        '$location',
        function ($scope, ruchJowSecurity, $location) {

            var urlPrefix = $location.protocol() + '://' +
                $location.host() + ($location.port() !== 80 ? ':' + $location.port() : '');


            $scope.banners = [
                {
                    imgUrl: urlPrefix + '/images/banners/banner_jow_250_250.jpg',
                    html: ''
                },
                {
                    imgUrl: urlPrefix + '/images/banners/banner_jow_300_250.jpg',
                    html: ''
                },
                {
                    imgUrl: urlPrefix + '/images/banners/banner_jow_336_280.jpg',
                    html: ''
                },
                {
                    imgUrl: urlPrefix + '/images/banners/banner_jow_468_60.jpg',
                    html: ''
                },
                {
                    imgUrl: urlPrefix + '/images/banners/banner_jow_750_100.jpg',
                    html: ''
                },
                {
                    imgUrl: urlPrefix + '/images/banners/banner_jow_728_90.jpg',
                    html: ''
                },
                {
                    imgUrl: urlPrefix + '/images/banners/banner_jow_750_200.jpg',
                    html: ''
                }
            ];

            angular.forEach($scope.banners, function (bannerDef, id) {
               $scope.$watch('banners[' + id + '].html', function () {
                   bannerDef.html = prepareBannersHtml(bannerDef);
               })
            });

            function prepareBannersHtml(bannerDef) {
                return '' +
                    '<a href="' + ruchJowSecurity.currentUser.referralUrl + '">' +
                    '<img src="' + bannerDef.imgUrl + '"/>' +
                    '</a>';
            }
        }
    ])
    .controller('ChallengesInviteFriendsLinkCtrl', [
        '$scope',
        'ruchJowSecurity',
        function ($scope, ruchJowSecurity) {
            $scope.referralUrl = ruchJowSecurity.currentUser.referralUrl;

            $scope.$watch('referralUrl', function () {
                $scope.referralUrl = ruchJowSecurity.currentUser.referralUrl;
            });
        }
    ])
    .controller('ChallengesInviteFriendsEmailCtrl', [
        '$scope',
        '$http',
        '$alert',
        function ($scope, $http, $alert) {

            $scope.emailPattern = /^[a-zA-Z0-9.!#$%&'*+/?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;

            $scope.maxEmails = 10;

            $scope.newEmail = null;

            $scope.emails = [];
            $scope.emailsMap = {};

            $scope.inProgress = false;

            $scope.clean = function () {
                $scope.newEmail = null;
                $scope.emails = [];
                $scope.emailsMap = {};
            };

            $scope.addEmail = function () {
                if (!$scope.newEmail || $scope.isExceeded()) {
                    return;
                }
                if (angular.isUndefined($scope.emailsMap[$scope.newEmail])) {
                    $scope.emailsMap[$scope.newEmail] = $scope.emails.push($scope.newEmail) - 1;
                }

                $scope.newEmail = null;
            };

            $scope.removeEmail = function (email) {
                var index = $scope.emailsMap[email]

                if (angular.isUndefined(index)) {
                    return;
                }

                $scope.emails.splice(index, 1);
                delete $scope.emailsMap[email];

                for (var i = index; i < $scope.emails.length; i++) {
                    $scope.emailsMap[$scope.emails[i]] = i;
                }
            };

            $scope.sendEmails = function () {
                if (!$scope.emails.length) {
                    return;
                }

                $scope.inProgress = true;

                var config = {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    method: 'POST',
                    url: Routing.generate('user_ajax_invite_friends'),
                    data: $scope.emails
                };

                $http(config).then(function (request) {
                    var cnt = $scope.emails.length;
                    $scope.clean();
                    $alert(['supportForm.invite_friends.sent.confirmation.message', { NUMBER: cnt }, 'messageformat']);

                    return request.data;
                })['finally'](function () {
                    $scope.inProgress = false;
                });
            };

            $scope.isExceeded = function () {
                return $scope.emails.length >= $scope.maxEmails
            };

        }
    ])

;