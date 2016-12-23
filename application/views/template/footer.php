<!-- jQuery -->
<script src="<?=base_url()?>resources/jquery/jquery-3.1.1.min.js"></script>
<!-- Bootstrap -->
<script src="<?=base_url()?>resources/bootstrap/js/bootstrap.min.js"></script>
<!-- Local Script -->
<script src="<?=base_url()?>resources/script.js?<?php echo time(); ?>"></script>



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
        success: function(html)
        {
            if (!html.startsWith('<div id="chat_check"></div>')) {
              return false;
            }
            html = replaceURLWithHTMLLinks(html)
            $("#chat_messages_box").html(html);
            $("#chat_messages_box").scrollTop($("#chat_messages_box")[0].scrollHeight);
        }
    });
  }
  chat_load();

  // Chat Loop
  chat_interval = 3 * 1000;
  if (document.location.hostname == "localhost") {
    chat_interval = 10 * 1000;
  }
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

<!-- Footer -->
  </body>
</html>