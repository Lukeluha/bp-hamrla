var app = angular.module('app', []);

app.controller('ChatController', ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {


    $scope.userId = null;

    $scope.onlineCheck = null;



    $scope.init = function(userId) {
        $scope.userId = userId;
    }

}]);