<!-- Main Script -->
<script>
var room_key = <?php echo $room['id']; ?>;
var slug = '<?php echo $room['slug']; ?>';
var last_message_id = 0;
var at_bottom = true;
var load_messages = true;
var window_active = true;
var page_title = '<?php echo $page_title; ?>';
var missed_messages = 0;
var users_array = new Array();

// Clear loading text
$("#message_content_parent").html('');

// Initial Load Messages
messages_load(true);

// Interval Load Messages
var load_interval = <?php echo $load_interval; ?>;
setInterval(function() {
  messages_load(false);
}, load_interval);

// Load Users
users_load();
var user_load_interval = <?php echo $user_load_interval; ?>;
setInterval(function() {
  users_load();
}, user_load_interval);

// Detect if window is open
$(window).blur(function() {
  window_active = false;
});
$(window).focus(function() {
  missed_messages = 0;
  $('title').html(page_title);
  window_active = true;
});

// On tab press in message input
document.querySelector('#message_input').addEventListener('keydown', function (e) {
  if (e.which == 9) {
    autocomplete_username();
    e.preventDefault();
  }
});

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

$(document).on('click', '.message_pin', function(event) {
  pin_action(event);
});

$('#toggle_theme').click(function(event) {
  toggle_theme(event);
});

init_theme();

// New Message
function submit_new_message(event) {
  // Message input
  var message_input = $("#message_input").val();
  // Empty chat input
  $('#message_input').val('');
  $.ajax({
    url: "<?=base_url()?>new_message",
    type: "POST",
    data: {
      message_input: message_input,
      slug: slug
    },
    cache: false,
    success: function(response) {
      // console.log('submit');
      // All responses are error messsages
      if (response) {
        response = response.replace('<p>', '');
        response = response.replace('</p>', '');
        alert(response);
        return false;
      }
      // Load log so user can instantly see his message
      messages_load(false);
      // Focus back on input
      $('#message_input').focus();
      // Scroll to bottom
      scroll_to_bottom();
    }
  });
  return false;
}

// User load
function users_load() {
  $.ajax({
    url: "<?=base_url()?>users_load",
    type: "POST",
    data: {
      room_key: room_key
    },
    cache: false,
    success: function(response) {
      var users = JSON.parse(response);
      var user_count = users.length;
      $('#user_count').html(user_count);
      var user_list = '';
      users_array = users;
      for (var i = 0; i < user_count; i++) {
        user_list += users[i]['username'];
        if (i < user_count - 1) {
          user_list += ', ';
        }
      }
      $('#user_count_parent').prop('title', user_list);
      $('#user_list').html(user_list);
    }
  });
}

$('#user_count_parent').click(function(){
  if ($('#user_count_parent').hasClass('active')) {
    $('#user_count_parent').removeClass('active');
    $('#user_list_parent').hide();
  }
  else {
    $('#user_count_parent').addClass('active');
    $('#user_list_parent').show();
  }

});

