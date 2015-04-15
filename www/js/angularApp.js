var app = angular.module('app', ['luegg.directives']);

app.controller('ChatController', ['$scope', '$http', '$interval', '$timeout', function($scope, $http, $interval, $timeout) {


    $scope.userId = null;
    $scope.userProfilePicture = '';

    $scope.onlineCheck = null;
    $scope.users = null;
    $scope.checkTimeout = 0;
    $scope.popups = [];
    $scope.conversations = {};
    $scope.unreadCount = {};

    $scope.checkUsersUrl = '';
    $scope.sendMessageUrl = '';
    $scope.getConversationUrl = '';
    $scope.checkNewMessagesUrl = '';


    $scope.init = function(userId, userProfilePicture, checkUsersUrl, sendMessageUrl, users, getConversationUrl, getAllConversationsUrl, checkNewMessagesUrl) {
        $scope.userId = userId;
        $scope.userProfilePicture = userProfilePicture;
        $scope.checkUsersUrl = checkUsersUrl;
        $scope.sendMessageUrl = sendMessageUrl;
        $scope.users = users;
        $scope.getConversationUrl = getConversationUrl;
        $scope.checkNewMessagesUrl = checkNewMessagesUrl;

        var popups = localStorage.getItem('popups' + $scope.userId);
        if ( popups ) {
            $scope.popups = JSON.parse(popups);

            if ($scope.popups.length) {
                var userIds = new Array;

                for (var i = 0; i < $scope.popups.length; i++) {
                    userIds.push($scope.popups[i].userId);
                }

                $http({
                    method: 'POST',
                    url: getAllConversationsUrl,
                    data: $.param({users : userIds}),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(function(data) {
                    $scope.conversations = data.conversations;
                })
            }

        }

        checkForNewMessages();
    }

    $scope.openPopup = function(userId) {

        for (var i = 0; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) { // if already opened, push to front
                var toFront = $scope.popups[i];
                $scope.popups.splice(i, 1);
                $scope.popups.unshift(toFront);

                return;
            }
        }

        var popup = {
            "userId" : userId,
            "status" : 1,
            "loading" : true
        };

        $scope.popups.unshift(popup);


        $http.get($scope.getConversationUrl, { params: {userId : userId}})
            .success(function(data) {
                $scope.conversations[userId] = data.conversation;
                popup.loading = false;
                saveToStorage();
            });

    }

    $scope.closePopup = function(userId) {
        for (var i = 0; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) {
                $scope.popups.splice(i, 1);
                $scope.users[userId].popup = false;
                break;
            }
        }

        saveToStorage();
    }

    $scope.minimizeMaximizePopup = function(userId) {
        for (var i = 0; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) {
                $scope.popups[i].status = !$scope.popups[i].status;
                break;
            }
        }

        saveToStorage();
    }

    $scope.chatKeyPress = function (event, userId) {
        if (event.which != 13) return;
        var message = event.target.value.trim();
        if (message.length) {
            $http({
                method: 'POST',
                url: $scope.sendMessageUrl,
                data: $.param({message: message, to: userId}),
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).success(function(){

                var messageObject = {
                    from: $scope.userId,
                    message: message
                }

                $scope.conversations[userId].push(messageObject);
                event.target.value = '';
            }).error(function(){
                alert('Zprávu se nepodařilo odeslat, zkuste to prosím znovu');
            })
        }




        event.preventDefault();
    }

    function saveToStorage() {
        localStorage.setItem('popups' + $scope.userId, angular.toJson($scope.popups));
    }

    /**
     * Exponencially check for new mesages
     */
    function checkForNewMessages() {
        $timeout(function() {
            $http.get($scope.checkNewMessagesUrl)
                .success(function(data) {
                    if (data.newMessages && data.newMessages.length) {
                        angular.forEach(data.newMessages, function(message) {
                            $scope.conversations[message.from].push(message);
                        })

                        $scope.checkTimeout = 0;
                    } else {
                        if ($scope.checkTimeout < 5) {
                            $scope.checkTimeout++;
                        }
                    }
                    checkForNewMessages();
                })
        }, Math.pow(2, $scope.checkTimeout) * 1000)
    }


    /**
     * Check for all online users
     */
    $interval(function(){
        $http.get($scope.checkUsersUrl)
            .success(function(data) {
                $scope.users = data.users;
            })
    },30000);

}]);

/**
 * Source: http://stackoverflow.com/questions/14478106/angularjs-sorting-by-property
 */
app.filter('orderObjectBy', function() {
    return function(items, field, reverse) {
        var filtered = [];
        angular.forEach(items, function(item) {
            filtered.push(item);
        });
        filtered.sort(function (a, b) {
            return (a[field] > b[field] ? 1 : -1);
        });
        if(reverse) filtered.reverse();
        return filtered;
    };
});

