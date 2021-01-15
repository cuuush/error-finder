<?php


include_once "config.php";

//die(var_dump(get_defined_vars()));

class connection
{
    protected $conn;

    /**
     * connection constructor.
     */
    public function __construct()
    {
        global $dbHost, $dbUsername, $dbPassword, $dbName;
        $conn = $this->conn;
        $this->conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);


        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }


    public function queryEndpoints($query)
    {
        $query = $this->conn->real_escape_string($query);

        $result = $this->conn->query('SELECT ' . $query . ' FROM endpoints');

        if (!$result) //if there is nothing
            return false;

        $array = [];

        while ($rowArray = $result->fetch_assoc()) {
            array_push($array, $rowArray[$query]);
        }

        return $array;
    }

    /**
     * gets a certain element from an endpoint based off of its name
     * @param $name the name of the endpoint
     * @param $query the column(s) to return from the database
     * @return bool the array with the query result
     */
    public function queryEndpointFromName($name, $query)
    {
        $query = $this->conn->real_escape_string($query);

        $result = $this->conn->query('SELECT ' . $query . ' FROM endpoints where devicename = "' . $name . '"');

        if (!$result) //if there is nothing
            return false;

        $row = $result->fetch_assoc();

        return $row[$query];
    }

    /**
     * updates an endpoint's ip address given its name
     * @param $name the name of the endpoint
     * @param $ip the new ip
     */
    public function updateEndpointIPFromName($name, $ip)
    {
        $stmt = $this->conn->prepare('UPDATE endpoints SET ip = ? WHERE devicename = (?)');
        $stmt->bind_param('ss', $ip, $name);
        $stmt->execute();
    }


    /**
     * adds an endpoint to the database
     * @param $ip the endpoint id
     * @param $devicename the nake of the endpoint
     * @param $devicetype the device type, such as "DX80"
     */
    public function addEndpoint($ip, $devicename, $devicetype)
    {
        $ip = $this->conn->real_escape_string($ip);
        $devicename = $this->conn->real_escape_string($devicename);
        $devicetype = $this->conn->real_escape_string($devicetype);

        $stmt = $this->conn->prepare('INSERT INTO endpoints (ip, devicename, devicetype) VALUES (?,?,?)');
        $stmt->bind_param('sss', $ip, $devicename, $devicetype);
        $stmt->execute();
    }

    /**
     * truncates all endpoints.
     */
    public function truncateEndpoints()
    {
        $stmt = $this->conn->prepare('TRUNCATE TABLE endpoints;');
        $stmt->execute();
    }


    /**
     * returns the data associated with the most recent job
     * @return array
     */
    public function errors_getMostRecentJob()
    {
        $stmt = $this->conn->prepare('SELECT * FROM errors ORDER BY job_id DESC LIMIT 1');
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row;
    }


    /**
     * Gets the most recent job ID from the job list
     * @return mixed string value of most recent job id
     */
    public function errors_getMostRecentJobID()
    {
        $stmt = $this->conn->prepare('SELECT job_id FROM errors ORDER BY job_id DESC LIMIT 1');
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['job_id'];
    }

    /**
     * kills a running job
     * @param $jobID the job id to kill
     */
    public function errors_killJob($jobID)
    {
        $stmt = $this->conn->prepare('UPDATE errors SET killed = 1 WHERE job_id = ?');
        $stmt->bind_param('i', $jobID);
        $stmt->execute();
    }

    /**
     * kills a running job
     * @param $jobID the job id to kill
     */
    public function errors_getJob($jobID)
    {
        $stmt = $this->conn->prepare('SELECT * FROM errors WHERE job_id = ?');
        $stmt->bind_param('i', $jobID);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row;
    }

    /**
     * returns the output of the last or currently running job
     * @return array the output of the job
     */
    public function errors_getOutput()
    {
        $stmt = $this->conn->prepare('SELECT * FROM errors_output');
        $stmt->execute();

        $result = $stmt->get_result();

        $array = [];

        while ($rowArray = $result->fetch_assoc()) {
            array_push($array, $rowArray);
        }

        return $array;
    }

    /**
     * returns all errors ordered by job id
     * @return array all errors
     */
    public function errors_getAllErrors()
    {
        $stmt = $this->conn->prepare('SELECT * FROM errors_errorlist ORDER BY job_id DESC');
        $stmt->execute();

        $result = $stmt->get_result();

        $array = [];

        while ($rowArray = $result->fetch_assoc()) {
            array_push($array, $rowArray);
        }

        return $array;
    }

    /**
     * returns all errors from the most recent job
     * @return array all errors from the most recent job
     */
    public function errors_getAllErrorsFromMostRecentJob()
    {
        $query = 'select errors_errorlist.endpoint_id, endpoints.ip `ip`, endpoints.devicename `name`, `date`, `level`, `reference`, `type`, `text` 
                  from errors_errorlist 
                  INNER JOIN 
	                endpoints ON errors_errorlist.endpoint_id = endpoints.endpoint_id
                  WHERE job_id = (select job_id from errors ORDER BY job_id DESC LIMIT 1)';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->get_result();

        $array = [];

        while ($rowArray = $result->fetch_assoc()) {
            array_push($array, $rowArray);
        }

        return $array;
    }

    /**
     * gets all errors from a certain endpoint id
     * @param $endpointID the endpoint id
     * @return array all errors from the given endpoint id
     */
    public function errors_getAllErrorsFromEndpoint($endpointID)
    {
        $stmt = $this->conn->prepare('SELECT * FROM errors_errorlist WHERE endpoint_id = ? ORDER BY job_id DESC');
        $stmt->bind_param('i', $endpointID);
        $stmt->execute();

        $result = $stmt->get_result();

        $array = [];

        while ($rowArray = $result->fetch_assoc()) {
            array_push($array, $rowArray);
        }

        return $array;
    }

    /**
     * Gets errors that are unique for that job ID. If no job Id is provided, most recent job is selected instead
     * @param int $jobID the Job id to look up
     * @return array an array of errors
     */
    public function errors_getNewErrorsFromJobID($jobID = -1)
    {

        if ($jobID == -1) {
            // i love sql so much! its actually super simple. i thought join was black magic but it took me a day to understand it. It Just Works!
            $stmt = $this->conn->prepare(
                'SELECT endp.ip, endp.devicename, el.level, el.date, el.reference, el.type, el.text 
FROM errors_newerrors ne 
JOIN errors_errorlist el ON ne.error_id = el.error_id
JOIN endpoints endp ON el.endpoint_id = endp.endpoint_id
WHERE ne.job_id = (SELECT job_id FROM errors ORDER BY job_id DESC LIMIT 1)
');
        } else {
            $stmt = $this->conn->prepare('SELECT endp.ip, endp.devicename, el.level, el.date, el.reference, el.type, el.text FROM errors_newerrors ne 
JOIN errors_errorlist el ON ne.error_id = el.error_id
JOIN endpoints endp ON el.endpoint_id = endp.endpoint_id
WHERE ne.job_id = ?');
            $stmt->bind_param('i', $jobID);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $array = [];

        while ($rowArray = $result->fetch_assoc()) {
            array_push($array, $rowArray);
        }

        return $array;
    }

}