var app = angular.module('app', ['luegg.directives']);

app.controller('ChatController', ['$scope', '$http', '$interval', '$timeout', function($scope, $http, $interval, $timeout) {


    /**
     * Id of currently logged user
     * @type {number}
     */
    $scope.userId = null;

    /**
     * Profile picture of currently logged user
     * @type {string}
     */
    $scope.userProfilePicture = '';




    /**
     * All users in chat window
     * @type {{}}
     */
    $scope.users = {};
    /**
     * Timeout for checking new messages
     * @type {number}
     */
    $scope.checkTimeout = 0;
    /**
     * Chat popups on screen
     * @type {Array}
     */
    $scope.popups = [];
    /**
     * Conversations with each users
     * @type {{}}
     */
    $scope.conversations = {};
    /**
     * Count of unread messages
     * @type {{}}
     */
    $scope.unreadCount = {};
    /**
     * Is device tablet or phone?
     * @type {boolean}
     */
    $scope.tablet = true;





    /**
     * Urls for ajax requests
     * @type {string}
     */
    $scope.checkUsersUrl = '';
    $scope.sendMessageUrl = '';
    $scope.getConversationUrl = '';
    $scope.checkNewMessagesUrl = '';


    /**
     * Init method - settings variables, etc
     * @param userId
     * @param userProfilePicture
     * @param checkUsersUrl
     * @param sendMessageUrl
     * @param users
     * @param getConversationUrl
     * @param getAllConversationsUrl
     * @param checkNewMessagesUrl
     */
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

        var unreadCount = localStorage.getItem('unreadCount' + $scope.userId);
        if (unreadCount) {
            $scope.unreadCount = JSON.parse(unreadCount);
        }

        $scope.tablet = window.mobileAndTabletcheck();


        checkForNewMessages();
    }

    /**
     * Open chat popup with conversation
     * @param userId
     */
    $scope.openPopup = function(userId) {
        for (var i = 0; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) { // if already opened, push to front or leave it alone

                var toFront = $scope.popups[i];
                toFront.status = 1;

                if ($scope.unreadCount[toFront.userId] !== undefined) {
                    $scope.unreadCount[toFront.userId] = 0;
                    saveUnreadToStorage();
                }


                $scope.popups.splice(i, 1);
                $scope.popups.unshift(toFront);

                setTimeout(function() { $("#message-input-" + userId).focus(); }, 1);

                return;
            }
        }

        var popup = {
            "userId" : userId,
            "status" : 1,
            "loading" : true
        };

        $scope.popups.unshift(popup);

        if ($scope.unreadCount[popup.userId] !== undefined) {
            $scope.unreadCount[popup.userId] = 0;
            saveUnreadToStorage();
        }



        setTimeout(function() {
            if (!$scope.tablet) {
                $("#message-input-" + userId).focus();
            }
            $(".chat-text").niceScroll();
        }, 1); // hack for waiting after angular rendered

        if ($scope.conversations[userId] === undefined) {
            $http.get($scope.getConversationUrl, { params: {userId : userId}})
                .success(function(data) {
                    $scope.conversations[userId] = data.conversation;
                    popup.loading = false;
                    savePopupsToStorage();
                });
        } else {
            popup.loading = false;
        }

    }

    /**
     * Close popup when click on close icon
     * @param userId
     */
    $scope.closePopup = function(userId) {
        for (var i = 0; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) {
                $scope.popups.splice(i, 1);
                $scope.users[userId].popup = false;
                break;
            }
        }

        savePopupsToStorage();
    }

    /**
     * Only for minimizing/maximizing popup window
     * @param userId
     */
    $scope.minimizeMaximizePopup = function(userId) {
        for (var i = 0; i < $scope.popups.length; i++) {
            if ($scope.popups[i].userId == userId) {
                $scope.popups[i].status = !$scope.popups[i].status;
                break;
            }
        }

        savePopupsToStorage();
    }

    /**
     * When focused in chat input, check for keypressed. If enter was hitted, send the message
     * @param event
     * @param userId
     */
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

    /**
     * Checks if key was esc, if true, then close chat popup
     * @param event
     * @param userId
     */
    $scope.checkEsc = function (event, userId) {
        if (event.which == 27) {
            $scope.closePopup(userId);
        }
    }

    /**
     * When click on chat popup, focus on chat input
     * @param popup
     */
    $scope.clickOnPopup = function(popup) {
        if (!popup.status) return;

        if ($scope.unreadCount[popup.userId] !== undefined) {
            $scope.unreadCount[popup.userId] = 0;
            saveUnreadToStorage();
        }

        if (!$scope.tablet) {
            $("#message-input-" + popup.userId).focus();
        }
    }

    /**
     * Save open popups and their statuses to storage
     */
    function savePopupsToStorage() {
        localStorage.setItem('popups' + $scope.userId, angular.toJson($scope.popups));
    }

    /**
     * Save unread counts to storage
     */
    function saveUnreadToStorage() {
        localStorage.setItem('unreadCount' + $scope.userId, angular.toJson($scope.unreadCount));
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
                            if ($scope.unreadCount[message.from] === undefined) {
                                $scope.unreadCount[message.from] = 1;
                            } else {
                                $scope.unreadCount[message.from]++;
                            }

                            if ($scope.popups[message.from] === undefined) { // if popup is currently not opened

                                if ($scope.conversations[message.from] !== undefined) { // if popup was already once opened
                                    $scope.conversations[message.from].push(message);
                                }

                                $scope.openPopup(message.from);
                            } else { // if popup is opened
                                $scope.conversations[message.from].push(message);
                            }


                            saveUnreadToStorage();
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

/**
 * Source: https://github.com/sparkalow/angular-truncate
 */
app.filter('characters', function () {
    return function (input, chars, breakOnWord) {
        if (isNaN(chars)) return input;
        if (chars <= 0) return '';
        if (input && input.length > chars) {
            input = input.substring(0, chars);

            if (!breakOnWord) {
                var lastspace = input.lastIndexOf(' ');
                //get last space
                if (lastspace !== -1) {
                    input = input.substr(0, lastspace);
                }
            }else{
                while(input.charAt(input.length-1) === ' '){
                    input = input.substr(0, input.length -1);
                }
            }
            return input + '…';
        }
        return input;
    };
})

/**
 * Source: http://stackoverflow.com/questions/11381673/detecting-a-mobile-browser
 * @returns {boolean}
 */
window.mobileAndTabletcheck = function() {
    var check = false;
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
}