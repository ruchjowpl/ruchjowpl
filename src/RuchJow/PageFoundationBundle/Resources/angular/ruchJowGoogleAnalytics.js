
angular.module('ruchJow.googleAnalytics', []).
    provider('ruchJowGoogleAnalytics', [
        function () {

            var webPropertyId;

            var provider = {
                setWebPropertyId: function (id) {
                    webPropertyId = id;
                },
                $get: ['$rootScope', '$window', '$location', function ($rootScope, $window, $location) {

                    if (!webPropertyId) {
                        throw new Error('Google Analytics Web Property Id must be defined. (ruchJowGoogleAnalyticsProvider.setWebPropertyId(\'UA-XXXX-Y\'))');
                    }

                    // Initialize Google Analytics object
                    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                    })($window,document,'script','//www.google-analytics.com/analytics.js','__gaTracker');


                    $window[$window['GoogleAnalyticsObject']]('create', webPropertyId, 'auto');

                    $rootScope.$on('$locationChangeSuccess', function (event) {
                        $window[$window['GoogleAnalyticsObject']]('send', 'pageview', { page: $location.path() });
                    });

                    return function (method, webPropertyId, options) {
                        return $window[$window['GoogleAnalyticsObject']](method, webPropertyId, options);
                    };
                }]
            };

            return provider;
        }
    ]);