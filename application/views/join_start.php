<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <h1 class="start_title text-center"><?php echo site_name(); ?></h1>
            <p class="start_subtitle lead text-center">You are about to join room <?php echo $room['slug']; ?></p>
            <form action="<?=base_url()?>join_room/<?php echo $room['slug']; ?>" method="post">
                <input type="hidden" name="slug" id="start_slug" value="<?php echo $room['slug']; ?>"/>
                <label class="start_label">Name</label>
                <input type="text" name="username" id="start_username" class="form-control"/>
                <label class="start_label">Location</label>
                <input type="text" name="location" id="start_location" class="form-control"/>
                <label class="start_label">Color</label>
                <input type="color" name="color" id="start_color" class="form-control"/>
                <br>
                <button type="submit" class="start_join_button btn btn-action form-control"><strong>Join This Room</strong></button>
            </form>
            <hr>
        </div>
    </div>
</div>

<script>
    $('#start_color').val(randomColor());
</script>