
angular.module('ruchJow.states', [
    'ui.router',
    'ruchJow.security'
]).
    provider('ruchJowStates', [
        '$stateProvider',
        function ($stateProvider) {

            var defaultState;

            var provider = {
                setDefaultState: function (state) {
                    defaultState = state;
                },
                state: function (name, params, roles, failState) {


                    if (roles) {

                        failState = failState || defaultState;

                        params.resolve = params.resolve || {};
                        params.resolve.ruchJowRolesCheck = [
                            '$q',
                            '$state',
                            'ruchJowSecurity',
                            function ($q, $state, security) {

                                // Check if access is granted, if not then let user login and check again.
                                // if access still isn't granted then redirect.
                                return security.isGrantedAsyncReTry(roles, 'unauthorized-state').
                                    then(function () {
                                        return true;
                                    }, function () {
                                        if (angular.isArray(failState)) {
                                            $state.go(failState[0], failState[1])
                                        } else {
                                            $state.go(failState);
                                        }

                                        return $q.reject('Unauthorized state');
                                    });
                            }
                        ];

                        params.ruchJowRoles = roles;
                    }

                    $stateProvider.state(name, params);

                    return provider;
                },
                $get: ['$state', 'ruchJowSecurity', function ($state, security) {
                    return {
                        isPathAccessible: function (name) {
                            var params = $state.get(name);
                            if (!params) {
                                return false;
                            }

                            return security.isGranted(params.ruchJowRoles);
                        },
                        $state: $state
                    };
                }]
            };


            return provider;
        }
    ])

    .directive('ruchJowLinkBlock', [ 'ruchJowStates', 'ruchJowSecurity', '$animate', function (ruchJowStates, security, $animate) {
        return {
            restrict: 'A',
            scope: true,
            link: function (scope, element, attr) {
                var name = scope.$eval(attr.ruchJowLinkBlock),
                    params,
                    options;

                if (angular.isArray(name)) {
                    params = name[1];
                    options = name[2];
                    name = params[0];
                }


                if (typeof attr.ruchJowLinkHrefVariable === 'string') {
                    var href = ruchJowStates.$state.href(name, params, options);

                    scope[attr.ruchJowLinkHrefVariable] =
                        !href ? null : href;
                }

                var updateClass = function () {
                    if (ruchJowStates.isPathAccessible(name)) {
                        $animate.removeClass(element, 'ruch-jow-link-hide');
                    } else {
                        $animate.addClass(element, 'ruch-jow-link-hide');
                    }
                };

                scope.security = security;

                scope.$watch('security.currentUser', updateClass, true);

                updateClass();
            }
        };
    }]);

!angular.$$csp() && angular.element(document).find('head').prepend('<style type="text/css">@charset "UTF-8";.ruch-jow-link-hide{display:none !important;}</style>');