// Message Load
function messages_load(inital_load) {
  if (!load_messages) {
    return false;
  }
  $.ajax({
    url: "<?=base_url()?>load",
    type: "POST",
    data: {
      room_key: room_key,
      slug: slug,
      inital_load: inital_load,
      last_message_id: last_message_id
    },
    cache: false,
    success: function(response) {
      console.log('load');
      var html = '';
      // Emergency force reload
      if (response === 'reload') {
        window.location.reload(true);
      }
      // Parse messages and loop through them
      messages = JSON.parse(response);
      if (!messages) {
        return false;
      }
      // Handle errors
      if (messages.error && load_messages && window_active) {
        // Prevent stacking errors
        load_messages = false;
        // Alert user
        alert(messages.error + '. You\'ll be redirected so you can rejoin the room.');
        // Redirect to try to rejoin user
        window.location = '<?=base_url()?>join_start/<?php echo $room['slug']; ?>';
        // Prevent more execution
        return false;
      }
      $.each(messages, function(i, message) {
        // Skip if we already have this message, although we really shouldn't
        if (parseInt(message.id) <= parseInt(last_message_id)) {
          return true;
        }
        // Update latest message id
        last_message_id = message.id;
        // If window is not active, give feedback in tab title
        if (!window_active && !inital_load) {
          missed_messages++;
          $('title').html('(' + missed_messages + ') ' + page_title);
        }
        // System Messages
        if (parseInt(message.user_key) === <?php echo $this->system_user_id; ?>) {
          html += '<div class="system_message ' + message.username + '">' + message.message + '</div>';
          return true;
        }
        // Process message
        var message_message = urls_to_embed(message.message);
        // Wrap @username with span
        message_message = convert_at_username(message_message);
        // Detect if youtube
        // build message html
        html += '<div class="message_parent">';
        html += '<span class="message_face glyphicon glyphicon-user" title="' + message.timestamp + ' ET" style="color: ' + message.color + ';"></span>';
        if (use_pin(message_message)) {
          html += '<span class="message_pin glyphicon glyphicon-pushpin" style="color: ' + message.color + ';"></span>';
        }
        html += '<span class="message_username" style="color: ' + message.color + ';">' + message.username + '</span>';
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

function autocomplete_username() {
  if ($('#message_input').val().startsWith('@')) {
    var parsed_text_input = $('#message_input').val().replace('@','').toLowerCase();
    for (var i = 0; i < users_array.length; i++) {
      if (users_array[i].username.toLowerCase().startsWith(parsed_text_input)) {
        $('#message_input').val('@' + users_array[i].username);
      }
    }
  }
}

function pin_action(event) {
  if (!$(event.target).hasClass('active_pin')) {
    $('.active_pin').removeClass('active_pin');
    $('.pinned').removeClass('pinned')
    $(event.target).addClass('active_pin');
    $(event.target).parent().addClass('pinned')
  } else {
    $(event.target).removeClass('active_pin');
    $(event.target).parent().removeClass('pinned')
  }
}

function urls_to_embed(message) {
  // Order important
  message = convert_youtube(message);
  message = convert_vimeo(message);
  message = convert_twitch(message);
  message = convert_soundcloud(message);
  message = convert_vocaroo(message);
  message = convert_video_url(message);
  message = convert_image_url(message);
  message = convert_general_url(message);
  return message
}

function use_pin(message) {
  if (
    string_contains(message, 'message_youtube') ||
    string_contains(message, 'message_vimeo') ||
    string_contains(message, 'message_twitch') ||
    string_contains(message, 'message_soundcloud') ||
    string_contains(message, 'message_vocaroo') ||
    string_contains(message, 'message_video') ||
    string_contains(message, 'message_image')
  ) {
    return true;
  }
  return false;
}

function convert_at_username(input) {
  var pattern = /^\@\w+/g;
  if (pattern.test(input)) {
    var at_username = input.split(' ')[0];
    if (!at_username) {
      return input;
    }
    var replacement = '<span class="at_username">' + at_username + '</span>';
    var input = input.replace(pattern, replacement);
  }
  return input;

}

function convert_youtube(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?!channel\/)(?!user\/)(?:watch\?v=)?([a-zA-Z0-9_-]{11})(?:\S+)?/g;
  if (pattern.test(input)) {
    var replacement = '<span class="message_youtube_parent"><iframe src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen class="message_youtube message_content"></iframe></span>';
    var input = input.replace(pattern, replacement);
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

function convert_soundcloud(input) {
  var pattern = /(?:http?s?:\/\/)?(?:www\.)?(?:soundcloud\.com)(\/\S+\/)(\S+)/g;
  if (pattern.test(input)) {
    // Soundcloud requires an api call
    // We use a placeholder span with id, and will target and fill that span on success
    var soundcloud_id = new Date().valueOf();
    var settings = {
      "url": "http://soundcloud.com/oembed",
      "method": "POST",
      "crossDomain": true,
      "data": {
        "format": "json",
        "url": input
      }
    }
    $.ajax(settings).done(function (response) {
      $('#' + soundcloud_id).html(response.html);
    });
    var replacement = '<span id="' + soundcloud_id + '" class="message_soundcloud message_content"></span>';
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
  // Ignore " to not conflict with other converts
  var pattern = /(?!.*")([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?(?:jpg|jpeg|gif|png)(?:\?\S+)?)/gi;
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

function toggle_theme(event) {
  if ($(event.target).hasClass('active')) {
    $(event.target).text('Switch to Dark Theme');
    $(event.target).removeClass('active');
    $('body').addClass('light');
    $('#message_content_parent').addClass('light');
  } else {
    $(event.target).text('Switch to Light Theme');
    $(event.target).addClass('active');
    $('body').removeClass('light');
    $('#message_content_parent').removeClass('light');
  }
}

</script>