<!-- Main Script -->
<script>
var room_key = <?php echo $room['id']; ?>;
var current_message = 0;

// Message Load
function messages_load(inital_load) {
  $.ajax({
      url: "<?=base_url()?>message/load",
      type: "POST",
      data: {
        room_key: room_key,
        inital_load: inital_load
      },
      cache: false,
      success: function(response) {
        console.log('load');
        var html = '';
        // On inital load, clear loading html
        if (inital_load) {
          $("#message_content_parent").html(html);
        }
        // Emergency force reload
        if (response === 'reload') {
          window.location.reload(true);
        }
        // Parse messages and loop through them
        messages = JSON.parse(response);
        if (!messages) {
          return false;
        }
        $.each(messages, function(i, message) {
          // Skip if we already have this message
          if (message.id <= current_message) {
            return true;
          }
          // Replace URLs with hyperlinks
          var message_message = replaceURLWithHTMLLinks(message.message);
          html += '<div class="message_parent"><span class="message_icon glyphicon glyphicon-user" style="color: ' + message.color + '""></span><span class="message_username">' + message.username + '</span>: <span class="message_message">' + message_message + '</span></div>';
          current_message = message.id;
        });
        // Append to div
        $("#message_content_parent").append(html);
        // Bring to bottom
        // !todo! make smart
        window.scrollTo(0,document.body.scrollHeight);
      }
  });
}

// Initial Load
messages_load(true);

// Interval Load
var load_interval = <?php echo $load_interval; ?>;
setInterval(messages_load(false), load_interval);

// New Message
function submit_new_message(e) {
  // Empty chat input
  $('#message_input').val('');
  // Message input
  var message_input = $("#message_input").val();
  $.ajax({
      url: "<?=base_url()?>message/new_message",
      type: "POST",
      data: { 
        message_input: message_input,
        room_key: room_key
      },
      cache: false,
      success: function(response) {
        console.log('submit');
        // All responses are error messsages
        if (response) {
          alert(response);
        }
        // Load log so user can instantly see his message
        messages_load();
        // Focus back on input
        $('#message_input').focus();
      }
  });
  return false;
}

function replaceURLWithHTMLLinks(text) {
    var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i;
    return text.replace(exp,"<a target='_blank' style='color: #CCCCFF' href='$1'>$1</a>"); 
}
</script>
