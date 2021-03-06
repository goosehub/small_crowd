<div id="message_outer_parent">
  <div id="message_content_parent">
    Loading...
  </div>
  <div id="message_input_parent">
    <form id="new_message" onsubmit="return submit_new_message()">
      <input type="text" name="message_input" class="form-control" id="message_input" autocomplete="off" value="" placeholder="" />
      <!-- submit button positioned off screen -->
      <input name="submit_message" type="submit" id="submit_message" value="true" style="position: absolute; left: -9999px">
    </form>
  </div>
  <div id="toolbar">
    <a href="<?=base_url()?>" id="join_new" class="btn btn-sm btn-primary">
      Join a New Room
    </a>
    <div id="toggle_theme" class="btn btn-sm btn-danger active">Switch to Light Theme</div>
    <div id="user_count_parent" class="btn btn-default" title="">
      <span id="user_count">0</span><?php if (!$room['archived']) { ?> / <?php echo $room_capacity; ?> <?php } ?>
      <span id="user_list_parent" style="display: none;"> | <span id="user_list"></span></span>
    </div>
  </div>
</div>