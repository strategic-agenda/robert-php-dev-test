<?php require APPROOT . '/views/inc/header.php'; ?>
  <div class="jumbotron jumbotron-flud text-center">
    <div class="container">
    <h1 class="display-3"><?php echo $data['title']; ?></h1>
    <p class="lead"><b><?php echo $data['description']; ?></b></p>
    <p class="lead"><?php echo $data['info']; ?></p>
    <p class="lead"><b><?php echo $data['name']; ?></b></p>
    <p class="lead"><?php echo $data['location']; ?></p>
    <p class="lead"><b><?php echo $data['contact']; ?></b></p>
    <p class="lead"><ol><?php echo $data['mail']; ?></ol></p>
    </div>
  </div> 
<?php require APPROOT . '/views/inc/footer.php'; ?>
