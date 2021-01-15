<?php


require_once 'includes/includes.php';

$connection = new connection();

$job = $connection->errors_getMostRecentJob();

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="lib/css/bootstrap.min.css">


    <style>
        .container{
            background-color: lightgray;
        }

        .output{
            overflow-y: scroll;
            height: 20em;
            width: 100%;
            background-color: black;
            color: lawngreen;
            padding: .5em;
            transition: 0.3s;
        }

        .output:hover{
            -webkit-box-shadow: 0px 0px 68px -19px rgba(0,0,0,0.75);
            -moz-box-shadow: 0px 0px 68px -19px rgba(0,0,0,0.75);
            box-shadow: 0px 0px 68px -19px rgba(0,0,0,0.75);
        }

        .menu-buttons{
            margin: 10px;
            text-align: center;

        }



    </style>
</head>



<body>

<div class='container'>

    <div class='menu-buttons' >
        <h1>Error Finder</h1>
        <a href="list.php">
            <button type="button" class="btn btn-primary">List All Errors</button>
        </a>
        <a href="daily-report">
            <button type="button" class="btn btn-primary">Daily Report</button>
        </a>
        <a href="upload.php">
            <button type="button" class="btn btn-primary">Update Endpoint List</button>
        </a>


        <h3>Welcome to Error Finder!</h3>
        <h4>Press "Run" to start the Error finding process!</h4>

        <div class="btn-group" role="group" aria-label="App Controls">

            <?php if($job['active'] == 1) {
                print('<button type="button" id="run"class="btn btn-success"><b>Running</b></button>');
                print('<button type="button" id="stop" class="btn btn-danger">Stop</button>');
            }
            else {
                print('<button type="button" id="run"class="btn btn-success">Run</button>');
                print('<button type="button" id="stop" class="btn btn-danger"><b>Stopped</b></button>');
            }
            ?>

        </div>
    </div>


    <div class="row">
        <div id="output" class="output">
            <div id="response">Click "Run" to start this app<br></div>
            <div id="bottom"></div>
        </div>

    </div>


</div>

<script src="lib/js/jquery.js"></script>

<script>
    var outputFunction = null;
    var getOutputInterval;

    var waitingForResponse = false;

    <?php
    if($job['active'] == 1) {
        print ('getOutput();');
        print('getOutputInterval = setInterval(getOutput, 500);');
            }
?>
    var mouseIsOver = false;

    $("#output").mouseenter(function(){
        mouseIsOver = true;
    }).mouseleave(function(){
        mouseIsOver = false;
    });

    function log(message){
        $('#response').append(message + "<br>");
    }

    function clearLog(){
        $('#response').html("");
    }



    function checkForRun(){
        checkAttempts = 0
        waitingForResponse = true;
        $.post('app.php', {action: 'status'}, function(data){
            if(data == "running" || data == "finished") {
                $('#run').html("<b>Running</b>");
                log("Connected...");
                getOutputInterval = setInterval(getOutput, 500);
                clearInterval(checkForRunInterval);
            }
            else if(data == "0"){
                clearInterval(getOutputInterval);
                $('#stop').html("<b>Stopped</b>");
                $('#run').html("Run");
            }
        }).fail(function() {
            log('Lost connection to backend.');
        });
    }

    function getOutput(){
        $.get('output.php',function(response){
                $('#response').html(response);
                if(!mouseIsOver)
                    $('#bottom')[0].scrollIntoView();
        });
        $.post('app.php', {action: 'percent'}, function(data){
            if(data == "nopython") {
                clearInterval(getOutputInterval);
                $('#stop').html("<b>Stopped</b>");
                $('#run').html("Run");
                log('Unable to initiate python script.');
            }
            else if(data == "done"){
                clearInterval(getOutputInterval);
                $('#stop').html("<b>Stopped</b>");
                $('#run').html("Run");
            }
            else
                $('#run').html(data + '%');
        }).fail(function() {
            log('Lost connection to backend.');
        });

    }



    $(document).ready(function(){

        $('#run').click(function(){
            $('#run').html("<b>Running</b>");
            $('#stop').html("Stop");
            clearLog();
            log("Sending Start request to server");
            $.post('app.php', {action: 'start'}, function(data){
                if(data == 'nofile'){
                    log("<b>ERROR</b> The python file \"main.py\" was not detected. Check configuration");
                }
                else if(data == 'started'){
                    $('#run').html("<b>Running</b>");
                    log("Request received, waiting for connection");
                    checkForRunInterval = setInterval(checkForRun, 250);

                    $('#stop').html("Stop");
                }
            }).fail(function() {
                log('Lost connection to backend.');
            });
        });

        $('#stop').click(function(){
            clearInterval(getOutputInterval);
            clearInterval(checkForRunInterval);
            $('#stop').html("Stopping");
            $.post('app.php', {action: 'stop'}, function(data) {
                if (data == 'stopped') {
                    $('#run').html("Run");
                    $('#stop').html("<b>Stopped</b>");
                    log("<b>Stopped</b>");
                }
            }).fail(function() {
                log('Lost connection to backend.');
            });
        });
    });

</script>

</body>

</html>