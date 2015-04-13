var app = angular.module('app', []);

app.controller('ChatController', ['$scope', '$http', '$interval', function($scope, $http, $interval) {


    $scope.userId = null;

    $scope.onlineCheck = null;

    $scope.users = null;


    $scope.popupsMax = 0;

    $scope.popups = [];

    $scope.checkUsersUrl = '';


    $scope.init = function(userId, checkUsersUrl) {
        $scope.userId = userId;
        $scope.checkUsersUrl = checkUsersUrl;
        countPopups();
    }

    $scope.openPopup = function(userId) {

        if ($scope.users[userId].popup === undefined || $scope.users[userId].popup != true) {

            var popup = {
                "userId" : userId,
                "status" : 1
            };

            $scope.users[userId].popup = true;
            $scope.popups.unshift(popup);
        } else if ($scope.users[userId].popup == true) { // if opened, push to front
            for (var i = 0; i < $scope.popups.length; i++) {
                if ($scope.popups[i].userId == userId) {

                    var toFront = $scope.popups[i];
                    $scope.popups.splice(i, 1);
                    $scope.popups.unshift(toFront);

                    break;
                }
            }
        }
    }

    $scope.closePopup = function(userId) {
        var i = 0;
        for (i; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) {
                $scope.popups.splice(i, 1);
                $scope.users[userId].popup = false;
                break;
            }
        }
    }

    $scope.minimizeMaximizePopup = function(userId) {
        var i = 0;
        for (i; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) {
                $scope.popups[i].status = !$scope.popups[i].status;
                break;
            }
        }
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


    function countPopups() {
        //var width = window.innerWidth - 200;
        //$scope.popupsMax = parseInt(width/270);
        //console.log($scope.popupsMax);
    }

    window.addEventListener("resize", countPopups);

}]);