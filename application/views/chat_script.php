<!-- Chat Script -->
<script>
  var room_key = <?php echo $room['id']; ?>;

  //Chat Load
  function chat_load() {
    $.ajax(
    {
        url: "<?=base_url()?>chat/load",
        type: "POST",
        data: { room_key: room_key },
        cache: false,
        success: function(json)
        {
            chats = JSON.parse(json);
            if (!chats) {
              return false;
            }
            var html = '';
            $.each(chats, function(i, chat) {
              var chat_message = replaceURLWithHTMLLinks(chat.message);
              html += '<div class="message_parent"><span class="message_icon glyphicon glyphicon-user" style="color: ' + chat.color + '""></span><span class="message_username">' + chat.username + '</span>: <span class="message_message">' + chat_message + '</span></div>';
            });
            $("#chat_messages_box").html(html);
            $("#chat_messages_box").scrollTop($("#chat_messages_box")[0].scrollHeight);
        }
    });
  }
  chat_load();

  // Chat Loop
  var chat_interval = <?php echo $chat_interval; ?>;
  setInterval(chat_load, chat_interval);

  // Called by form
  function chat_submit_function(e) {
    // Chat input
    var chat_input = $("#chat_input").val();
    $.ajax(
    {
        url: "<?=base_url()?>chat/new_chat",
        type: "POST",
        data: { 
          chat_input: chat_input,
          room_key: room_key
        },
        cache: false,
        success: function(html)
        {
          if (html) {
            alert(html);
          }
        }
    });

    $('#chat_input').val('');
    // Load log so user can instantly see his message
    chat_load();
    // Focus back on input
    $('#chat_input').focus();
    return false;
  }

  function replaceURLWithHTMLLinks(text) {
      var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i;
      return text.replace(exp,"<a target='_blank' style='color: #CCCCFF' href='$1'>$1</a>"); 
  }
</script>