<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <h1 class="text-center"><?php echo site_name(); ?></h1>
            <form action="<?=base_url()?>new" method="post">
                <br>
                <button type="submit" class="btn btn-action form-control"><strong>Join a Room</strong></button>
                <br>
                <br>
                <label>Name <small>(Optional)</small></label>
                <input type="text" name="username" class="form-control"/>
                <label>Location <small>(Optional)</small></label>
                <input type="text" name="location" class="form-control"/>
            </form>
        </div>
    </div>
</div>