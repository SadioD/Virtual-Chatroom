$(function() {
    /*var dataToSend = { senderMessage: 'bonjour douze', receiverPseudo: 'Ahmed' };

    function sendAjax() {
        $.ajax({
            method: 'POST',
            url: 'http://homework:800/Projects/Chatroom/CodeIgniter/chat/ajaxtest',
            data: dataToSend,
            dataType: 'json',
            error: function(xhr) {
                alert('oups...' + xhr.statusText)
            },
            success: function(response) {
                console.log(response)
            }
        });
    }
    setTimeout(function() { sendAjax(); }, 3000);*/

    deleteList = 'ahmed';
    /*deleteList[0] = 'ahmed';
    deleteList[1] = 'chou';*/
    var list = $(deleteList).serializeArray();
    console.log(list)


})
