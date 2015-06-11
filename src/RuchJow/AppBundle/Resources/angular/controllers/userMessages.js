(function (angular) {
    'use strict';

    angular.module('ruchJow.ctrls.userMessages', ['ruchJow.messages', 'ui.bootstrap.modal', 'pascalprecht.translate'])
        .controller('UserMessagesCtrl', [
            '$scope',
            'frTimeEvents',
            'frData',
            'ruchJowMessages',
            function ($scope, frTimeEvents, frData, ruchJowMessages) {

                $scope.statuses = {
                    folders: 'loading',
                    messages: 'idle'
                };

                $scope.folders = {
                    messages: [],
                    emptyMessages: [],
                    messagesStart: 0,
                    messagesStop: 0,
                    list: [],
                    map: {},
                    selected: null,
                    page: 1,
                    pageInput: 1,
                    pageSize: 12,
                    pages: 1,
                    isSelected: function (folder) {
                        return this.selected && this.selected.name === folder.name;
                    },
                    select: function (name) {
                        if (this.map.hasOwnProperty(name)) {

                            if (this.selected && this.selected.name !== name) {
                                this.messages = [];
                                this.$fillMessages();
                                this.messagesStart = 0;
                                this.messagesStop = 0;
                            }

                            this.selected = this.list[this.map[name]];
                            this.pages = Math.max(1, Math.ceil(this.selected.messages / this.pageSize));

                            this.$getMessages();
                        }
                    },
                    changePage: function (page) {
                        page = parseInt(page);
                        page = page < 1 ? 1
                            : (page > this.pages ? this.pages
                                : page);

                        if (page != this.page) {
                            this.page = page;
                            this.$getMessages();
                        }

                        this.pageInput = page;
                    },
                    $clear: function () {
                        this.messages = [];
                        this.$fillMessages();
                        this.list = [];
                        this.map = {};
                        this.selected = null;
                        this.page = 1;
                        this.pages = 1;
                    },
                    $add: function (name, folder) {
                        folder.name = name;

                        if (!this.map.hasOwnProperty(name)) {
                            this.map[name] = this.list.push(folder) - 1;
                        } else {
                            this.list[this.map[name]] = folder;
                        }
                    },
                    $sort: function () {
                        this.list.sort(function (a, b) {

                            return getWeight(b) - getWeight(a);

                            function getWeight(obj) {
                                if (obj.hasOwnProperty('weight')) {
                                    return obj.weight;
                                }

                                switch (a.name) {
                                    case '#inbox': return 1001;
                                    case '#sent': return 1000;
                                }

                                return 0;
                            }
                        });

                        // ReMap
                        for (var i = 0; i < this.list.length; i++) {
                            this.map[this.list[i].name] = i;
                        }
                    },
                    $getMessages: function () {
                        if (!this.selected) {
                            this.messages = [];
                            this.$fillMessages();
                            this.messagesStart = 0;
                            this.messagesStop = 0;
                            return;
                        }

                        (function (folders) {
                            $scope.statuses.messages = 'loading';
                            frData.getParametrized(
                                'messages:getMessages',
                                {
                                    folder: folders.selected.name,
                                    start: (folders.page - 1) * folders.pageSize,
                                    cnt: folders.pageSize
                                },
                                undefined,
                                true
                            ).then(function (messages) {
                                    folders.$setMessages(messages);

                                    $scope.statuses.messages = 'success';
                                }, function () {
                                    $scope.statuses.messages = 'fail';
                                })
                        })(this);
                    },
                    $setMessages: function (messages) {
                        this.messages = messages;
                        if (this.messages.length) {
                            this.messagesStart = (this.page - 1) * this.pageSize + 1;
                            this.messagesStop = (this.page - 1) * this.pageSize + this.messages.length;
                        } else {
                            this.messagesStart = 0;
                            this.messagesStop = 0;
                        }
                        this.$fillMessages();
                    },
                    $fillMessages: function () {
                        this.emptyMessages = Array
                            .apply(null, new Array(this.pageSize - this.messages.length))
                            .map(function () { return null; });
                    }
                };

                $scope.showMessage = function (id) {
                    ruchJowMessages.showMessage(id)['finally'](function () {
                        frData.getParametrized('messages:getMessage', { id: id }, id, false)
                            .then(function (message) {
                                for (var i = 0; i < $scope.folders.messages.length; i++) {
                                    if ($scope.folders.messages[i].id === id) {
                                        $scope.folders.messages[i] = message;
                                    }
                                }
                            })['finally'](function () { refresh(true); });
                    });

                };

                var refresh = function (force) {
                    $scope.statuses.folders = 'loading';

                    // Initialize FOLDERS
                    frData.get('messages:getFolders', force)
                        .then(function (folders) {
                            var selectedName = $scope.folders.selected
                                && folders.hasOwnProperty($scope.folders.selected.name)
                                && $scope.folders.selected.name;

                            //$scope.folders.list = [];
                            //$scope.folders.map = {};
                            var toSelect;

                            for (var name in folders) {
                                if (folders.hasOwnProperty(name)) {
                                    $scope.folders.$add(name, folders[name]);
                                }

                                if (!toSelect || name === '#inbox') {
                                    toSelect = name;
                                }
                            }
                            if (!selectedName && toSelect) {
                                $scope.folders.select(toSelect);
                            }

                            $scope.folders.$sort();

                            $scope.statuses.folders = 'success';
                        }, function () {
                            $scope.statuses.folders = 'error';
                        });
                };
                refresh(true);

                var timeEventId = frTimeEvents.on('messages:refresh', function () { refresh(true); });
                $scope.$on('$destroy', function () {
                    frTimeEvents.off('messages:refresh', timeEventId);
                });

                var fillArray = function (array, cnt, value) {
                    for (var i = array.length; i < cnt; i++) {
                        array[i] = value;
                    }
                }
            }
        ]);

})(angular);
