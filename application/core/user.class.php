<?php
class User extends db{
    
    private $table_name = "uzivatel";
    private $where_array = array();
  
    /**
     * Konstruktor
     */
    public function User($connection){
	       $this->connection = $connection;	
	   }
    
    /**
     * Vlozeni uzivatele
     * @param $name jmeno
     * @param $email email
     * @param $pwd heslo
     */
    public function insertUser($name, $email, $pwd){
        $item = array("jmeno" => $name, "email" => $email, "heslo" =>  $pwd);
        $this->DBInsert($this->table_name, $item);
    }
    
    /**
     * Update uzivatele
     * @param $form data z formulare k ulozeni
     * @where_array pole podminek
     */
    public function updateUser($form, $where_array){
        $this->DBUpdate($this->table_name, $form, $where_array);
    }
    
    /**
     * Vrati pole kapel, kterych je uzivatel clenem.
     * @param $limit limi
     * @param $where pole podminek
     * @param $order razeni podle id uzivatele
     */
    public function getBands($limit, $where, $order){
        $select_columns_string = "*";
        $table_name = "uzivatel_kapela";
        
        if($limit != ""){
            $limit_string = "LIMIT ".$limit;;
        }
        else{
            $limit_string = "";
        }
        
        $order_by_array = array(0 => array("column" => "uzivatel_id_uzivatel", "sort" => $order));
	
        // pole zaznamu
        $bands = $this->DBSelectAll($table_name, $select_columns_string, $where, $limit_string, $order_by_array);
        
        return $bands;
    }
    
    
    /**
     * Vrati uzivatle podle id nebo jmena
     * @param $id_user id uzivatele
     * @param $name jmeno
     */
    public function getUser($id_user = 0, $name = ""){
        $select_columns_string = "*";
        
        if($id_user != 0){
            $where_array = array(0 => array("column" => "id_uzivatel", "value" => intval($id_user), "symbol" => "="));
        }
        else{
            $where_array = array(0 => array("column" => "jmeno", "value" => $name, "symbol" => "="));
        } 
        
        return $this->DBSelectOne($this->table_name, $select_columns_string, $where_array, "");
    }
  
    /**
     * Vrati pole uzivatelu
     * @param $limit - pocet
     * @param $order - razeni string ASC/DESC     
     */     
    public function getUsers($limit, $where, $order){
		      $select_columns_string = "*"; 
		      $limit_string = "";
        
        if($limit != 0){
            $limit_string = "LIMIT ".$limit;;
        }
        
		      $order_by_array = array(0 => array("column" => "id_uzivatel", "sort" => $order));
	
		      // pole zaznamu
		      $predmety = $this->DBSelectAll($this->table_name, $select_columns_string, $where, $limit_string, $order_by_array);

		      return $predmety;
    }
}

?>