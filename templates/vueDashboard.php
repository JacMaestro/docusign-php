<div class="">
  <h4>Wellcome ! </h4>

  <p>Here is a list of all yours envelopes.</p>

  <div class="">
    <?php if (isset($envelopes) && count($envelopes) > 0): ?>
      <table class="table table-dark">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Document name</th>
            <th scope="col">Signer Name</th>
            <th scope="col">Cc Name</th>
            <th scope="col">Date</th>
            <th scope="col">#</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($envelopes as $key => $value): ?>
            <tr>
              <th scope="row"><?php echo $key;  ?></th>
              <td><?php echo $value['file_name']; ?></td>
              <td><?php echo $value['signer_name']; ?></td>
              <td><?php echo $value['cc_name']; ?></td>
              <td><?php echo $value['date']; ?></td>
              <td><a href="<?php echo "/index.php?page=envelope&id=" . $value['id']; ?>"><button style="margin-left:20px;" class="btn btn-docu">See status</button></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
        <p>No envelopes was sended yet.</p>
    <?php endif; ?>
  </div>

<div class="">
  <a href="/"><button style="margin-left:20px;" class="btn btn-docu">Send a file to sign</button></a>
</div>
</div>
