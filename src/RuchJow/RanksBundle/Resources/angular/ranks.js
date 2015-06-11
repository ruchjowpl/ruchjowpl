angular.module('ruchJow.ctrls.ranks', ['ui.bootstrap', 'ruchJow.security'])
    .provider('ruchJowLocalGov', [function () {

        var cache = {},
            shapesUrl = Routing.generate('local_gov_ajax_support');

        var provider = {

            setShapesUrl: function (url) {
                shapesUrl = url;
            },
            $get: ['$q', '$http', function ($q, $http) {
                var service = {
                    getMarkersData: function (unitType, unitId) {
                        return getSupportData(unitType, unitId);
                    }
                };

                return service;

                function getSupportData(unitType, unitId) {
                    var unitIdKey = unitId || 'null';

                    if (
                        cache.hasOwnProperty(unitType) &&
                        cache[unitType].hasOwnProperty(unitIdKey)

                    ) {
                        return cache[unitType][unitIdKey];
                    }

                    var config = {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        method: 'POST',
                        url: shapesUrl,
                        data: JSON.stringify(
                            {
                                type: unitType,
                                id: unitId
                            }
                        )
                    };

                    if (typeof cache[unitType] === 'undefined') {
                        cache[unitType] = {};
                    }
                    cache[unitType][unitIdKey] = $http(config).then(function (response) {
                        cache[unitType][unitIdKey] = $q.when(response.data);

                        return response.data;
                    });

                    return cache[unitType][unitIdKey];
                }
            }]
        };

        return provider;

    }])
    .provider('ruchJowTerritorialUnits', [function () {
        var cache = {},
            shapesUrl = Routing.generate('territorial_units_geo_shapes');

        var provider = {

            setShapesUrl: function (url) {
                shapesUrl = url;
            },
            $get: ['$q', '$http', function ($q, $http) {
                var service = {
                    getShapeData: function (unitType, unitId) {
                        return getTUData(unitType, unitId);
                    },
                    getName: function (unitType, unitId) {
                        if (unitType === 'country') {
                            return $q.when('whole country');
                        }

                        return getTUData(unitType, unitId).then(function (data) {
                            return data.name;
                        });
                    },
                    getParent: function (unitType, unitId) {
                        if (unitType === 'country') {
                            return $q.when(null);
                        }

                        if (unitType === 'region') {
                            return $q.when({
                                type: 'country',
                                id: null,
                                name: 'whole country'
                            });
                        }

                        return getTUData(unitType, unitId).then(function (data) {

                            var ret = {};

                            if (unitType === 'district') {
                                ret.type = 'region';
                                ret.id = data.region.id;
                                ret.name = data.region.name;
                            } else {
                                ret.type = 'district';
                                ret.id = data.district.id;
                                ret.name = data.district.name;
                            }

                            return ret;
                        });
                    },
                    getChildren: function (unitType, unitId) {
                        return getTUData(unitType, unitId).then(function (data) {
                            var ret = [];
                            if (data.children) {
                                angular.forEach(data.children, function (child) {
                                    ret.push({
                                        type: child.type,
                                        id: child.territorial_unit_id,
                                        name: child.name
                                    })
                                })
                            }

                            return ret;
                        });
                    }
                };

                return service;

                function getTUData(unitType, unitId) {
                    if (
                        cache.hasOwnProperty(unitType) &&
                        cache[unitType].hasOwnProperty(unitId)

                    ) {
                        return cache[unitType][unitId];
                    }

                    var config = {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        method: 'POST',
                        url: shapesUrl,
                        data: JSON.stringify(
                            {
                                type: unitType,
                                id: unitId
                            }
                        )
                    };

                    if (typeof cache[unitType] === 'undefined') {
                        cache[unitType] = {};
                    }
                    cache[unitType][unitId]  = $http(config).then(function (response) {
                        cache[unitType][unitId] = $q.when(response.data);

                        return response.data;
                    });

                    return cache[unitType][unitId];
                }

            }]
        };

        return provider;
    }])
    .provider('ruchJowTUStatistics', [function () {

        var cache = {},
            dataUrl = Routing.generate('ranks_ajax_unit_statistics');


        var provider = {

            setUrl: function (url) {
                dataUrl = url;
            },

            $get: ['$q', '$http', function ($q, $http) {
                var service = {
                    getStatistics: function (unitType, unitId) {

                        if (
                            cache.hasOwnProperty(unitType) &&
                            cache[unitType].hasOwnProperty(unitId)
                        ) {
                            return cache[unitType][unitId];
                        }

                        var config = {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            method: 'POST',
                            url: dataUrl,
                            data: JSON.stringify(
                                {
                                    type: unitType,
                                    id: unitId
                                }
                            )
                        };

                        if (typeof cache[unitType] === 'undefined') {
                            cache[unitType] = {};
                        }
                        cache[unitType][unitId] = $http(config).then(function (response) {
                            cache[unitType][unitId] = $q.when(response.data);

                            return response.data;
                        });

                        return cache[unitType][unitId];
                    },
                    clearCache: function () {
                        cache = [];
                    }
                };

               return service;
            }]
        };

        return provider;
    }])
    .provider('ruchJowGeneralRanks', [function () {
        var cache = {},
            dataUrl = Routing.generate('ranks_ajax_ranks');

        var saveToCache = function (type, level, limit, data) {
            var levelKey = level.type + (level.id || '');

            cache[type] = cache[type] || {};
            cache[type][levelKey] = {
                limit: limit,
                data: data
            };
        };

        var getFromCache = function (type, level, limit) {
            var levelKey = level.type + (level.id || '');

            if (
                typeof cache[type] === 'undefined' ||
                typeof cache[type][levelKey] === 'undefined' ||
                cache[type][levelKey].limit < limit
            ) {
                return false;
            }

            var i = Math.min(limit, cache[type][levelKey].data.length), ret = [];
            while (i--) { ret[i] = cache[type][levelKey].data[i]; }

            return ret;
        };

        var provider = {

            $get: ['$http', '$q', function ($http, $q) {

                var service = {
                    getRank: function (type, level, limit, $page) {

                        level = level || { type: 'country' };

                        //var ret = getFromCache(type, level, limit);
                        //if (ret !== false) {
                        //    return $q.when(ret);
                        //}

                        var canceller = $q.defer();

                        var config = {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            method: 'POST',
                            url: dataUrl,
                            data: JSON.stringify(
                                {
                                    type: type,
                                    level: level || { type: 'country' },
                                    limit: limit || 10,
                                    page: $page || 1
                                }
                            ),
                            timeout: canceller.promise
                        };

                        var promise = $http(config).then(function (request) {
                            //saveToCache(type, level, limit, request.data);
                            return request.data;
                        });

                        promise.canceller = canceller;

                        return promise

                    },
                    cancel: function (promise) {
                        if (typeof promise.canceller !== 'undefined') {
                            promise.canceller.resolve("Request cancelled");
                        }
                    },
                    updateRankingObj: function(rankingObj, level) {
                        if (rankingObj.promise) {
                            service.cancel(rankingObj.promise);
                        }

                        rankingObj.promise = service.getRank(
                            rankingObj.type,
                            level,
                            rankingObj.limit * (rankingObj.columns || 1),
                            rankingObj.page
                        );

                        return rankingObj.promise.then(function (data) {
                            rankingObj.ranking = data.ranking;
                            rankingObj.pages = data.pages;
                            rankingObj.page = data.page;
                            rankingObj.highlighted = data.highlighted;

                            rankingObj.promise = null;
                        });
                    }
                };

                return service;

            }]
        };

        return provider;
    }])
    .controller('RanksFullCtrl', [
        '$scope',
        '$http',
        'ruchJowTUStatistics',
        'ruchJowTerritorialUnits',
        'ruchJowGeneralRanks',
        'ruchJowSecurity',
        function ($scope, $http, ruchJowTUStatistics, ruchJowTerritorialUnits, ruchJowGeneralRanks, security) {
            $scope.activeTerritorialUnit = {type: 'country', name: 'Polska', id:  null};

            $scope.markersData = {};
            $scope.parent = null;
            $scope.children = null;




            // RANKING: NATIONWIDE USER
            $scope.nationwideUserRanking = {
                limit: 10,
                columns: 1,
                type: 'user',
                reset: function () {
                    this.loading = false;
                    this.page = 1;
                    this.totalPages = 1;
                    this.ranking = null;
                    this.highlighted = null;
                },
                update: function () {
                    this.loading = true;
                    var rankingObj = this;
                    updateRankingObj(this)['finally'](function () {
                        rankingObj.loading = false;
                    });
                }
            };
            $scope.nationwideUserRanking.reset();
            $scope.$watch("[nationwideUserRanking.page, nationwideUserRanking.limit]", function () {
                $scope.nationwideUserRanking.update();
            }, true);

            // RANKING: Organisation
            $scope.organisationRanking = {
                limit: 6,
                type: 'organisation',
                loading: false,
                reset: function () {
                    this.page = 1;
                    this.totalPages = 1;
                    this.ranking = null;
                    this.highlighted = null;
                },
                update: function () {
                    this.loading = true;
                    var rankingObj = this;
                    ruchJowGeneralRanks.updateRankingObj(this)['finally'](function () {
                        rankingObj.loading = false;
                    });
                }
            };
            $scope.organisationRanking.reset();
            $scope.$watch("[organisationRanking.page, organisationRanking.limit]", function () {
                $scope.organisationRanking.update();
            });

            // RANKING: NATIONWIDE TU
            $scope.nationwideTerritorialUnitRanking = {
                limit: 10,
                columns: 1,
                loading: false,
                type: 'region',
                availableTypes: ['region', 'district', 'commune'],
                reset: function () {
                    this.page = 1;
                    this.totalPages = 1;
                    this.ranking = null;
                    this.highlighted = null;
                },
                update: function () {
                    this.loading = true;
                    var rankingObj = this;
                    updateRankingObj(this)['finally'](function () {
                        rankingObj.loading = false;
                    });
                },
                setType: function (type) {
                    if (type === 'region' || type === 'district' || type == 'commune') {
                        this.type = type;
                        this.update();
                    }
                }
            };
            $scope.nationwideTerritorialUnitRanking.reset();
            $scope.$watch("[nationwideTerritorialUnitRanking.page, nationwideTerritorialUnitRanking.limit]", function () {
                $scope.nationwideTerritorialUnitRanking.update();
            }, true);



            // USER RANKING
            $scope.userRanking = {
                limit: 11,
                type: 'user',
                reset: function () {
                    this.loading = false;
                    this.page = 1;
                    this.totalPages = 1;
                    this.ranking = null;
                    this.highlighted = null;
                },
                update: function () {
                    this.loading = true;
                    var rankingObj = this;
                    updateRankingObj(this)['finally'](function () {
                        rankingObj.loading = false;
                    });
                }
            };
            $scope.userRanking.reset();
            $scope.$watch("[userRanking.page, userRanking.limit]", function () {
                $scope.userRanking.update();
            });

            // RANKING: TU
            $scope.territorialUnitRanking = {
                limit: 10,
                columns: 1,
                types: {
                    list: ['country', 'region', 'district', 'commune'],
                    map: {'country': 0, 'region': 1, 'district': 2, 'commune': 3},
                    isAllowed: function (levelType, type) {
                        return this.map[levelType] < this.map[type];
                    },
                    isAllowedLevel: function (levelType) {
                        return this.map.hasOwnProperty(levelType)
                            && this.map[levelType] + 1 < this.list.length;
                    },
                    getAllowedList: function (levelType) {
                        return this.list.slice(this.map[levelType] + 1);
                    }
                },
                availableTypes: [],
                loading: false,
                reset: function () {
                    this.page = 1;
                    this.totalPages = 1;
                    this.ranking = null;
                    this.highlighted = null;
                    this.availableTypes = null;
                },
                update: function () {
                    this.availableTypes = this.types.getAllowedList($scope.activeTerritorialUnit.type);

                    if (this.availableTypes.length == 0) {
                        this.type = null;
                    } else if (
                        !this.type
                        || !this.types.isAllowed($scope.activeTerritorialUnit.type, this.type)
                    ) {
                        this.type = this.availableTypes[0];
                    }

                    if (this.type) {
                        this.loading = true;
                        var rankingObj = this;
                        updateRankingObj(this)['finally'](function () {
                            rankingObj.loading = false;
                        });
                    }
                },
                setType: function (type) {
                    if (!this.types.isAllowed($scope.activeTerritorialUnit.type, type)) {
                        return;
                    }

                    if (this.type !== type) {
                        this.type = type;
                        this.update();
                    }
                }
            };
            $scope.territorialUnitRanking.reset();
            $scope.$watch("[territorialUnitRanking.page, territorialUnitRanking.limit]", function () {
                $scope.territorialUnitRanking.update();
            });




            // Listen if user has been changed
            var checkUser = function () {
                var commune;
                if (security.currentUser && (commune = security.currentUser.commune)) {
                    $scope.userCommune = commune;
                    $scope.userDistrict = commune.district;
                    $scope.userRegion = commune.district.region;
                } else {
                    $scope.user = null;
                    $scope.userCommune = null;
                    $scope.userDistrict = null;
                    $scope.userRegion = null;
                }


                $scope.nationwideUserRanking.update();
                $scope.organisationRanking.update();
                $scope.nationwideTerritorialUnitRanking.update();

                $scope.userRanking.update();
                $scope.territorialUnitRanking.update();
            };
            $scope.$on('ruchJowUserChanged', function () {
                checkUser();
            });
            checkUser();

            // Watch changes triggered by map.
            $scope.$watch('[activeTerritorialUnit.type, activeTerritorialUnit.id]', function () {
                $scope.markersData = {};
                ruchJowTUStatistics.getStatistics(
                    $scope.activeTerritorialUnit.type,
                    $scope.activeTerritorialUnit.id
                ).then(function (data) {
                        $scope.markersData = data;
                    });

                $scope.parent = null;
                ruchJowTerritorialUnits.getParent(
                    $scope.activeTerritorialUnit.type,
                    $scope.activeTerritorialUnit.id
                ).then(function (data) {
                        $scope.parent = data;
                    });

                $scope.children = null;
                ruchJowTerritorialUnits.getChildren(
                    $scope.activeTerritorialUnit.type,
                    $scope.activeTerritorialUnit.id
                ).then(function (data) {
                        $scope.children = data;
                    });


                // GET RANKING: User Nationwide
                $scope.nationwideUserRanking.reset();
                $scope.nationwideUserRanking.update();

                // GET RANKING: Organisation
                $scope.organisationRanking.reset();
                $scope.organisationRanking.update();

                // GET RANKING: Organisation
                $scope.nationwideTerritorialUnitRanking.reset();
                $scope.nationwideTerritorialUnitRanking.update();

                // GET RANKING: User
                $scope.userRanking.reset();
                $scope.userRanking.update();

                // GET RANKING: TU
                $scope.territorialUnitRanking.reset();
                $scope.territorialUnitRanking.update();
            }, true);

            // HELPER FUNCTIONS

            // Change current region
            $scope.setCurrentTU = function (type, id) {

                if (angular.isObject(type)) {
                    id = type.id;
                    type = type.type;
                }

                if (['my_region', 'my_district', 'my_commune'].indexOf(type) !== -1) {
                    if (!$scope.userCommune) {
                        return;
                    }

                    switch (type) {
                        case 'my_region':
                            type = 'region';
                            id = $scope.userRegion.id;
                            break;

                        case 'my_district':
                            type = 'district';
                            id = $scope.userDistrict.id;
                            break;

                        case 'my_commune':
                            type = 'commune';
                            id = $scope.userCommune.id;
                            break;
                    }
                }

                $scope.activeTerritorialUnit.type = type;
                $scope.activeTerritorialUnit.id = id;
                $scope.activeTerritorialUnit.name = '';

                ruchJowTerritorialUnits.getName(type, id).then(function (name) {
                    $scope.activeTerritorialUnit.name = name;
                });
            };

            $scope.showParent = function() {
                if ($scope.parent) {
                    $scope.setCurrentTU($scope.parent)
                }
            };

            $scope.isCurrentTU = function(type, id) {
                var atu = $scope.activeTerritorialUnit;
                switch (type) {
                    case 'country':
                        return atu.type === 'country';

                    case 'my_region':
                        return (
                            atu.type === 'region'
                            && $scope.userRegion
                            && atu.id === $scope.userRegion.id
                        );

                    case 'my_district':
                        return (
                            atu.type === 'district'
                            && $scope.userDistrict
                            && atu.id === $scope.userDistrict.id
                        );

                    case 'my_commune':
                        return (
                            atu.type === 'commune'
                            && $scope.userCommune
                            && atu.id === $scope.userCommune.id
                        );

                    case 'region':
                        return (atu.type === 'region' && id && atu.id === id);

                    case 'district':
                        return (atu.type === 'district' && id && atu.id === id);

                    case 'commune':
                        return (atu.type === 'commune' && id && atu.id === id);
                }

                return false;
            };

            function updateRankingObj(rankingObj) {
                return ruchJowGeneralRanks.updateRankingObj(
                    rankingObj,
                    {
                        type: $scope.activeTerritorialUnit.type,
                        id: $scope.activeTerritorialUnit.id
                    }
                );
            }
        }
    ])
    .controller('RanksCtrl', [
        '$scope',
        '$http',
        'ruchJowTUStatistics',
        'ruchJowTerritorialUnits',
        'ruchJowGeneralRanks',
        'ruchJowLocalGov',
        'ruchJowSecurity',
        function ($scope, $http, ruchJowTUStatistics, ruchJowTerritorialUnits, ruchJowGeneralRanks, ruchJowLocalGov, security) {
            $scope.activeTerritorialUnit = {type: 'country', name: 'Polska', id:  null};

            $scope.markersData = {};
            $scope.parent = null;
            $scope.children = null;
            $scope.localGovMarkers = [];

            // RANKING: User
            $scope.userRanking = {
                limit: 5,
                type: 'user',
                loading: false,
                reset: function () {
                    this.page = 1;
                    this.totalPages = 1;
                    this.ranking = null;
                    this.highlighted = null;
                },
                update: function () {
                    this.loading = true;
                    var rankingObj = this;
                    updateRankingObj(this)['finally'](function () {
                        rankingObj.loading = false;
                    });
                }
            };
            $scope.userRanking.reset();
            $scope.$watch('userRanking.page', function () {
                $scope.userRanking.update();
            });
            $scope.$watch('userRanking.limit', function () {
                $scope.userRanking.update();
            });

            // RANKING: TU
            $scope.territorialUnitRanking = {
                limit: 5,
                typeMap: {
                    country: 'region',
                    region: 'district',
                    district: 'commune'
                },
                loading: false,
                reset: function () {
                    this.page = 1;
                    this.pages = 1;
                    this.ranking = null;
                    this.highlighted = null;
                    this.type = this.typeMap[$scope.activeTerritorialUnit.type];
                },
                update: function () {
                    if (this.type) {
                        this.loading = true;
                        var rankingObj = this;
                        updateRankingObj(this)['finally'](function () {
                            rankingObj.loading = false;
                        });
                    }
                }
            };
            $scope.territorialUnitRanking.reset();
            $scope.$watch('territorialUnitRanking.page', function () {
                $scope.territorialUnitRanking.update();
            });
            $scope.$watch('territorialUnitRanking.limit', function () {
                $scope.territorialUnitRanking.update();
            });

            // Listen if user has been changed
            var checkUser = function () {
                var commune;
                if (security.currentUser && (commune = security.currentUser.commune)) {
                    $scope.userCommune = commune;
                    $scope.userDistrict = commune.district;
                    $scope.userRegion = commune.district.region;
                } else {
                    $scope.user = null;
                    $scope.userCommune = null;
                    $scope.userDistrict = null;
                    $scope.userRegion = null;
                }

                $scope.userRanking.update();
                $scope.territorialUnitRanking.update();
            };
            $scope.$on('ruchJowUserChanged', function () {
                checkUser();
            });
            checkUser();


            // Watch changes triggered by map.
            $scope.$watch('activeTerritorialUnit.type + activeTerritorialUnit.id', function () {
                $scope.markersData = {};
                ruchJowTUStatistics.getStatistics(
                    $scope.activeTerritorialUnit.type,
                    $scope.activeTerritorialUnit.id
                ).then(function (data) {
                        $scope.markersData = data;
                    });

                $scope.parent = null;
                ruchJowTerritorialUnits.getParent(
                    $scope.activeTerritorialUnit.type,
                    $scope.activeTerritorialUnit.id
                ).then(function (data) {
                        $scope.parent = data;
                    });

                $scope.children = null;
                ruchJowTerritorialUnits.getChildren(
                    $scope.activeTerritorialUnit.type,
                    $scope.activeTerritorialUnit.id
                ).then(function (data) {
                        $scope.children = data;
                    });


                // Update LOCAL GOV MARKERS
                $scope.localGovMarkers = [];
                ruchJowLocalGov.getMarkersData(
                    $scope.activeTerritorialUnit.type,
                    $scope.activeTerritorialUnit.id
                ).then(function (data) {
                    $scope.localGovMarkers = data;
                });

                // GET RANKING: User
                $scope.userRanking.reset();
                $scope.userRanking.update();

                // GET RANKING: TU
                $scope.territorialUnitRanking.reset();
                $scope.territorialUnitRanking.update();
            });


            // Change current region
            $scope.setCurrentTU = function (type, id) {

                if (angular.isObject(type)) {
                    id = type.id;
                    type = type.type;
                }

                if (['my_region', 'my_district', 'my_commune'].indexOf(type) !== -1) {
                    if (!$scope.userCommune) {
                        return;
                    }

                    switch (type) {
                        case 'my_region':
                            type = 'region';
                            id = $scope.userRegion.id;
                            break;

                        case 'my_district':
                            type = 'district';
                            id = $scope.userDistrict.id;
                            break;

                        case 'my_commune':
                            type = 'commune';
                            id = $scope.userCommune.id;
                            break;
                    }
                }

                $scope.activeTerritorialUnit.type = type;
                $scope.activeTerritorialUnit.id = id;
                $scope.activeTerritorialUnit.name = '';

                ruchJowTerritorialUnits.getName(type, id).then(function (name) {
                    $scope.activeTerritorialUnit.name = name;
                });
            };

            $scope.showParent = function() {
                if ($scope.parent) {
                    $scope.setCurrentTU($scope.parent)
                }
            };

            $scope.isCurrentTU = function(type, id) {
                var atu = $scope.activeTerritorialUnit;
                switch (type) {
                    case 'country':
                        return atu.type === 'country';

                    case 'my_region':
                        return (
                        atu.type === 'region'
                        && $scope.userRegion
                        && atu.id === $scope.userRegion.id
                        );

                    case 'my_district':
                        return (
                        atu.type === 'district'
                        && $scope.userDistrict
                        && atu.id === $scope.userDistrict.id
                        );

                    case 'my_commune':
                        return (
                        atu.type === 'commune'
                        && $scope.userCommune
                        && atu.id === $scope.userCommune.id
                        );

                    case 'region':
                        return (atu.type === 'region' && id && atu.id === id);

                    case 'district':
                        return (atu.type === 'district' && id && atu.id === id);

                    case 'commune':
                        return (atu.type === 'commune' && id && atu.id === id);
                }

                return false;
            };

            function updateRankingObj(rankingObj) {
                return ruchJowGeneralRanks.updateRankingObj(
                    rankingObj,
                    {
                        type: $scope.activeTerritorialUnit.type,
                        id: $scope.activeTerritorialUnit.id
                    }
                );
            }
        }
    ])
    .directive('ruchJowRankTable', [function () {

        return {
            restrict: 'A',
            replace: false,
            template: '' +
                '<div class="rank-table-wrapper" ng-class="{ loading: rankingObj.loading, disabled: !rankingObj.loading && !rankingObj.ranking.length }">' +
                '<div ng-repeat="column in ruchJowRankTableRanking" class="[[ columnClass ]] col-small-padding">' +
                '    <table class="table rank-table" ng-class="{ disabled: !rankingObj.ranking.length }">' +
                '        <thead>' +
                '            <tr>' +
                '                <th class="name" colspan="2">[[ nameColumnTitle ]]</th>' +
                '                <th class="points" ng-if="!hidePoints">[[ pointsColumnTitle ]]</th>' +
                '            </tr>' +
                '        </thead>' +
                '        <tbody>' +
                '            <tr ng-repeat="row in column" ng-class="{ highlighted: row.highlighted }">' +
                '                <td class="rank">[[ row.rank ]].</td>' +
                '                <td ruch-jow-rank-table-transclude="row"></td>' +
                '                <td class="points" ng-if="!hidePoints">[[ row.points ]]</td>' +
                '            </tr>' +
                '        <tbody>' +
                '    </table>' +
                '</div>' +
                '<div pagination' +
                '    total-items="rankingObj.pages * rankingObj.limit * (rankingObj.columns || 1)"' +
                '    items-per-page="rankingObj.limit * (rankingObj.columns || 1)"' +
                '    ng-model="rankingObj.page"' +
                '    max-size="5"' +
                '    class="pagination-sm"' +
                '    boundary-links="true"' +
                //'    rotate="false"' +
                '    direction-links="false"' +
                '    first-text="&laquo;"' +
                '    previous-text="&lsaquo;"' +
                '    next-text="&rsaquo;"' +
                '    last-text="&raquo;"' +
                '></div>' +
                '</div>',
            scope: {

                rankingObj: '=',
                nameColumnTitle: '@',
                pointsColumnTitle: '@',
                hidePoints: '='
            },
            transclude: true,
            link: function($scope, $element, $attrs, controller, $transclude) {

                $scope.$ruchJowRankTableTtranscludedScope = $scope.$parent.$new();

                $scope.$ruchJowRankTableTtranscludedScope.ruchJowRankTableRanking = [];
                $scope.ruchJowRankTableRanking = $scope.$ruchJowRankTableTtranscludedScope.ruchJowRankTableRanking;

                var updateCol = function () {
                    var prefix = 'col-md-';
                    var suffix = 12;

                    switch ($scope.rankingObj.columns || 1) {
                        case 2:
                            suffix = 6;
                            break;
                        case 3:
                            suffix = 4;
                            break;
                        case 4:
                            suffix = 3;
                            break;
                    }

                    return prefix + suffix;
                };

                var updateList = function () {
                    $scope.columnClass = updateCol();

                    //$scope.$ruchJowRankTableTtranscludedScope.ruchJowRankTableRanking = [];
                    var list = $scope.$ruchJowRankTableTtranscludedScope.ruchJowRankTableRanking;    // Array of columns (arrays).
                    while (list.length) { list.pop(); } // We clear array without loosing reference.

                    //if (!$scope.rankingObj.ranking) {
                    //    return;
                    //}

                    var rankingObj = $scope.rankingObj;
                    var ranking = rankingObj.ranking || [];
                    var columns = rankingObj.columns || 1;
                    var limit   = rankingObj.limit;

                    var column; // Current column.
                    var i = 0;      // Position in ranking.



                    for (column = 0; column < columns; column++) {
                        list.push([]);

                        // Add highlighted element (to the first column) if it should be above ranking.
                        if (
                            column === 0
                            && rankingObj.highlighted
                            && rankingObj.highlighted.relativePosition === 1
                        ) {
                            list[column].push(rankingObj.highlighted);
                        }

                        while (
                            list[column].length < limit
                            || column === columns - 1 // Last column may be longer than previous ones.
                            && ranking[i]
                        ) {
                            list[column].push(ranking[i++] || {});
                        }


                        // Add highlighted element (to the last column) if it should be below ranking.
                        if (
                            column === columns - 1
                            && rankingObj.highlighted
                            && rankingObj.highlighted.relativePosition === -1
                        ) {
                            list[column].push(rankingObj.highlighted);
                        }
                    }
                };

                $scope.$watch(
                    '[rankingObj.ranking, rankingObj.highlighted, rankingObj.columns, rankingObject.limit]',
                    function () {
                        updateList();
                    },
                    true
                );
            }
        };

    }])
    .directive('ruchJowRankTableTransclude', ['$parse', function ($parse) {
        return {
            restrict: 'EAC',
            //scope: {
            //    'row': '=ruchJowRankTableTransclude'
            //},
            link: function($scope, $element, $attrs, controller, $transclude) {
                if (!$transclude) {
                    throw Error('Illegal use of ruchJowRankTableTransclude directive!');
                }

                var rowGetter = $parse($attrs['ruchJowRankTableTransclude']);
                var scope = $scope.$ruchJowRankTableTtranscludedScope.$new();
                scope.row = rowGetter($scope);

                $transclude(scope, function(clone) {
                    $element.empty();
                    $element.append(clone);
                });
            }
        }
    }]);

