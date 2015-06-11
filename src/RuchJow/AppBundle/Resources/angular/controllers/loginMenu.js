
angular.module('ruchJow.ctrls.loginMenu', []).
    controller('LoginMenuCtrl', [
        '$scope',
        'ruchJowSecurity',
        function ($scope, ruchJowSecurity) {

            $scope.register = function () {
                ruchJowSecurity.register();
            };

            $scope.login = function () {
                ruchJowSecurity.login();
            };

            $scope.logoutInProgress = false;
            $scope.logout = function () {
                $scope.logoutInProgress = true;
                ruchJowSecurity.logout().
                    then(null, function (msg) {

                    })
                    ['finally'](function () { // IE8 treats finally as keyword so it must be accessed this way.
                    $scope.logOutInProgress = false;
                });
            }
        }
    ]);