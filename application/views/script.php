<!-- Main Script -->
<script>
var room_key = <?php echo $room['id']; ?>;
var current_message = 0;
var at_bottom = true;

// Initial Load
messages_load(true);

// Interval Load
var load_interval = <?php echo $load_interval; ?>;
setInterval(function(){
  messages_load(false);
}, load_interval);

// Detect if user is at bottom
$(window).scroll(function() {
  at_bottom = false;
  if ($(window).scrollTop() + $(window).height() == $(document).height()) {
    at_bottom = true;
  }
});

// New Message
function submit_new_message(e) {
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
        // Empty chat input
        $('#message_input').val('');
        // All responses are error messsages
        if (response) {
          response = response.replace('<p>', '');
          response = response.replace('</p>', '');
          alert(response);
        }
        // Load log so user can instantly see his message
        messages_load();
        // Focus back on input
        $('#message_input').focus();
        // Scroll to bottom
        scroll_to_bottom();
      }
  });
  return false;
}

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
          // Convert URLs to content
          // Order important
          var message_message = message.message;
          message_message = convert_youtube(message_message);
          message_message = convert_vimeo(message_message);
          message_message = convert_twitch(message_message);
          message_message = convert_vocaroo(message_message);
          message_message = convert_video_url(message_message);
          message_message = convert_image_url(message_message);
          message_message = convert_general_url(message_message);
          html += '<div class="message_parent"><span class="message_icon glyphicon glyphicon-user" style="color: ' + message.color + '""></span><span class="message_username">' + message.username + '</span>: <span class="message_message">' + message_message + '</span></div>';
          current_message = message.id;
        });
        // Append to div
        $("#message_content_parent").append(html);
        // Stay at bottom if at bottom
        if (at_bottom) {
          scroll_to_bottom();
        }
      }
  });
}

function convert_youtube(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(\S+)/g;
  if (pattern.test(input)) {
    var replacement = '<iframe width="420" height="345" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>';
    var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_vimeo(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:vimeo\.com)\/?(\S+)/g;
  if (pattern.test(input)) {
   var replacement = '<iframe width="420" height="345" src="//player.vimeo.com/video/$1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
   var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_twitch(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:twitch\.tv)\/?(\S+)/g;
  if (pattern.test(input)) {
   var replacement = '<iframe src="https://player.twitch.tv/?channel=$1&!autoplay" frameborder="0" allowfullscreen="true" scrolling="no" height="378" width="620"></iframe>';
   var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_vocaroo(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:vocaroo\.com\/i)\/?(\S+)/g;
  if (pattern.test(input)) {
   var replacement = '<object width="148" height="44"><param name="movie" value="http://vocaroo.com/player.swf?playMediaID=$1&autoplay=0"></param><param name="wmode" value="transparent"></param><embed src="http://vocaroo.com/player.swf?playMediaID=$1&autoplay=0" width="148" height="44" wmode="transparent" type="application/x-shockwave-flash"></embed></object>';
   var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_video_url(input) {
  var pattern = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?(?:webm|mp4|ogv))/gi;
  if (pattern.test(input)) {
    var replacement = '<video controls="" loop="" controls src="$1" style="max-width: 960px; max-height: 676px;"></video>';
    var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_image_url(input) {
  var pattern = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?(?:jpg|jpeg|gif|png))/gi;
  if (pattern.test(input)) {
    var replacement = '<a href="$1" target="_blank"><img class="sml" src="$1" /></a><br />';
    var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_general_url(input) {
  // Ignore " to not conflict with other converts
  var pattern = /(?!.*")([-a-zA-Z0-9@:%_\+.~#?&//=;]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=;]*))/gi;
  if (pattern.test(input)) {
    var replacement = '<a href="$1" target="_blank">$1</a>';
    var input = input.replace(pattern, replacement);
  }
  return input;
}

function scroll_to_bottom() {
  window.scrollTo(0,document.body.scrollHeight);
}
</script>