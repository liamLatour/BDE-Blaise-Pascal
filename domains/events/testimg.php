<?php
include('../../dependances/class/base.php');

// $alt = $gestion_images->getImageAlt('test');

if (isset($_POST['submit']) && !empty($_FILES["file"]["name"]) && isset($_POST['name']))
{
    // $size = getimagesize($_FILES["file"][tmp_name]);
    // $ratio = $size[0]/$size[1];
    // if ($ratio >= 6.8 && $ratio <= 7.2)
    // {
        var_dump($gestion_images->uploadImage($_POST['name'], $_FILES["file"]));
        echo'<img src="https://docs.bde-bp.fr/images/imgprnt.php?i='.$_POST['name'].'" style="width: 250px">';
    // }
    // else
    // {
    //     var_dump($ratio);
    // }
}
?>


<!-- <img src="https://docs.bde-bp.fr/images/imgprnt.php?i=test" style="width: 250px" alt="<?php echo $alt ?>"> -->

<form action="testimg.php" method="post" enctype="multipart/form-data">
    Select Image File to Upload:
    <input type="file" name="file">
    <input type="text" name="name">
    <input type="submit" name="submit" value="Upload">
</form>

