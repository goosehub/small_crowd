<div class="container">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div id="message_outer_parent">
        <div id="message_content_parent">
          Loading...
        </div>
      </div>
      <div id="message_input_parent">
        <form name="new_message" id="new_message" onsubmit="return submit_new_message()">
          <input type="text" name="message_input" class="form-control" id="message_input" autocomplete="off" value="" placeholder="message" />
          <!-- submit button positioned off screen -->
          <input name="submit_message" type="submit" id="submit_message" value="true" style="position: absolute; left: -9999px">
        </form>
      </div>
    </div>
  </div>
</div>
