var app = angular.module('app', []);

app.controller('ChatController', ['$scope', '$http', '$interval', function($scope, $http, $interval) {


    $scope.userId = null;

    $scope.onlineCheck = null;

    $scope.users = null;


    $scope.popups = [];

    $scope.checkUsersUrl = '';


    $scope.init = function(userId, checkUsersUrl) {
        $scope.userId = userId;
        $scope.checkUsersUrl = checkUsersUrl;
        var popups = localStorage.getItem('popups' + $scope.userId);
        if ( popups ) {
            $scope.popups = JSON.parse(popups);
            //console.log(JSON.parse(popups));
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
    },60000);

}]);