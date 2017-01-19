<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <h1 class="start_title text-center"><?php echo site_name(); ?></h1>
            <?php if ($error) { ?>
            <div class="start_error alert alert-danger text-center"><?php echo $error; ?></div>
            <?php } ?>
            <form action="<?=base_url()?>join_room" method="post">
                <label class="start_label">Name</label>
                <input type="text" name="username" id="start_username" class="form-control"/>
                <label class="start_label">Location</label>
                <input type="text" name="location" id="start_location" class="form-control"/>
                <label class="start_label">Color</label>
                <input type="color" name="color" id="start_color" class="jscolor form-control"/>
                <br>
                <button type="submit" class="start_join_button btn btn-action form-control">Join a Room</button>
            </form>
            <hr>
            <p class="start_subtitle lead text-center">Get paired with <?php echo $this->room_capacity - 1; ?> strangers from around the world</p>
        </div>
    </div>
</div>

<div id="start_footer">
    <a href="https://www.reddit.com/r/smallcrowd/" target="_blank">/r/smallcrowd</a>
    |
    <a href="https://github.com/goosehub/small_crowd" target="_blank">GitHub</a>
    |
    <a href="http://gooseweb.io/" target="_blank">gooseweb.io</a>
    |
    <a href="<?=base_url()?>about">More Info</a>
    <br>
    <small>Contact goosepostbox@gmail.com with any bugs, unexpected behavior, or confusing interfaces.</small>
</div>

<script>
    $('#start_color').val(randomColor());
</script>