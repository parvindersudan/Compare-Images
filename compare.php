<?php
  set_time_limit(0);

  error_reporting(E_ALL ^ E_NOTICE);
  ini_set('display_errors', true);

  include_once('simple_html_dom.php');

  $pathToConvert = "/usr/bin/convert";
  $pathToCompare = "/usr/bin/compare";

  define("IMAGES_PATH","images/");

  if($_POST['url'] != ""){
    $url = $_POST['url'];
  } else {
    $url = "";
  }

  if($_POST['image1'] != ""){
    $image1 = $_POST['image1'];
  } else {
    $image1 = "";
  }

  if($_POST['image2'] != ""){
    $image2 = $_POST['image2'];
  } else {
    $image2 = "";
  }

  if($_POST['libpuzzlethreshold'] != ""){
    $libpuzzlethreshold = $_POST['libpuzzlethreshold'];
  } else {
    $libpuzzlethreshold = 0.5;
  }


  if($_POST['imagemagicthreshold'] != ""){
    $imagemagicthreshold = $_POST['imagemagicthreshold'];
  } else {
    $imagemagicthreshold = 0.2;
  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <style>
      table, td, th
      {
        border:1px solid grey;
      }
      img {
        outline: 1px solid black;
      }
      ul.products li {
        width: 370px;
        display: inline-block;
        vertical-align: top;
      }
      
      h4 {
        font-family:verdana;font-size:12px;
      }
    </style>
  </head>
  <body bgcolor="#dadada">

    <form name="form1" action="" method="post">
      <table border="1" width="800" style="font-family: verdana; font-size:12px;">
        <tr><td>Enter the Moebel Image 1 Url</td><td><input type="text" name="image1" style="width:400px;" value="<?=$image1?>"></td></tr>
        <tr><td>Enter the Moebel Image 2 Url</td><td><input type="text" name="image2" style="width:400px;" value="<?=$image2?>"></td></tr>        
        <tr>
          <td>Libpuzzle Threshold</td>
          <td>
            <select name='libpuzzlethreshold'>
              <?php
                for($j=0;$j<=1;$j=$j+0.1){
                  if($j == $libpuzzlethreshold){
                    $selected = " selected";
                  } else {
                    $selected = "";
                  }
                  echo '<option value="'.$j.'" '.$selected.'>'.$j.'</option>';
                }
              ?>
            </select>
          </td>
        </tr>

        <tr>
          <td>ImageMagic Threshold</td>
          <td>
            <select name='imagemagicthreshold'>
              <?php
                for($j=0;$j<=1;$j=$j+0.1){
                  if($j == $imagemagicthreshold){
                    $selected = " selected";
                  } else {
                    $selected = "";
                  }
                  echo '<option value="'.$j.'" '.$selected.'>'.$j.'</option>';
                }
              ?>
            </select>
          </td>
        </tr>

        <tr><td colspan='2' align='left'><input type="submit" value="Submit"></td></tr>
      </table>
    </form>

    <? 
      if($_POST['url'] != ""){

        $newurl=$_POST['url'];

        $html = file_get_html($newurl);

        if(is_null($html) || $html==''){
          echo "<p>Please try again</p>";
          exit();
        }

        $cnt=0;

        echo '<ul class="products">';

        foreach($html->find('div.product_box_container') as $product) {

          //get Title
          $title = trim($product->find('div.subcategor_slideimgtitle', 0)->plaintext);
          $image = trim($product->find('div.product_box_images', 0)->getAttribute('style'));
          $image = str_replace("'","",$image);
          $image = str_replace("'","",$image);
          $image = str_replace("(","",$image);
          $image = str_replace(")","",$image);
          $image = str_replace(";","",$image);
          $image = str_replace("background-image:url","",$image);

          echo '
          <li>
          <img src="'.$image.'">
          <h4>'.$title.'</h4>
          </li>';               
        }

        echo "</ul>";
      }


      if($_POST['image1'] != "" && $_POST['image2'] != ""){


        $image1 = IMAGES_PATH."image1.jpg";
        file_put_contents($image1, file_get_contents($_POST['image1']));
        chmod($image1,0666);

        $image2 = IMAGES_PATH."image2.jpg";
        file_put_contents($image2, file_get_contents($_POST['image2']));
        chmod($image2,0666);

        # Compute signatures for two images
        $cvec1 = puzzle_fill_cvec_from_file($image1);
        $cvec2 = puzzle_fill_cvec_from_file($image2);

        # Compute the distance between both signatures
        $d = puzzle_vector_normalized_distance($cvec1, $cvec2);


        # Are pictures similar?
        if ($d < $libpuzzlethreshold) {
          //$result =  "Pictures are looking similar, distance=$d\n";
          $result =  "Yes";
        } else {
          //$result = "Pictures are different, distance=$d\n";
          $result =  "No";
        }

        echo "<table width='1200' cellspacing='0' cellpadding='5' style='font-family: verdana; font-size:12px;'>
        <tr>
        <td width='350px;' style='font-weight:bold;text-align:center;'>Image 1</td>
        <td width='350px;' style='font-weight:bold;text-align:center;'>Image 2</td>
        </tr>
        <tr>
        <td align='center' valign='top'><img src='".$image1."'></td>
        <td align='center' valign='top'><img src='".$image2."'></td>  
        </tr>
        </table>";

        echo "<BR><BR>";

        echo "<br><table width='1200' cellspacing='0' cellpadding='5' style='font-family: verdana; font-size:12px;'>";
        echo '<tr>
        <td width="350px;" style="font-weight:bold;text-align:center;">Type</td>
        <td width="150px;" style="font-weight:bold;text-align:center;">Distance</td>
        <td width="150px;" style="font-weight:bold;text-align:center;">Similar</td>
        </tr>';

        //dispaly the details
        echo "<tr>
        <td align='center' valign='top'>Libpuzzle with Threshold : ".$libpuzzlethreshold."</td>

        <td align='center' valign='top'>".$d."</td>
        <td align='center' valign='top'>".$result."</td>
        </tr>";




        $differenceImage = IMAGES_PATH."difference.jpg";
        //-verbose
        $command = $pathToCompare." -metric RMSE ".$image1." ".$image2." ".$differenceImage;

        $distanceImageMagic = exec($command." 2>&1");

        $distanceImageMagicArr = explode(" ",$distanceImageMagic);
        $resultImageMagicStr = str_replace("(","",$distanceImageMagicArr[1]);
        $resultImageMagicStr = str_replace(")","",$distanceImageMagicArr[1]);
        $resultImageMagicStr = trim($resultImageMagicStr);

        if($resultImageMagicStr >= $imagemagicthreshold){
          $resultImageMagic="No";
        } else {
          $resultImageMagic="Yes";
        }

        //dispaly the details
        echo "<tr>
        <td align='center' valign='top'>ImageMagic with Threshold : ".$imagemagicthreshold."</td>
        <td align='center' valign='top'>".$distanceImageMagic."</td>
        <td align='center' valign='top'>".$resultImageMagic."</td>
        </tr>";

        echo "</table>";  

      }
    ?>
  </body>
</html>