angular.module('ruchJow.security.retryQueue', [])

    // This is a generic retry queue for security failures. Each item is expected to expose two functions: retry and cancel.
    .factory('ruchJowSecurityRetryQueue', ['$q', '$log', function($q, $log) {
        var currentQueue = [],
            nextQueue = [],
            retryCallback,
            handlePromise = null; // May be it should be an array.

        var getNextQueue = function () {
            currentQueue = nextQueue;
            nextQueue = [];
        };

        var handle = function () {
            if (handlePromise || !nextQueue.length) {
                return;
            }

            handlePromise = $q.when(retryCallback())
                ['finally'](function () {
                    getNextQueue();
                })
                .then(function () {
                    while (currentQueue.length) {
                        currentQueue.shift().retryItem.retry();
                    }
                }, function () {
                    while (currentQueue.length) {
                        currentQueue.shift().retryItem.cancel();
                    }
                })
                ['finally'](function () {
                    handlePromise = null;
                    handle();
                });
        };



        var service = {

//            hasMore: function() {
//                return retryQueue.length > 0;
//            },

            setRetryCallback: function (callback) {
                retryCallback = callback;
            },

            /**
             * Wraps function into deferred promise
             * @param retryFn
             * @param reason
             * @returns {*}
             */
            pushRetryFn: function (retryFunction, reason) {
                var deferred = $q.defer();
                var retryFn = retryFunction;

                // Prepare item to be pushed to next queue.
                var retryItem = {
                    reason: reason,
                    retry: function() {
                        // Wrap the result of the retryFn into a promise if it is not already
                        $q.when(retryFn()).then(function(value) {
                            // If it was successful then resolve our deferred
                            deferred.resolve(value);
                        }, function(value) {
                            // Otherwise reject it
                            deferred.reject(value);
                        });
                    },
                    cancel: function() {
                        // Give up on retrying and reject our deferred
                        deferred.reject();
                    }
                };
                service.push(retryItem);

                return deferred.promise;
            },

            push: function(retryItem) {
                nextQueue.push({
                    retryItem: retryItem
                });

                handle();
            } //,
//            retryReason: function() {
//                return service.hasMore() && retryQueue[0].reason;
//            },
//            cancelAll: function() {
//                while(service.hasMore()) {
//                    retryQueue.shift().cancel();
//                }
//            },
//            retryAll: function() {
//                while(service.hasMore()) {
//                    retryQueue.shift().retry();
//                }
//            }
        };
        return service;
    }]);
