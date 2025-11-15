<?php

class DbConnector
{
    private $link;
    private $theQuery;

    public function __construct()
    {
        $host = 'localhost';
        $db = 'capstone';
        $user = 'root';
        $pass = '';

        $this->link = mysqli_connect($host, $user, $pass, $db);

        if (!$this->link) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    public function __destruct()
    {
        mysqli_close($this->link);
    }

    // Execute a database query
    public function query($query): bool|mysqli_result
    {
        $this->theQuery = $query;
        return mysqli_query($this->link, $query);
    }

    // Get array of query results
    public function fetchArray($result)
    {
        return mysqli_fetch_array($result);
    }

    // Optional: manual close
    public function close()
    {
        mysqli_close($this->link);
    }
}
