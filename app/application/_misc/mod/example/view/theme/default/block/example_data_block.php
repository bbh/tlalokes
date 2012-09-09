    <h1>Block data</h1>

<?php
if ( $example = response( 'example' ) ) :
?>
    <p>Id: <?=$example['id_example'];?></p>
    <p>Id: <?=$example['vc_name'];?></p>
    <p>Id: <?=$example['t_description'];?></p>
<?php
endif;
?>
