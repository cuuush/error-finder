<?php

require_once '../includes/includes.php';

$connection = new connection();

$latestJob = $connection->errors_getMostRecentJob();

if(isset($_GET['job'])){
    $job = $connection->errors_getJob($_GET['job']);
    $jobdate = strtotime($job['date']);
}
else{
    $jobdate = strtotime($latestJob['date']);

}

$jobdate = date('l, F jS Y, g:i A', $jobdate);


$latestJobNumber = $latestJob['job_id'];

if(isset($_GET['job']) && is_numeric($_GET['job'])){
    $urlJob = $_GET['job'];
    $jobnumber = $urlJob; // i dont know how to name stuff

}
else
    $jobnumber = $latestJobNumber;


?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.15.3/dist/bootstrap-table.min.css">


    <style>
        html {
            font-family: sans-serif;
        }

        input {
            width: 20em;
            height: 3em;
        }

        input[type=submit] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            font-size: 1em;
        }
        .row{
            padding-top: 1em;
        }

        .spacer{
            padding-bottom: 50px;
        }

    </style>
</head>

<body>



<div class='container'>



        <h1>Daily Report</h1>

        <h2>For <?php print($jobdate)?></h2>


        <a href="<?php echo $rootdir;?>">
            <button type="button" class="btn btn-primary">Back</button>
        </a>

        <div class="btn btn-secondary" id="download">Download CSV</div>

        <div class="spacer"></div>

        <p>Select a different date</p><Br>
            <ul class="pagination">
                <?php
                if($jobnumber > 1)
                    print('<li class="page-item"><a class="page-link" href="'. $rootdir .'/daily-report?job='. ($jobnumber - 1) .'">Previous</a></li>');
                else
                    print('<li class="page-item disabled"><a class="page-link">Previous</a></li>');

                if($jobnumber < $latestJobNumber)
                    print('<li class="page-item"><a class="page-link" href="'. $rootdir .'/daily-report?job='. ($jobnumber + 1) .'">Next</a></li>');
                else
                    print('<li class="page-item disabled"><a class="page-link">Next</a></li>');
                ?>

            </ul>





</div>
<table
        data-show-export="true"
        data-toggle="table"
        data-search="true"
        <?php
        if(isset($_GET['job']))
        $url = 'data-url="list.php?job=' . $_GET['job'] . '"';
        else
            $url = 'data-url="list.php"';
        print($url);
        ?>
        id="table1"
        class="table-dark">
    <thead>
    <tr>
        <th data-field="ip" data-formatter="identifierFormatter">IP</th>
        <th data-field="devicename">Name</th>
        <th data-field="date">Date</th>
        <th data-field="level">Error Level</th>
        <th data-field="reference">Reference</th>
        <th data-field="type">Error Type</th>
        <th data-field="text">Error Text</th>
    </tr>
    </thead>
</table>



<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<script src="https://unpkg.com/bootstrap-table@1.15.3/dist/bootstrap-table.min.js"></script>




<script src="js/FileSaver.min.js"></script>

<script src="js/tableExport.min.js"></script>


<script>
    $('#download').click(function() {
        $('#table1').tableExport({type:'csv'});
    });

    function identifierFormatter(value, row, index) {
        return [
            '<a id="IP" href=https://'+value+' title="'+value+'">',
            value,
            '</a>'].join('');
    }

    $(document).on('click', 'a', function(e){
        if($(this).attr('id') == "IP"){
        e.preventDefault();
        var url = $(this).attr('href');
        window.open(url, '_blank');
        }
    
    });


</script>
</body>

</html>
