<!-- Main Script -->
<script>
var room_key = <?php echo $room['id']; ?>;
var current_message_id = 0;
var at_bottom = true;

// Initial Load
messages_load(true);

// Interval Load
var load_interval = <?php echo $load_interval; ?>;
setInterval(function(){
  messages_load(false);
}, load_interval);

// Detect if user is at bottom
var text_to_bottom_css = true;
$('#message_content_parent').scroll(function() {
  at_bottom = false;
  if ($('#message_content_parent').prop('scrollHeight') - $('#message_content_parent').scrollTop() <= Math.ceil($('#message_content_parent').height())) {
    at_bottom = true;
  }
});

window.onload = function() {
  // Focus on chat
  $('#message_input').focus();
}

$(document).on('click', '.message_pin', function(event){
  pin_action(event);
});

// New Message
function submit_new_message(event) {
  // Message input
  var message_input = $("#message_input").val();
  $.ajax({
      url: "<?=base_url()?>new_message",
      type: "POST",
      data: { 
        message_input: message_input
      },
      cache: false,
      success: function(response) {
        console.log('submit');
        // All responses are error messsages
        if (response) {
          response = response.replace('<p>', '');
          response = response.replace('</p>', '');
          alert(response);
          return false;
        }
        // Empty chat input
        $('#message_input').val('');
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
      url: "<?=base_url()?>load",
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
        if (messages.error) {
          alert(messages.error);
          window.location = '<?=base_url()?>';
          return false;
        }
        $.each(messages, function(i, message) {
          // Skip if we already have this message
          if (parseInt(message.id) <= parseInt(current_message_id)) {
            return true;
          }
          current_message_id = message.id;
          // System Messages
          if (parseInt(message.user_key) === <?php echo $this->system_user_id; ?>) {
            html += '<div class="system_message ' + message.username + '">' + message.message + '</div>';
            return true;
          }
          // Process message
          var message_message = process_message(message.message);
          // Detect if youtube
          // Lighten color for text
          var light_color = lighten_darken_color(message.color, -75);
          // build message html
          html += '<div class="message_parent">';
          html += '<span class="message_face glyphicon glyphicon-user" style="color: ' + light_color + ';"></span>';
          if (use_pin(message_message)) {
            html += '<span class="message_pin glyphicon glyphicon-pushpin" style="color: ' + light_color + ';"></span>';
          }
          html += '<span class="message_username" style="color: ' + light_color  + ';">' + message.username + '</span>';
          html += '<span class="message_message">' + message_message + '</span>';
          html += '</div>';
        });
        // Append to div
        $("#message_content_parent").append(html);
        // Stay at bottom if at bottom
        if (at_bottom || inital_load) {
          scroll_to_bottom();
        }
      }
  });
}

function pin_action(event) {
  if (!$(event.target).hasClass('active_pin')) {
    $('.active_pin').removeClass('active_pin');
    $('.pinned').removeClass('pinned')
    $(event.target).addClass('active_pin');
    $(event.target).parent().addClass('pinned')
  }
  else {
    $(event.target).removeClass('active_pin');
    $(event.target).parent().removeClass('pinned')
  }
}

function process_message(message) {
  // Order important
  message = convert_youtube(message);
  message = convert_vimeo(message);
  message = convert_twitch(message);
  message = convert_vocaroo(message);
  message = convert_video_url(message);
  message = convert_image_url(message);
  message = convert_general_url(message);
  return message
}

function use_pin(message) {
  if (
    string_contains(message, 'message_youtube')
    || string_contains(message, 'message_vimeo')
    || string_contains(message, 'message_twitch')
    || string_contains(message, 'message_vocaroo')
    || string_contains(message, 'message_video')
    || string_contains(message, 'message_image')
    ) {
    return true;
  }
  return false;
}

function convert_youtube(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(\S+)/g;
  if (pattern.test(input)) {
    var replacement = '<span class="message_youtube_parent"><iframe src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen class="message_youtube message_content"></iframe></span>';
    var input = input.replace(pattern, replacement);
    // For start time, turn get param & into ?
    // ?wmode=opaque may appear twice
    if (input.indexOf('&amp;t=') !== -1) {
      var input = input.replace('&amp;t=', '?t=');
    }
  }
  return input;
}

function convert_vimeo(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:vimeo\.com)\/?(\S+)/g;
  if (pattern.test(input)) {
   var replacement = '<iframe src="//player.vimeo.com/video/$1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen class="message_vimeo message_content"></iframe>';
   var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_twitch(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:twitch\.tv)\/?(\S+)/g;
  if (pattern.test(input)) {
   var replacement = '<iframe src="https://player.twitch.tv/?channel=$1&!autoplay" frameborder="0" allowfullscreen="true" scrolling="no" height="378" width="620" class="message_twitch message_content"></iframe>';
   var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_vocaroo(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:vocaroo\.com\/i)\/?(\S+)/g;
  if (pattern.test(input)) {
   var replacement = '<object width="148" height="44" class="message_vocaroo message_content"><param name="movie" value="http://vocaroo.com/player.swf?playMediaID=$1&autoplay=0"></param><param name="wmode" value="transparent"></param><embed src="http://vocaroo.com/player.swf?playMediaID=$1&autoplay=0" width="148" height="44" wmode="transparent" type="application/x-shockwave-flash"></embed></object>';
   var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_video_url(input) {
  var pattern = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?(?:webm|mp4|ogv))/gi;
  if (pattern.test(input)) {
    var replacement = '<video controls="" loop="" controls src="$1" style="max-width: 960px; max-height: 676px;" class="message_video message_content"></video>';
    var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_image_url(input) {
  var pattern = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?(?:jpg|jpeg|gif|png))/gi;
  if (pattern.test(input)) {
    var replacement = '<a href="$1" target="_blank" class="message_image_link message_content"><img class="message_image message_content" src="$1"/></a><br />';
    var input = input.replace(pattern, replacement);
  }
  return input;
}

function convert_general_url(input) {
  // Ignore " to not conflict with other converts
  var pattern = /(?!.*")([-a-zA-Z0-9@:%_\+.~#?&//=;]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=;]*))/gi;
  if (pattern.test(input)) {
    var replacement = '<a href="$1" target="_blank" class="message_link message_content">$1</a>';
    var input = input.replace(pattern, replacement);
  }
  return input;
}

function scroll_to_bottom() {
  $("#message_content_parent").scrollTop($("#message_content_parent")[0].scrollHeight);
}

function string_contains(string, sub_string) {
  if (string.indexOf(sub_string) !== -1) {
    return true;
  }
  return false;
}

// http://stackoverflow.com/questions/5560248/programmatically-lighten-or-darken-a-hex-color-or-rgb-and-blend-colors
function lighten_darken_color(col,amt) {
    var usePound = false;
    if ( col[0] == "#" ) {
        col = col.slice(1);
        usePound = true;
    }
    var num = parseInt(col,16);
    var r = (num >> 16) + amt;
    if ( r > 255 ) r = 255;
    else if  (r < 0) r = 0;
    var b = ((num >> 8) & 0x00FF) + amt;
    if ( b > 255 ) b = 255;
    else if  (b < 0) b = 0;
    var g = (num & 0x0000FF) + amt;
    if ( g > 255 ) g = 255;
    else if  ( g < 0 ) g = 0;
    return (usePound?"#":"") + (g | (b << 8) | (r << 16)).toString(16);
}

</script>