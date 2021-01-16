<div class="">
  <h4>About Envelope <?php echo $envelope['envelope_id']; ?></h4>

  <p>All about this envelope and his documents status.</p>

  <div class="">
    <?php if (isset($envelope)): ?>
      <table class="table table-dark">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">Document Name</th>
            <td><?php echo $envelope['file_name']; ?></td>
          </tr>
          <tr>
            <th scope="row">Signer name</th>
            <td><?php echo $envelope['signer_name']; ?></td>
          </tr>
          <tr>
            <th scope="row">Signer email</th>
            <td><?php echo $envelope['signer_email']; ?></td>
          </tr>
          <tr>
            <th scope="row">Cc name</th>
            <td><?php echo $envelope['cc_name']; ?></td>
          </tr>
          <tr>
            <th scope="row">Cc email</th>
            <td><?php echo $envelope['cc_email']; ?></td>
          </tr>
          <tr>
            <th scope="row">ID</th>
            <td><?php echo $envelope['envelope_id']; ?></td>
          </tr>
          <tr>
            <th scope="row">Date of send</th>
            <td><?php echo $envelope['date']; ?></td>
          </tr>
          <tr>
            <th scope="row">State</th>
            <td><?php echo $envelope['status']; ?></td>
          </tr>
          <?php if ($envelope['status'] == "completed"): ?>
            <tr>
              <th scope="row">Download the document</th>
              <td><a href="<?php echo "/index.php?page=download&id=" . $envelope['id'] . "&envelope_id=" . $envelope['envelope_id']; ?>"><button style="margin-left:20px;" class="btn btn-docu">Download</button></a></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    <?php else: ?>
        <p>Something goes wrong. <a href="/index.php?page=dashboard"><button style="margin-left:20px;" class="btn btn-docu">Try again please</button></a>.</p>
    <?php endif; ?>
  </div>

<div class="">
  <a href="<?php echo "/index.php?page=envelope&id=" . $envelope['id']; ?>"><button style="margin-left:20px;" class="btn btn-docu">Update state</button></a>
  <a href="/"><button style="margin-left:20px;" class="btn btn-docu">Send a file to sign</button></a>
  <a href="/index.php?page=dashboard"><button style="margin-left:20px;" class="btn btn-docu">Back</button></a>
</div>
</div>
