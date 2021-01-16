
  <h4>Send an envelope with a remote (email) signer and cc recipient</h4>

  <form class="eg" action="/index.php?page=send_envelope" method="post" data-busy="form" enctype='multipart/form-data'>
      <div class="form-group">
          <label for="signer_email">Signer Email</label>
          <input type="email" class="form-control" id="signer_email" name="signer_email"
                 aria-describedby="emailHelp" placeholder="pat@example.com" required
                 >
          <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
      </div>
      <div class="form-group">
          <label for="signer_name">Signer Name</label>
          <input type="text" class="form-control" id="signer_name" placeholder="Pat Johnson" name="signer_name"
                  required>
      </div>
      <div class="form-group">
          <label for="cc_email">CC Email</label>
          <input type="email" class="form-control" id="cc_email" name="cc_email"
                 aria-describedby="emailHelp" placeholder="pat@example.com" required >
          <small id="emailHelpCC" class="form-text text-muted">The email for the cc recipient must be different from the signer's email.</small>
      </div>
      <div class="form-group">
          <label for="cc_name">CC Name</label>
          <input type="text" class="form-control" id="cc_name" placeholder="Pat Johnson" name="cc_name"
                 required >
      </div>
      <div class="form-group">
        <label for="formFile" class="form-label">Default file input example</label>
        <input class="form-control" type="file" id="formFile" required name="fileToUpload">
      </div>
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"/>
      <button type="submit" class="btn btn-docu">Submit</button>
  </form>

  <div class="">
    <a href="/index.php?page=dashboard"><button style="margin-top:20px;" class="btn btn-docu">Go to dashboard</button></a>
  </div>
