     <?php
    /**
     * Implements the Repository Pattern to deal with sum of Active Records
     *
     * @version    4.0
     * @package    database
     * @author     Pablo Dall'Oglio
     * @author     Marco Driemeyer <ma...@plenatech.com.br>
     * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
     * @license    http://www.adianti.com.br/framework-license
     */
    class TRepositorySum 
    {
        private $class; // Active Record class to be manipulated
        private $criteria; // buffered criteria to use with fluent interfaces
        
        
        /**
         * Class Constructor
         * @param $class = Active Record class name
         */
        public function __construct($class)
        {
            if (class_exists($class))
            {
                if (is_subclass_of($class, 'TRecord'))
                {
                    $this->class = $class;
                    $this->criteria = new TCriteria;
                }
                else
                {
                    throw new Exception(AdiantiCoreTranslator::translate('The class ^1 was not accepted as argument. The class informed as parameter must be subclass of ^2.', $class, 'TRecord'));
                }
            }
            else
            {
                throw new Exception(AdiantiCoreTranslator::translate('The class ^1 was not found. Check the class name or the file name. They must match', $class));
            }
        }
        
        
        /**
         * Returns the name of database entity
         * @return A String containing the name of the entity
         */
        protected function getEntity()
        {
            return constant($this->class.'::TABLENAME');
        }
        /**
         * Return the sum of columns of objects that satisfy a given criteria
         * @param $criteria  An TCriteria object, specifiyng the filters
         * @param $columns   An indexed array with the name and the column to sum
         * @return           An stdClass with the named property storing the sum of values
         */
        public function sum(TCriteria $criteria = NULL, array $columns)
        {
            if (!$criteria)
            {
                $criteria = isset($this->criteria) ? $this->criteria : new TCriteria;
            }
            // creates a SELECT statement
            $sql = new TSqlSelect;
            
            // Interact with the array and add the columns to sum
            foreach ($columns as $key => $column)
            {
                $sql->addColumn("round(sum($column),2) as $key");
            }
            
            $sql->setEntity($this->getEntity());
            // assign the criteria to the SELECT statement
            $sql->setCriteria($criteria);
            
            // get the connection of the active transaction
            if ($conn = TTransaction::get())
            {
                // register the operation in the LOG file
                TTransaction::log($sql->getInstruction());
                
                $dbinfo = TTransaction::getDatabaseInfo(); // get dbinfo
                if (isset($dbinfo['prep']) AND $dbinfo['prep'] == '1') // prepared ON
                {
                    $result = $conn-> prepare ( $sql->getInstruction( TRUE ) , array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    $result-> execute ( $criteria->getPreparedVars() );
                }
                else
                {
                    // executes the SELECT statement
                    $result= $conn-> query($sql->getInstruction());
                }
                
                if ($result)
                {
                    $row = $result->fetch();
                 
                    // Initiate the stdClass and interact with the return of the sum 
                    $stdClass = new stdClass();
                    foreach ($columns as $key => $column)
                    {
                        if ($row["$key"])
                            $stdClass->$key = $row["$key"];
                        else
                            $stdClass->$key = 0;
                    }
                    
                    return $stdClass;
                }
            }
            else
            {
                // if there's no active transaction opened
                throw new Exception(AdiantiCoreTranslator::translate('No active transactions') . ': ' . __METHOD__ .' '. $this->getEntity());
            }
        }
        
        public function get(TCriteria $criteria = NULL, $callObjectLoad = TRUE)
        {
            return $this->load($criteria, $callObjectLoad);
        }
    }
    ?> 

