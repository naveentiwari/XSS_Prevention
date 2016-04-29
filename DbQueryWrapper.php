<?php

require('htmlencode.php');

class DBQueryWrapper {
    private $db;
    private $isInitialized;
    private $lasterror;
    private $result;
    private $result_ndx;
    private $encoder;

    function reSetExecutionValues () {
        $this->_result = array();
        $this->_result_ndx = 0;
    }

    public function __construct ($dbhost, $dbport, $dbname, $dbuser, $dbpasswd) {
        // set isInitialized to false
        // this will prevent functions getting called in case of some error
        $this->_isInitialized = false;
        $this->reSetExecutionValues ();

        try {
            $this->_db     = new PDO("mysql:host=".$dbhost.";port=".$dbport.";dbname=".$dbname, $dbuser, $dbpasswd);
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // initialize the encoder
            $this->_encoder = new HTMLEncode(true);
            $this->SetEncoderInfo ();

            $this->_isInitialized = true;
            $this->_lasterror = '';
        } catch (Exception $e) {
            $this->_lasterror = $e->getMessage();
            return false;
        }
    }

    private function SetEncoderInfo () {
        $this->_encoder->addPreMapChar('"', '\"');
    }

    public function encodeDBResult ($result) {
        if (gettype($result) == 'string') {
            return $this->_encoder->encodePreChars ($result);
        }

        if (gettype($result) == 'array') {
            foreach ($result as $key => $value) {
                $result[$key] = $this->encodeDBResult ($value);
            }

            return $result;
        }

        return $result;
    }

    public function executeQuery ($query)  {

        // check if the class has been properly initialized
        // if not, return false
        if ($this->_isInitialized == false)
            return false;

        $counter = 1;
        $paramarry = array();

        // reinitialize the result array
        $this->reSetExecutionValues();

        // walk the entire post request and replace the entire user data with
        // ':param<counter>' and fill the user value and the position holder
        // in an array
        foreach ($_POST as $key => $value) {
            $retval = strstr($query, $value);

            // check if the return value is set and it is not boolean
            // not boolean means that needle was found in haystack
            if (isset($retval) && gettype($retval) != 'boolean') {
                $paramname = ":param".$counter;
                $paramarry[$paramname] = $value;
                $pattern = '/=\s*' . $value . '/';
                $query = preg_replace($pattern, '=' . $paramname, $query);
                $counter++;
            }
        }

        try {
            // prepare the query for the database
            $stmt = $this->_db->Prepare($query);
            // execute the query
            $stmt->execute($paramarry);

            $this->_result = $stmt->fetchAll();
            $this->_lasterror = '';

            return true;
        } catch (PDOException $e) {
            $this->_lasterror = $e->getMessage ();
            return false;
        }

        return false;
    }

    public function getLastError () {
        return $this->_lasterror;
    }

    public function fetchArray () {
        if ($this->_isInitialized == false ||  $this->_lasterror != '')
            return false;

        if (isset($this->_result[$this->_result_ndx])) {
            $this->_result_ndx++;
            return $this->encodeDBResult ($this->_result[$this->_result_ndx - 1]);
        }

        return false;
    }
}
?>
