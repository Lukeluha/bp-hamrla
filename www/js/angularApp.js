var app = angular.module('app', []);

app.controller('ChatController', ['$scope', '$http', '$interval', function($scope, $http, $interval) {


    $scope.userId = null;

    $scope.onlineCheck = null;

    $scope.users = null;

    $scope.checkTimeout = 1;

    $scope.popups = [];




    $scope.checkUsersUrl = '';

    $scope.sendMessageUrl = '';


    $scope.init = function(userId, checkUsersUrl, sendMessageUrl, users) {
        $scope.userId = userId;
        $scope.checkUsersUrl = checkUsersUrl;
        $scope.sendMessageUrl = sendMessageUrl;
        $scope.users = users;

        var popups = localStorage.getItem('popups' + $scope.userId);
        if ( popups ) {
           $scope.popups = JSON.parse(popups);
        }
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
            "status" : 1
        };

        $scope.popups.unshift(popup);

        console.log($scope.popups);


        saveToStorage();
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
        var message = event.target.value;

        $http({
            method: 'POST',
            url: $scope.sendMessageUrl,
            data: $.param({message: message, to: userId}),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })

        event.preventDefault();
    }

    function saveToStorage() {
        localStorage.setItem('popups' + $scope.userId, angular.toJson($scope.popups));
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

