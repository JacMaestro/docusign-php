
  <h4>Sorry, you need to re-authenticate to DocuSign before.</h4>

  <form class="eg" action="/index.php?page=ds_login" method="post" data-busy="form">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"/>
      <button type="submit" class="btn btn-docu">Continue</button>
      <a href="<?php echo $back_url; ?>"><button style="margin-left:20px;" class="btn btn-docu">Back</button></a>
  </form>
