<?php

require_once 'includes/includes.php';

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="lib/css/bootstrap.min.css">
    <link rel="stylesheet" href="lib/css/bootstrap-table.min.css">


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

    </style>
</head>

<body>



<div class='container'>


    <div class='row justify-content-center' style="padding-top: 2em">
        <h1>Error List</h1>
    </div>

    <div class='row justify-content-center'>
        <a href="<?php echo $rootdir;?>/">
            <button type="button" class="btn btn-secondary">Back</button>
        </a>

    </div>

    <div class='row justify-content-center'>
        <div class="btn btn-primary" id="download">Download CSV</div>
    </div>

    <div class='row justify-content-center'>

    </div>








</div>
<table
        data-show-export="true"
        data-toggle="table"
        data-search="true"
        data-url="json.php"
        id="table1"
        class="table-dark">
    <thead>
    <tr>
        <th data-field="ip" data-formatter="identifierFormatter">IP</th>
        <th data-field="name">Name</th>
        <th data-field="date">Date</th>
        <th data-field="level">Error Level</th>
        <th data-field="reference">Reference</th>
        <th data-field="type">Error Type</th>
        <th data-field="text">Error Text</th>
    </tr>
    </thead>
</table>



<script src="lib/js/jquery.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
<script src="https://unpkg.com/bootstrap-table@1.15.3/dist/bootstrap-table.min.js"></script>




<script src="lib/js/FileSaver.min.js"></script>

<script src="lib/js/tableExport.min.js"></script>


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
