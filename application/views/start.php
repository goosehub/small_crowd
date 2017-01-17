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
                <input type="color" name="color" id="start_color" class="form-control"/>
                <br>
                <button type="submit" class="start_join_button btn btn-action form-control"><strong>Join a Room</strong></button>
            </form>
            <hr>
            <p class="start_subtitle lead text-center">Get paired with <?php echo $this->room_capacity - 1; ?> strangers from around the world</p>
        </div>
    </div>
</div>

<div id="beta_parent">
    <small>Small Crowd is in open beta. Please contact goosepostbox@gmail.com with any bugs, unexpected behavior, or confusing interfaces.</small>
</div>

<script>
    $('#start_color').val(randomColor());
</script>