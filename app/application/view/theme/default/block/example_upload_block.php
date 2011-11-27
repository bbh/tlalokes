    <h1>Block data</h1>

<?php
// check if upload failed
if ( !tf_response( 'flag' ) ) :

  // check if file was sent
  if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) :

    // print file type error
    if ( $error_type = tf_app_error( 'Upload filter type' ) ) :
?>
    <p><?php echo $error_type;?></p>
<?php
    endif;

    // print file size error
    if ( $error_size = tf_app_error( 'Upload filter size' ) ) :
?>
    <p><?php echo $error_size;?></p>
<?php
    endif;
  endif;

  // nothing sent print form
?>
    <form method="post" action="?controller=example&action=uploadFile<?php echo tf_debug_var();?>" enctype="multipart/form-data">
      <label>Select your file</label>
      <input type="file" name="myfile" />
      <input type="submit" value="Send" />
    </form>
<?php
// upload was OK
else :
?>
    <p>File upload successful</p>
    <p><a href="javascript:history.back();">Back</a></p>
<?php
endif;
?>
