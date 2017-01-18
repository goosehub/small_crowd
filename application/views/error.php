<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <br> <br> <br> <br> <br> <br>
            <div class="alert alert-danger">
                <?php if ($validation_errors) { echo $validation_errors; } ?>
                <br>
                Please email goosepostbox@gmail.com to help me fix this.
            </div>
            <br> <br> <br>
            <a href="<?=base_url()?>" class="btn btn-action form-control">Take me back to the start</a>
        </div>
    </div>
</div>