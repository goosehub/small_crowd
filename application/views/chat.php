<!-- Chat HTML -->
<div id="chat_parent">
  <div id="chat_messages_parent">
    <div id="chat_messages_box">
      Loading...
    </div>
  </div>

  <div id="chat_input_parent">
    <form name="new_chat" id="new_chat" onsubmit="return chat_submit_function()">
      <input type="text" name="chat_input" class="form-control" id="chat_input" autocomplete="off" value="" placeholder="chat" />
      <!-- submit button positioned off screen -->
      <input name="submit_chat" type="submit" id="submit_chat" value="true" style="position: absolute; left: -9999px">
    </form>
  </div>
</div>