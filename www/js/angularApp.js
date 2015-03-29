var app = angular.module('app', []);



app.controller('StudentsClassesController', function($scope, $http, $timeout) {
    /**
     * All students
     * @type {Array}
     */
    $scope.students = [];

    /**
     * Query for student search
     * @type {string}
     */
    $scope.query = "";

    /**
     * URL for ajax request
     * @type {string}
     */
    $scope.dataUrl = "";

    $scope.addStudentUrl = "";
    $scope.removeStudentUrl = "";

    $scope.class = {
        id: 0
    }

    $scope.loading = false;

    $scope.search = false;

    var timeoutPromise;

    $scope.addStudent = function(studentId, button) {
        $http.get($scope.addStudentUrl,
            {
                params: {studentId: studentId,classId: $scope.class.id}
            }
        )
            .success(function(data) {
                loadStudents();
            }).error(function() {
            });
    }

    $scope.removeStudent = function(studentId) {
        $http.get($scope.removeStudentUrl,
            {
                params: {studentId: studentId,classId: $scope.class.id}
            }
        )
            .success(function(data) {
                loadStudents();
            }).error(function() {

            });
    }

    $scope.$watch("query", function (newValue, oldValue) {
        if (newValue !== oldValue) {

            if ($scope.query) {
                $timeout.cancel(timeoutPromise);

                timeoutPromise = $timeout(function(){
                    $scope.loading = true;
                    $scope.search = true;
                    loadStudents();
                },500);
            } else {
                $scope.students = [];
            }
        }
    });

    var loadStudents = function() {
        $http.get($scope.dataUrl,
            {
                params: {query: $scope.query, groupId: $scope.class.id}
            }
        )
            .success(function(data) {
                $scope.students = data;
                $scope.loading = false;
            }).error(function() {
                $scope.loading = false;
                alert("Objevila se chyba");
            });
    }

});

app.controller('ClassesController', function($scope, $http, $timeout) {
    $scope.loading = false;

    $scope.classes = [];

    $scope.query = "";

    $scope.searchUrl = "";

    var timeoutPromise;

    $scope.$watch("query", function (newValue, oldValue) {
        if (newValue !== oldValue) {

                $timeout.cancel(timeoutPromise);

                timeoutPromise = $timeout(function(){
                    $scope.loading = true;

                    $http.get($scope.searchUrl,
                        {
                            params: {query: $scope.query}
                        }
                    )
                        .success(function(data) {
                            $scope.classes = data;
                            $scope.loading = false;
                        }).error(function() {
                            $scope.loading = false;
                            alert("Objevila se chyba");
                        });
                },500);
        }
    });
});

app.controller('StudentsController', function($scope, $http, $timeout) {
    $scope.loading = false;

    $scope.students = [];

    $scope.query = "";

    $scope.searchUrl = "";

    var timeoutPromise;

    $scope.$watch("query", function (newValue, oldValue) {
        if (newValue !== oldValue) {

            $timeout.cancel(timeoutPromise);

            timeoutPromise = $timeout(function(){
                $scope.loading = true;

                $http.get($scope.searchUrl,
                    {
                        params: {query: $scope.query}
                    }
                )
                    .success(function(data) {
                        console.log(data);
                        $scope.students = data;
                        $scope.loading = false;
                    }).error(function() {
                        $scope.loading = false;
                        alert("Objevila se chyba");
                    });
            },500);
        }
    });
});