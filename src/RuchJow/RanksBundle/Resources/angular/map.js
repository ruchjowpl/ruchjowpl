angular.module('ruchJow.ranks', ['ruchJow.tools.mouseFollower'])

    .service('lazyLoadApi', function lazyLoadApi($window, $q) {
        function loadScript() {
            // use global document since Angular's $document is weak
            var s = document.createElement('script');
            s.src = '//maps.googleapis.com/maps/api/js?sensor=false&language=pl&callback=initMaps&libraries=geometry&key=AIzaSyCn3TZU03J_oFvgMEt6Uw0DVqOn5_wzcZA';
            document.body.appendChild(s);
        }
        var deferred = $q.defer();

        $window.initMaps = function() {
            deferred.resolve();
        };

        if (document.readyState === "complete") {
            loadScript();
        } else if ($window.attachEvent) {
            $window.attachEvent('onload', loadScript);
        } else {
            $window.addEventListener('load', loadScript, false);
        }

        globalLazyLoad = deferred.promise;

        return deferred.promise;
    })

    .value('mapOptions', {
        center: { lat: 51.79695260930911, lng: 19.52734375000001 },
        zoom: 6,
        minZoom: 6,
        maxZoom: 14,
        draggable: true,
        disableDoubleClickZoom: true,
        disableDefaultUI: true,
        scrollwheel: false,
        keyboardShortcuts: false,
        backgroundColor: '#ffffff88',
        markers: {
            default: {
                icon: 'images/map/star.png'
            },
            support: {
                icon: 'images/map/star.png'
            },
            referendum_point: {
                icon: 'images/map/referendumPoint.png'
            }
        }
    })

    .directive('ruchJowMap', ['ruchJowTerritorialUnits', 'lazyLoadApi', 'mapOptions', 'ruchJowMouseFollower', function(ruchJowTerritorialUnits, lazyLoadApi, mapOptions, ruchJowMouseFollower) {
        var opts = mapOptions || {};

        return {
            restrict: 'A',
            scope: {
                activeTerritorialUnit: '=',
                //points: '=',
                markersData: '=',
                otherMarkersData: '=',
                highlightedTerritorialUnit: '='

            },
            transclude: true,
            link: function ($scope, elem, attrs,  ctrl, $transclude) {
                var shapes = [];
                var otherMarkers = [];
                var openedInfoWindow;
                var map;
                var mouseOverShape;
                var chosenShape = null;
                var scale; // In m/pixel
                var maxMarkerSizeFactor = 0.02;
                var options = angular.extend({}, opts, $scope.$eval(attrs.options));

                // wait for the API
                lazyLoadApi.then(function() {

                    map = new google.maps.Map(elem[0], options);

                    $transclude(function (clone, newScope) {
                        elem.append(clone);
                    });

                    // Init view (based on map-init-view attr.
                    if (attrs.mapInitView) {
                        var params = attrs.mapInitView.split(':');
                        $scope.activeTerritorialUnit = {
                            type: params[0],
                            id: params[1]
                        }
                    }

                    // Watch for the view changes and load new shapes when necessary.
                    $scope.$watch('activeTerritorialUnit', function (shape) {
                        if (!shape) {
                            return;
                        }
                        loadShape(shape.type, shape.id);
                    }, true);

                    // Watch for the markersDara changes and update markers if necessary.
                    $scope.$watch('markersData', function(data) {
                        if (!data) {
                            return;
                        }
                        updateAllMarkers();
                    }, true);

                    // Watch for the markersDara changes and update markers if necessary.
                    $scope.$watch('otherMarkersData', function(data) {
                        if (!data) {
                            return;
                        }
                        updateOtherMarkers();
                    }, true);
                });


                /**
                 * Loads a shape corresponding to the given type and id.
                 *
                 * The shape will be cut out of the map and its children will be loaded as subshapes if present.
                 * Also it creates markers for all children - markers are initialized invisible and they will
                 * have to be resize and set visible (via opacity).
                 *
                 * @param type
                 * @param id
                 * @param options
                 */
                function loadShape(type, id, options) {
                    var defaultOptions = {
                        interactive: true,
                        outside: {
                            fillColor: '#FFFFFF',
                            fillOpacity: 0.9
                        }
                    };

                    options = angular.extend(defaultOptions, options || {});

                    var routeParams = { type: type };
                    if (id) {
                        routeParams.id = id;
                    }

                    ruchJowTerritorialUnits.getShapeData(type, id)
                        .then(function(data) {
                            clearShapes();

                            var path = google.maps.geometry.encoding.decodePath(data.shape);
                            var poly = new google.maps.Polygon({
                                paths: polyHole(path, ~~(type !== 'country')), // 0 for the whole country, 1 otherwise
                                strokeOpacity: 0,
                                strokeWeight: 0,
                                fillColor: options.outside.fillColor,
                                fillOpacity: options.outside.fillOpacity,
                                clickable: false,
                                map: map
                            });

                            map.fitBounds(getBounds(path));
                            shapes.push(poly);

                            // Formula taken from: https://groups.google.com/forum/#!topic/google-maps-js-api-v3/hDRO4oHVSeM
                            scale =  156543.03392 * Math.cos(map.getCenter().lat() * Math.PI / 180) / Math.pow(2, map.getZoom());
                            globalMap = map;


                            if (typeof data.children !== 'undefined' && data.children.length) {
                                // iterate through the children and display them with boundaries
                                showSubShapes(data.children, options);
                            } else { // if there are no children, show the shape itself
                                showSubShapes([data], angular.extend(options, { interactive: false }));
                            }

                            updateAllMarkers();
                        });
                }


                /**
                 * Iterates through all shapes and updates their markers.
                 */
                function updateOtherMarkers() {

                    angular.forEach(otherMarkers, function(marker, key) {
                        if (typeof $scope.otherMarkersData[key] === 'undefined') {
                            // Remove marker.
                            marker.marker.setMap(null);

                            if (marker.listenerClick) {
                                google.maps.event.removeListener(marker.listenerClick);
                            }
                            delete otherMarkers[key];
                        }
                    });

                    angular.forEach($scope.otherMarkersData, function(markerData, key) {

                        var latlng = new google.maps.LatLng(markerData.lat, markerData.lng);

                        var htmlTitle = markerData.title ? '<div class="map-info-title">' + markerData.title + '</div>' : '';
                        var htmlSubtitle = markerData.subtitle ? '<div class="map-info-subtitle">' + markerData.subtitle + '</div>' : '';
                        var htmlDescription = markerData.description ? '<div class="map-info-description">' +
                            (markerData.link ? '<a href="' +  markerData.link + '">' + markerData.description + '</a>' : markerData.description) +
                            '</div>' : '';
                        var htmlLink = markerData.link ? '<a href="' +  markerData.link + '">' +
                            (markerData.linkTitle ? markerData.linkTitle : markerData.link) +
                            '</a>' : '';

                        var htmlHeader  = htmlTitle || htmlSubtitle ? '<div class="map-info-header">' + htmlTitle + htmlSubtitle + '</div>' : '';
                        var htmlContent = htmlDescription || htmlLink ? '<div class="map-info-content">' + htmlDescription + htmlLink + '</div>' : '';

                        var content = '<div class="map-marker-info-support">' + htmlHeader + htmlContent + '</div>';


                        var title = markerData.title;

                        if (typeof otherMarkers[key] === 'undefined') {

                            var type = markerData.type,
                                icon = options.markers[type] && options.markers[type].icon ?
                                    options.markers[type].icon : options.markers['default'].icon;

                            var newMarker = new google.maps.Marker({
                                position: latlng,
                                icon: icon,
                                title: title,
                                map: map
                            });

                            var newInfoData = {
                                content: content,
                                minHeight: 150,
                                maxWidth: 300,
                                padding: 0,
                                borderRadius: 0
                            };

                            otherMarkers[key] = {
                                marker: newMarker,
                                infoWindowData: newInfoData
                            };

                            otherMarkers[key].listenerClick = google.maps.event.addListener(
                                otherMarkers[key].marker,
                                'click',
                                function () {
                                    if (!otherMarkers[key].infoWindow) {
                                        otherMarkers[key].infoWindow = new InfoBubble(otherMarkers[key].infoWindowData);
                                    }

                                    if (openedInfoWindow) {
                                        openedInfoWindow.close();
                                    }

                                    openedInfoWindow = otherMarkers[key].infoWindow;
                                    otherMarkers[key].infoWindow.open(
                                        map,
                                        otherMarkers[key].marker
                                    );
                                }

                            );

                        } else {
                            otherMarkers[key].marker.set('position', latlng);
                            otherMarkers[key].infoWindow.set('content', content);
                        }

                    });

                }


                /**
                 * Iterates through all shapes and updates their markers.
                 */
                function updateAllMarkers() {
                    var maxValue =  0;
                    var cnt = 0;
                    var minSize = 0;
                    var maxSize = 0;
                    var avgSize = 0;
                    angular.forEach(shapes, function(shape) {
                        if (!shape.shape || !shape.marker) {
                            return;
                        }

                        cnt++;
                        avgSize += Math.max(shape.shape.size, 0);
                        if (minSize == 0 || shape.shape.size < minSize) {
                            minSize = shape.shape.size;
                        }



                        var territorialUnitId = shape.shape.territorial_unit_id;

                        if (
                            typeof $scope.markersData !== 'undefined'
                            && typeof $scope.markersData[territorialUnitId] !== 'undefined'
                            && typeof $scope.markersData[territorialUnitId].total !== 'undefined'
                        ) {
                            var value = $scope.markersData[territorialUnitId].total;

                            if (value && value > maxValue) {
                                maxValue = value;
                            }
                        }
                    });


                    if (cnt) {
                        avgSize /= cnt;

                        var normalizer = maxValue > 0 ? 1/maxValue : 0;
                        var cntFactor = 16/cnt; // 16 is a default region count.
                        var factor = avgSize / scale * 3000 * 10;
                        angular.forEach(shapes, function (shape) {
                            if (!shape.shape || !shape.marker) {
                                return;
                            }

                            updateMarker(shape, normalizer, factor);
                        });
                    }

                }

                /**
                 * Updates marker for given shape.
                 *
                 * @param shape
                 * @param normalizer
                 */
                function updateMarker(shape, normalizer, cntFactor) {

                    if (!shape.shape || !shape.marker) {
                        return;
                    }

                    var marker = shape.marker;


                    var territorialUnitId = shape.shape.territorial_unit_id;

                    if (
                        typeof $scope.markersData === 'undefined'
                        || typeof $scope.markersData[territorialUnitId] === 'undefined'
                        || typeof $scope.markersData[territorialUnitId].total === 'undefined'
                    ) {
                        marker.set('icon', {
                            path: google.maps.SymbolPath.CIRCLE,
                            strokeWeight: 0,
                            fillOpacity: 0,
                            scale: 0
                        });

                        return;
                    }

                    var points = $scope.markersData[territorialUnitId].total;

                    var circle = {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillOpacity: 0.7,
                        fillColor: '#d94d3f',
                        strokeOpacity: 0.8,
                        strokeColor: '#d94d3f',
                        strokeWeight: 1.0,
                        // scale: points * (scale * maxMarkerSizeFactor) * normalizer
                        scale: markerSizeDistribution(points * normalizer) * cntFactor
                        //scale: points * normalizer * 40
                        //scale: points * 36/(map.getZoom() * map.getZoom()) * normalizer * 40
                    };

                    marker.set('icon', circle);
                }

                function markerSizeDistribution(x) {
                    return x <= 0 ? 0 : 0.05 + 0.95 * Math.pow(x, 0.8);
                }

                /**
                 * Creates an anti-shape, i.e. a hole.
                 *
                 * @param path Path to be cut off.
                 * @param dir  Direction - if not dir then points are added in reverse order.
                 *
                 * @returns {*}
                 */
                function polyHole(path, dir) {
                    if (!path instanceof Array) {
                        return path;
                    }

                    // Some very big area
                    var outer = [
                        { lat:  80, lng: -80 },
                        { lat:  80, lng: 100 },
                        { lat: -80, lng: 100 },
                        { lat: -80, lng: -80 },
                        { lat:  80, lng: -80 }
                    ];

                    // Correct choosing of clockwise or counterclockwise direction
                    // of points in the inner shape is necessary.
                    if (dir) {
                        path = outer.concat(path);
                    }
                    else {
                        path = outer.concat(path.reverse());
                    }

                    return path;
                }

                /**
                 * Shows sub-shapes.
                 *
                 * Initializes markers. Adds click listener and shape highlighting.
                 *
                 * @param subShapes
                 * @param options
                 */
                function showSubShapes(subShapes, options) {
                    var defaultOptions = {
                        strokeColor: "#6666bb",
                        strokeOpacity: 1,
                        strokeWeight: 1,
                        fillColor: "#008eff",
                        fillOpacity: 0.0,
                        interactive: true
                    };

                    options = angular.extend(defaultOptions, options || {});

                    for (var i = 0; i < subShapes.length; i++) {
                        var shape = subShapes[i];
                        var boundary = google.maps.geometry.encoding.decodePath(shape.shape);

                        if (
                            (
                                !shape.center ||
                                !angular.isArray(shape.center) ||
                                shape.center.length !== 2
                            ) ||
                            !shape.size
                        ) {
                            var bounds = getBounds(boundary);

                            if (
                                !shape.center ||
                                !angular.isArray(shape.center) ||
                                shape.center.length !== 2
                            ) {
                                var center = getBounds(boundary).getCenter();
                                shape.center = [center.lat(), center.lng()];
                            }

                            if (!shape.size) {
                                var ne = bounds.getNorthEast(),
                                    sw = bounds.getSouthWest();

                                shape.size = Math.min(
                                    ne.lat() - sw.lat(),
                                    ne.lng() - sw.lng()
                                );
                            }
                        }

                        var poly = new google.maps.Polygon({
                            paths: boundary,
                            strokeColor: options.strokeColor,
                            strokeOpacity: options.strokeOpacity,
                            strokeWeight: options.strokeWeight,
                            fillColor: options.fillColor,
                            fillOpacity: options.fillOpacity,
                            map: map,
                            shape: shape // save the reference

                        });
                        shapes.push(poly);


                        // Prepare marker and sotore reference to it in poly.
                        var circle = {
                            path: google.maps.SymbolPath.CIRCLE,
                            strokeWeight: 0,
                            fillOpacity: 1,
                            fillColor: '#ff0000',
                            scale: scale / 40
                        };

                        poly.marker = new google.maps.Marker({
                            icon: circle,
                            position: new google.maps.LatLng(shape.center[0], shape.center[1]),
                            map: map,
                            parent: poly
                        });


                        // Interactive
                        if (options.interactive) {
                            var shapeClickCallback = function(event) {
                                var shape = typeof this.shape !== 'undefined' ?
                                    this.shape :
                                    this.parent.shape;

                                $scope.$apply(function () {
                                    $scope.activeTerritorialUnit = {
                                        type: shape.type,
                                        id: shape.territorial_unit_id,
                                        name: shape.territorial_unit_name
                                    };
                                });
                            };

                            google.maps.event.addListener(poly, 'click', shapeClickCallback);
                            google.maps.event.addListener(poly.marker, 'click', shapeClickCallback);

                            // Shape highlighting;
                            google.maps.event.addListener(poly, 'mouseover', function(event) {
                                if (mouseOverShape) {
                                    mouseOverShape.set('fillOpacity', 0);
                                }
                                mouseOverShape = this;
                                this.set('fillOpacity', 0.3);

                                var shape = this.shape;

                                ruchJowMouseFollower.show(shape.territorial_unit_name);
                            });

                            google.maps.event.addListener(poly, 'mouseout', function(event) {
                                ruchJowMouseFollower.hide();
                            });
                        }

                    }
                }

                /**
                 * Remove all shapes from the map.
                 */
                function clearShapes() {
                    for (var i = 0; i < shapes.length; i++) {
                        if (shapes[i].marker) {
                            shapes[i].marker.setMap(null);
                            delete shapes[i].marker;
                        }

                        shapes[i].setMap(null);
                        delete shapes[i];
                    }
                    shapes = [];
                }

                /**
                 * Creates a LatLngBounds object and passes all path points to it so it represents coordinates of a box that the shape would fit in.
                 *
                 * @param path
                 * @returns {google.maps.LatLngBounds}
                 */
                function getBounds(path) {
                    if (path instanceof google.maps.MVCArray) {
                        path = path.getArray();
                    }
                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < path.length; i++) {
                        bounds.extend(path[i]);
                    }

                    return bounds;
                }
            }
        };
    }]);

