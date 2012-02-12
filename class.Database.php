<?php

interface DatabaseMethods
{
    public static function getInstance();                             // return the active database instance (Singleton practice)
    public function query( $query_string, array $parameters = null ); // Public query method supporting prepared statements  
};

class Database 
{
    private static $m_Instance;
    public 
        $dbHandle;
    
    private function __construct( $database_settings )
    {
        if ($database_settings)
        {
            try
            {
                $this->dbHandle = new PDO("mysql:host={$database_settings['host']};dbname={$database_settings['name']}", $database_settings['user'], $database_settings['password']);
            }
            catch (PDOException $e)
            {
                trigger_error('Cannot connect to the database.', E_USER_ERROR);   
            }
        }
    }
    
    public static function getInstance()
    {
        if (!self::$m_Instance)
        {
            $args = func_get_args();
            self::$m_Instance = new Database( $args[0] );
        }
        return self::$m_Instance;
    }
    
   /**
     * Database::query( string $query_string, array $parameters )
     * Example: $db->query('SELECT id FROM myTable WHERE myName = :myName AND myAge <= :myAge', array(
     *      ':myName' => 'Neil',
     *      ':myAge'  => 21
     * ));
     * @return void
     */
     
    public function query( $query_string, array $parameters = null )
    {
        $return = array(
            'execution_time' => microtime( TRUE ),
            'result'         => NULL,
            'num_rows'       => 0
        );
        
        $stmt = $this->dbHandle->prepare( $query_string );
        $param_counter = 1;
        
        if (isset($parameters))
        {
            /*
            foreach ($parameters as $alias => $value)
            {
                $stmt->bindParam($param_counter, $value, (is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
                $param_counter++;
            }
            */
            
            $values = array_values ( $parameters );
            if (!$stmt->execute( $values ))
            {
                trigger_error( 'Error executing query.' );
            }
        }
        else
        {
            if (!$stmt->execute())
            {
                trigger_error( 'Error executing query.' );
            }
        }
        
        $return['affected_rows'] = $stmt->rowCount();
        $result = $stmt->fetchAll( PDO::FETCH_ASSOC );
        
        if ($result)
        {
            $return['num_rows'] = count($result);
        }
        
        
        if (0 < $return['num_rows'])
        {
            if (1 == $return['num_rows'])
            {
                foreach ($result[0] as $row => $value)
                {
                    if ($row == 'created')
                        $result[0]['strCreated'] = date(DATETIME_FORMAT, $value);
                    if ($row == 'modified')
                        $result[0]['strModified'] = date(DATETIME_FORMAT, $value); 
                }
                $return['result'] = (object)$result[0];
            }
            else
            {
                foreach ($result as $offset => $data)
                {
                    foreach ($data as $key => $value)
                    {
                        if ($key == 'created')
                            $data['strCreated'] = date(DATETIME_FORMAT, $value);
                        if ($key == 'modified')
                            $data['strModified'] = date(DATETIME_FORMAT, $value);
                    }
                    $result[$offset] = (object)$data;
                }
                $return['result'] = $result;
            }
        }
        
        $return['execution_time'] = (microtime( TRUE ) - $return['execution_time']);
        $stmt->closeCursor();
        
        return (object)$return;
    }
    
    /*
    // Deprecated MySQLi Wrapper
    public function query2( $query_string, array $parameters = null )
    {
        if ($this->dbHandle->connect_error)
            trigger_error('Cannot perform query because the database connection is no longer active.', E_USER_ERROR);
            
        $return = array(
            'execution_time' => microtime( TRUE ),
            'result'       => NULL
        );
        
        $stmt = $this->dbHandle->prepare($query_string);
        
        if (!$stmt) 
        {
            trigger_error($this->dbHandle->error);
            return FALSE;
        }
        
        if (NULL == $parameters)
        {
            if (!$stmt->execute() or $stmt->errno)
            {
                trigger_error($stmt->error);
            }
        }
        else
        {
            $placeholders = array();
            $values       = array();
            $types = '';
            
            foreach ($parameters as $placeholder => $value)
            {
                $types .= is_numeric($value) ? 'i' : 's';
                $$placeholder = $value;
                $values[] = $value;
                $placeholders[] = '$'.$placeholder;
            }
            $evalCode = '$stmt->bind_param($types, ' . implode(', ', $placeholders) . ');';
            eval($evalCode);
            //$stmt->bind_param($type, $placeholders);
            //call_user_func_array(array($stmt, 'bind_param'), array($params, $values));
            if (!$stmt->execute() or $stmt->errno)
            {
                trigger_error($stmt->error);
            }
        }
        
        $result = $stmt->get_result();
        $return['affected_rows'] = $stmt->affected_rows;
        
        if ($result)
        {
            $return['num_rows'] = $result->num_rows;
        }
        else
        {
            // Insert query;
            // falsify result record and set num_rows to 0 to return
            $return['num_rows'] = $stmt->num_rows;
            $result = new stdClass;
            $result->num_rows = 0;
        }
        
        if ($result->num_rows > 0)
        {
            $rows   = $result->fetch_all( MYSQLI_ASSOC );
            if ($result->num_rows == 1)
            {
                foreach ($rows[0] as $key => $val)
                {
                    if (in_array($key, array('created', 'modified')))
                    {
                        $rows[0]['str'.ucwords($key)] = date(DATETIME_FORMAT, $val);
                    }
                }
                $return['result'] = (object)$rows[0];
            }
            else
            {
                foreach ($rows as $offset => $data)
                {
                    foreach ($data as $key => $val)
                    {
                        if (in_array($key, array('created', 'modified')))
                        {
                            $data['str'.ucwords($key)] = date(DATETIME_FORMAT, $val);
                        }
                    }
                    $rows[$offset] = (object)$data;
                }
                $return['result'] = $rows;
            }
        }
        
        $stmt->free_result();
        $stmt->close();
        
        $return['execution_time'] = (microtime( TRUE ) - $return['execution_time']);
        
        return (object)$return;
    }
    */
}