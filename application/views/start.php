<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <h1 class="text-center"><?php echo site_name(); ?></h1>
            <form action="<?=base_url()?>join_room" method="post">
                <label>Name</label>
                <input type="text" name="username" id="start_username" class="form-control"/>
                <label>Location</label>
                <input type="text" name="location" id="start_location" class="form-control"/>
                <label>Color</label>
                <input type="color" name="color" id="start_color" class="form-control"/>
                <br>
                <button type="submit" class="btn btn-action form-control"><strong>Join a Room</strong></button>
            </form>
        </div>
    </div>
</div>

<script>
    $('#start_color').val(randomColor());
</script>