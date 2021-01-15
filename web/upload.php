<?php


require_once 'includes/includes.php';

global $rootdir;

function getProperDeviceName($devicename){
    if(strpos($devicename, 'Cisco TelePresence')){
        $stringArray = explode(" ", $devicename);
        return $stringArray[2];
    }

    switch($devicename){
        case "Cisco TelePresence DX80":
            return "DX80";


    }
}

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

    $notACSV = false;

    if(!in_array($_FILES['file']['type'],$mimes) || substr(strtolower($_FILES['file']['name']),-4) != ".csv"){
        $notACSV = true;
    }




    else { //everything good
//        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
//            echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
//        } else {
//            echo "Sorry, there was an error uploading your file.";
//        }


        $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

        $csv = array();

        while (!feof($csvFile)) {
            $linetext = fgets($csvFile);
            if($linetext != false) {
                $linearray = str_getcsv($linetext);
                array_push($csv, $linearray);
            }

        }

        fclose($csvFile);

        array_shift($csv); //delete the column headers

        $connection = new connection();

        $endpointNames = $connection->queryEndpoints('devicename');

        $numberadded = 0;
        $numberUpdated = 0;

        foreach ($csv as $csvEndpoint) {

            if (!in_array($csvEndpoint[1], $endpointNames)) {
                $ip = $csvEndpoint[2];
                $devicename = $csvEndpoint[1];
                $devicetype = $csvEndpoint[4];

                $connection->addEndpoint($ip, $devicename, $devicetype);
                $numberadded++;
            }
            else if(in_array($csvEndpoint[1], $endpointNames)){
                $ipOnFile = $connection->queryEndpointFromName($csvEndpoint[1], 'ip');
                if($ipOnFile !== $csvEndpoint[2]){
                    $numberUpdated++;
                    $connection->updateEndpointIPFromName($csvEndpoint[1],$csvEndpoint[2]);
                }
            }
        }
    }
}


?>

<!DOCTYPE html>
<html>

<head>

    <link rel="stylesheet" href="lib/css/bootstrap.min.css">

    <style>
        html{
            font-family: sans-serif;
            background-color: aliceblue;
        }

        form{
            text-align: center;
        }
        input{
            width: 20em;
            height: 3em;
        }

        input[type=submit]{
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            font-size: 1em;
        }

        .container{
            text-align: center;
            background-color: lightgray;
        }

        .buttons{
            padding: 20px;
        }



    </style>
</head>

<body>
<div class='container'>
    <h1>Error Finder</h1>
    <h2>Endpoint Updater</h2>
    <div class="buttons">
        <a href="<?php echo $rootdir;?>/">
            <button type="button" class="btn btn-secondary">Back</button>
        </a>
    </div>
    <?php
    if(isset($numberadded)){
        print('<p style="color:green;">Parsed '. count($csv) .' lines, adding '. $numberadded .' endpoints and updating '. $numberUpdated .'.</p>');
    }




    if(isset($notACSV) && $notACSV)
        print('<p style="color:red;">You must upload a .CSV</p>')
    ?>


    <form action="upload.php" method="post" enctype="multipart/form-data">
        Select the system overview CSV
        <input type="file" name="file" id="file"><br><br>
        <input type="submit" value="Upload CSV" name="submit">
    </form>

    <br><br><h3>How to create this csv</h3>
    <ol>
        <li>
            Go to your TMS installation
        </li>
        <li>
            Go to <b>Systems</b> -> <b>System Overview</b>
        </li>
        <li>
            On the left check <b>Endpoints</b>
        </li>
        <li>
            On the right check <b>General Settings</b> -> <b>Specific System Type Description</b>
        </li>
        <li>Finally download this report and save it as a CSV format</li>
    </ol>
    <i>Note: This file must be a standard CSV in order to be interpreted</i>

</div>



</body>

</html>
