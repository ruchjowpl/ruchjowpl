/**
 * Created by grest on 9/5/14.
 */

angular.module('ruchJow.homepageActions', [])
    .provider('ruchJowHomepageActions', [function () {

        var actions = {},
            redirectState;

        return {
            register: function (name, action) {
                if (typeof action === 'string') {
                    action = action.split('.');
                    if (action.length !== 2) {
                        throw new Error('Incorrect action definition string format. Expected serviceName.methodName. Given ' + action.join('.'));
                    }
                }
                actions[name] = action;
            },
            setRedirection: function (state) {
                redirectState = state;
            },
            $get: ['$injector', '$q', '$state', function ($injector, $q, $state) {

                var promise;

                return {
                    call: function(actionName, params) {

                        if (promise) {
                            return $q.reject('Another action already in progress.');
                        }

                        if (!actions.hasOwnProperty(actionName)) {
                            promise = $q.reject('Action ' + actionName +' is not defined.');
                        } else {

                            if (angular.isArray(actions[actionName])) {
                                var serviceName = actions[actionName][0],
                                    methodName = actions[actionName][1],
                                    service,
                                    action;
                                try {
                                    service = $injector.get(serviceName);
                                } catch (e) {
                                    throw new Error('Action defined as ' + serviceName + '.' + methodName + ' could not be called,' +
                                    ' because service ' + serviceName + ' is not defined.');
                                }

                                if (!service.hasOwnProperty(methodName)) {
                                    throw new Error('Action ' + methodName + ' of service ' + serviceName + ' is not defined.');
                                }

                                if (typeof service[methodName] !== 'function') {
                                    throw new Error('Expected action ' + methodName + ' of service ' + serviceName + ' is not a function.');
                                }

                                actions[actionName] = service[methodName];
                            }

                            promise = $q.when(actions[actionName].apply(null, params));
                        }

                        return promise['finally'](function () {
                            promise = undefined;

                            if (redirectState) {
                                $state.go(redirectState);
                            }

                            return $q.when();
                        });
                    }
                };
            }]
        };
    }]);