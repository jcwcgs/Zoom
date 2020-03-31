<?php
if(isset($_POST['submit'])){
    if(count($_FILES['upload']['name']) > 0){
        //Loop through each file
        for($i=0; $i<count($_FILES['upload']['name']); $i++) {
          //Get the temp file path
            $tmpFilePath = $_FILES['upload']['tmp_name'][$i];

            //Make sure we have a filepath
            if($tmpFilePath != ""){
            
                //save the filename
                $shortname = $_FILES['upload']['name'][$i];

                //save the url and the file
                $filePath = "uploads/" . $_FILES['upload']['name'][$i];

                //Upload the file into the temp dir
                if(move_uploaded_file($tmpFilePath, $filePath)) {

                    $files[] = $shortname;
                    //insert into db 
                    //use $shortname for the filename
                    //use $filePath for the relative url to the file

                }
              }
        }
    }
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Update User Profile Photo - Zoom</title>
  <meta name="description" content="Update Zoom's User Photo">
  <meta name="author" content="Jeff Wang">

  <!-- Compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

  <!-- Compiled and minified JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

</head>

<body>
<?
    $domain = $_POST['domain'];
    $token = $_POST['token'];

    $added = "";
    $failed = "";

    foreach(glob('./uploads/*.jpg') as $filename){
        
        $curl = curl_init();

        $pathpart = pathinfo($filename);

        $username = $pathpart['filename'];
        $photo = $pathpart['basename'];

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.zoom.us/v2/users/" . $username . "@" . $domain . "/picture",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array('pic_file'=> new CURLFILE("uploads/" . $photo)),
        CURLOPT_HTTPHEADER => array(
            "Content-Type: multipart/form-data",
            "Authorization: Bearer $token"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        if(strpos($response, 'added_at') > 0) $added.= '<p>' . $response . '</p>'; else $failed.= '<p>' . $response . '</p>';
    }

    array_map('unlink', glob("./uploads/*.jpg"));
}

echo '<div align="center" style="margin-top:15px">
        <div align="left" style="width: 60%; border:solid thin; padding-bottom: 30px;">
            <div style="padding:20px">
                <h4>Upload Completed</h4>
                <p><strong>The following user photos are uploaded successfully:</strong></p>
                <p>' . $added . '</p>
                <p><strong>The following upload has failed:</strong></p>
                <p>' . $failed . '</p>
            </div>
        </div>
      </div>';
?>
</body>
</html>
