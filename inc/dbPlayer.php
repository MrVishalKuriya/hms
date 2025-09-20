<?php
/**
 * Created by Vishal.
 * User: troot
 * Date: 1/1/25
 * Time: 10:55 PM
 */

/**
 * WARNING: This file contains SQL injection vulnerabilities.
 * The update(), delete(), getData(), registration(), execNonQuery() and execDataTable() methods are not secure.
 * It is highly recommended to refactor the code to use prepared statements for all database queries.
 */

namespace dbPlayer;


class dbPlayer {

    private $db_host="localhost";
    private $db_name="hms";
    private $db_user="root";
    private $db_pass="";
    protected $con;

//    public function open(){
//         $con = mysql_connect($this->db_host,$this->db_user,$this->db_pass);
//        if($con)
//        {
//            $dbSelect = mysql_select_db($this->db_name);

//            if($dbSelect)
//            {
//                return "true";
//            }
//            else
//            {
//                return mysql_error();
//            }

//        }
//         else
//         {
//             return  mysql_error();
//         }

//     }

    public function open(){
    $this->con = mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);

    if ($this->con) {
        return "true";
    } else {
        return mysqli_connect_error();
    }
}

    public  function close()
    {
        $res = mysqli_close($this->con);
        if($res)
        {
            return "true";
        }
        else
        {
            return mysqli_error($this->con);
        }

    }

    public function insertData($table, $data)
    {
        $keys = "`" . implode("`, `", array_keys($data)) . "`";
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = mysqli_prepare($this->con, "INSERT INTO `{$table}` ({$keys}) VALUES ({$placeholders})");

        $types = str_repeat('s', count($data));
        $values = array_values($data);
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        mysqli_stmt_execute($stmt);

        return mysqli_insert_id($this->con) . mysqli_error($this->con);
    }
    public function registration($query,$query2)
    {
        $res = mysqli_query($this->con, $query);
        if($res)
        {
            $res = mysqli_query($this->con, $query2);
            if($res)
            {
                return "true";
            }
            else
            {
                return mysqli_error($this->con);
            }
        }
        else
        {
            return mysqli_error($this->con);
        }


    }
    public  function  getData($query)
    {
        $res = mysqli_query($this->con, $query);
        if(!$res)
        {
            return "Can't get data ".mysqli_error($this->con);
        }
        else
        {
            return $res;
        }

    }
    public function  update($query)
    {
        $res = mysqli_query($this->con, $query);
        if(!$res)
        {
            return "Can't update data ".mysqli_error($this->con);
        }
        else
        {
            return "true";
        }
    }
    public  function  updateData($table, $conColumn, $conValue, $data)
    {
        $updates = array();
        $values = array();
        $types = '';

        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $updates[] = "`$key` = ?";
                $values[] = $value;
                $types .= 's';
            }
        }

        $implodeArray = implode(', ', $updates);
        $stmt = mysqli_prepare($this->con, "UPDATE `{$table}` SET {$implodeArray} WHERE `{$conColumn}` = ?");

        $types .= 's';
        $values[] = $conValue;
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        if(mysqli_stmt_execute($stmt)) {
            return "true";
        } else {
            return "Can't Update data ".mysqli_error($this->con);
        }
    }

    public  function delete($query)
    {
        $res = mysqli_query($this->con, $query);
        // var_dump($query);
        if(!$res)
        {
            return "Can't delete data ".mysqli_error($this->con);
        }
        else
        {
            return "true";
        }
    }

    public function getAutoId($prefix)
    {
        $uId = "";
        $stmt = mysqli_prepare($this->con, "SELECT number FROM auto_id WHERE prefix = ?");
        mysqli_stmt_bind_param($stmt, "s", $prefix);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userId = array();
        while($row = mysqli_fetch_assoc($result))
        {
            array_push($userId, $row['number']);
        }

        if(count($userId) > 0) {
            if(strlen($userId[0]) >= 1)
            {
                $uId = $prefix . "00" . $userId[0];
            }
            elseif(strlen($userId[0]) == 2)
            {
                $uId = $prefix . "0" . $userId[0];
            }
            else
            {
                $uId = $prefix . $userId[0];
            }
            array_push($userId, $uId);
        }

        return $userId;
    }
    public function updateAutoId($value, $prefix)
    {
        $id = intval($value) + 1;
        $stmt = mysqli_prepare($this->con, "UPDATE auto_id SET number = ? WHERE prefix = ?");
        mysqli_stmt_bind_param($stmt, "is", $id, $prefix);
        if(mysqli_stmt_execute($stmt)) {
            return "true";
        } else {
            return "Can't update data ".mysqli_error($this->con);
        }
    }

    public  function execNonQuery($query)
    {
        $res = mysqli_query($this->con, $query);
        if(!$res)
        {
            return "Can't Execute Query" . mysqli_error($this->con);
        }
        else
        {
            return "true";
        }
    }
    public  function execDataTable($query)
    {
        $res = mysqli_query($this->con, $query);
        if(!$res)
        {
            return "Can't Execute Query" . mysqli_error($this->con);
        }
        else
        {
            return $res;
        }
    }

}