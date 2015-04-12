var app = angular.module('app', []);

app.controller('ChatController', ['$scope', '$http', '$interval', function($scope, $http, $interval) {


    $scope.userId = null;

    $scope.onlineCheck = null;

    $scope.users = null;



    $scope.checkUsersUrl = '';

    $scope.init = function(userId, checkUsersUrl) {
        $scope.userId = userId;
        $scope.checkUsersUrl = checkUsersUrl;
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