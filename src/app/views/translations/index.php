<?php require APPROOT . '/views/inc/header.php'; ?>
<?php flash('translation_message'); ?>
<div class="row ">
    <div class="col-md-8">
        <h2>Translations</h2>
    </div>
    <div class="col-md-4">
        <a class="btn btn-success pull-right" href="<?php echo URLROOT ;?>/translations/add"><i class="fa fa-plus"></i> Add Translation</a>
    </div>
</div>
<?php foreach ($data['translations'] as $translation) : ?>
    <div class="card mb-3 mt-2">
        <div class="card-body"><h2 class="card-text"><?php echo  $translation->source ;?></h2></div>
        <p class="card-body">
            <?php echo  $translation->translation ;?>
        </p>
        <p class="card-title bg-light p-2 mb-3">
            Created By <?php echo $translation->name ;?> on <?php echo  $translation->translationCreated ;?>
        </p>
        <a href="<?php echo URLROOT ;?>/translations/show/<?php echo $translation->translationId ;?>" class="btn btn-dark btn-block">More...</a>
    </div>
<?php endforeach ;?>
<?php require APPROOT . '/views/inc/footer.php'; ?>