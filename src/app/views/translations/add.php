<?php require APPROOT . '/views/inc/header.php'; ?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card bg-light mt-5">
            <div class="card-header card-text">
                <div class="row">
                    <div class="col">
                        <h2 class="card-text">Add New Translation</h2>
                    </div>
                    <div class="col">
                        <a href="<?php echo URLROOT ;?>/translations" class="btn btn-light pull-right"><i class="fa fa-backward"></i> Back</a>
                    </div>
                </div>
            </div>
        
            <div class="card-body">
                <form method="post" action="<?php echo URLROOT ;?>/translations/add">
                    <div class="form-group">
                        <label for="source">Source<sub>*</sub></label>
                        <textarea rows="5" type="text" id="source" name="source" class="form-control form-control-lg <?php echo (!empty($data['source_err'])) ? 'is-invalid' : '' ;?>" value="<?php echo $data['source'] ;?>"></textarea>
                        <span class="invalid-feedback"><?php echo $data['source_err']; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="translation">Translation<sub>*</sub></label>
                        <textarea rows="5" type="text" id="translation" name="translation" class="form-control form-control-lg <?php echo (!empty($data['translation_err'])) ? 'is-invalid' : '' ;?>" value="<?php echo $data['translation'] ;?>"></textarea>
                        <span class="invalid-feedback"><?php echo $data['translation_err']; ?></span>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <input type="submit" class="btn btn-success btn-block pull-left" value="Add Translation">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>