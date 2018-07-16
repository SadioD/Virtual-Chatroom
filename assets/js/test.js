$(function() {
    $.ajax({
        method: 'GET',
        url: 'http://homework:800/Projects/Chatroom/CodeIgniter/chat/ajaxtest',
        dataType: 'json',
        error: function(xhr) {
            console.log('oups erreur - ' + xhr.statusText);
        },
        success: function(response) {
            console.log(response);
        }
    });

})
