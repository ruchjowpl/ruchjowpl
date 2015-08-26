(function (angular) {
    'use strict';

    angular.module('ruchJow.config', [
        'ruchJow.config.routing',
        'ruchJow.config.security',
        'ruchJow.config.globals',
        'ruchJow.rss',
        'fr.angUtils.timeEvents',
        'ruchJow.facebook'
    ])
        .config(['frTimeEventsProvider', function (frTimeEventsProvider) {
            // FIXME Change time to 5min instead of 30sec.
            frTimeEventsProvider.registerTimer('messages:refresh', 0.5 * 60 * 1000, true, false);
        }])
        .config(['ruchJowRssProvider', function (ruchJowRssProvider) {
            ruchJowRssProvider.registerSource('announcements', Routing.generate('feed_announcements'));
        }])
        .run(['facebook', function (facebook) {

            console.log('asdfasdf');
            facebook.get().then(function (FB) {
                console.log(FB);
            });

        }])
    ;

})(angular);