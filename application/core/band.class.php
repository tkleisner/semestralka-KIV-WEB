<?php
class Band extends db{
    
    private $table_name = "kapela";
  
    public function Band($connection){
        $this->connection = $connection;	
	   }
    
    /**
     * Vrati kapelu podle id nebo nazvu
     * @param $id_band id kapely
     * @param $name nazev kapely
     * @return kapela
     */
    public function getBand($id_band = 0, $name = ""){
        $select_columns_string = "*";
        if($id_band != 0){
            $where_array = array(0 => array("column" => "id_kapela", "value" => intval($id_band), "symbol" => "="));
        }
        else{
            $where_array = array(0 => array("column" => "nazev", "value" => $name, "symbol" => "="));
        } 
        return $this->DBSelectOne($this->table_name, $select_columns_string, $where_array, "");
    }
    
    /**
     * Funkce pro vlozeni kapely
     * @param $form vyplnena data z formulare
     * @param $id_user id uzivatele, ktery kapelu vytvari
     */
    public function insertBand($form, $id_user){
        //pridat do tabulky kapel
        $this->DBInsert($this->table_name, $form);
        $band = $this->getBand(0, $form['nazev']);
        
        //pridat do tabulky uzivatel_kapela
        $item = array("uzivatel_id_uzivatel" => $id_user, "kapela_id_kapela" => $band['id_kapela']);
        $this->DBInsert("uzivatel_kapela", $item);
    }
    
    /**
     * Zmena udaju kapely
     * @param $form data prijata z formulare
     * @param $where pole podminek
     */
    public function updateBand($form, $where){
        $this->DBUpdate($this->table_name, $form, $where);
    }
    
    /**
     * Smazani kapely
     * @param $id id kapely
     */
    public function deleteBand($id){
        $table_name1 = "uzivatel_kapela";
        $table_name2 = "kapela_koncert";
        
        $where_array = array(0 => array("column" => "kapela_id_kapela", "value" => intval($id), "symbol" => "="));
        
        $this->DBDelete($table_name1, $where_array);
        $this->DBDelete($table_name2, $where_array);
        
        $where_array = array(0 => array("column" => "id_kapela", "value" => intval($id), "symbol" => "="));
        
        $this->DBDelete($this->table_name, $where_array);
    }
    
    /**
     * Pridani uzivatele ke kapele
     * @param $id_band id kapely
     * @param $id_user id uzivatele
     */
    public function addUser($id_band, $id_user){
        $table_name = "uzivatel_kapela";
        $item = array("kapela_id_kapela" => $id_band, "uzivatel_id_uzivatel" => $id_user);
        $this->DBInsert($table_name, $item);
    }
    
    /**
     * Odstraneni uzivatele ze seznamu clenu kapely
     * @param $id_band id kapely
     * @param $id_user id uzivatele
     */
    public function deleteUser($id_band, $id_user){
        $table_name = "uzivatel_kapela";
        $where_array = array(0 => array("column" => "uzivatel_id_uzivatel", "value" => intval($id_user), "symbol" => "="),
                            1 => array("column" => "kapela_id_kapela", "value" => intval($id_band), "symbol" => "="));
        $this->DBDelete($table_name, $where_array);
    }
    
    /**
     * Vrati boolean hodnotu podle toho, je- li uzivatel clenem kapely
     * @param $id_band id kapely
     * @param $id_user id uzivatele
     * @return true pokud je v kapele, false pokud neni v kapele
     */
    public function isInBand($id_user, $id_band){
        $select_columns_string = "*";
        $table_name = "uzivatel_kapela";
        $where_array = array(0 => array("column" => "uzivatel_id_uzivatel", "value" => intval($id_user), "symbol" => "="),
                            1 => array("column" => "kapela_id_kapela", "value" => intval($id_band), "symbol" => "="));
        $result = $this->DBSelectOne($table_name, $select_columns_string, $where_array, "");
    
        if($result == null)
            return false;
        else
            return true;
    }
    
    /**
     * Vrati pole koncertu, na kterych kapela vystupuje
     * @param $limit limit
     * @param $where pole podminek
     * @param $order razeni podle id (ASC/DESC)
     * @return pole koncertu kapely
     */
    public function getConcerts($limit, $where, $order){
        $select_columns_string = "*";
        $table_name = "kapela_koncert";
        
        if($limit != ""){
            $limit_string = "LIMIT ".$limit;;
        }
        else{
            $limit_string = "";
        }
        
        $order_by_array = array(0 => array("column" => "koncert_id_koncert", "sort" => $order));
	
        // pole zaznamu
        $concerts = $this->DBSelectAll($table_name, $select_columns_string, $where, $limit_string, $order_by_array);
        
        return $concerts;
    }
    
    /**
     * Vrati pole uzivatelu, kteri jsou cleny kapely
     * @param $id_band id kapely
     * @param $limit limit
     * @param $order razeni podle id uzivatele (ASC/DESC)
     * @return pole clenu kapely
     */
    public function getUserIds($id_band, $limit, $order){
        $select_columns_string = "*";
        $table_name = "uzivatel_kapela";
        
        if($limit != ""){
            $limit_string = "LIMIT ".$limit;;
        }
        else{
            $limit_string = "";
        }
        
        $where_array = array(0 => array("column" => "kapela_id_kapela", "symbol" => "=", "value" => intval($id_band)));
        $order_by_array = array(0 => array("column" => "uzivatel_id_uzivatel", "sort" => $order));
	
        // pole zaznamu
        $users = $this->DBSelectAll($table_name, $select_columns_string, $where_array, $limit_string, $order_by_array);
        
        return $users;
    }
  
    /**
    * Vrati pole kapel
    * @param $limit pocet
    * @param $order razeni string ASC/DESC     
    * @return pole kapel
    */     
    public function getBands($limit, $where, $order){
        $select_columns_string = "*"; 
		      $where_array = $where;
        $limit_string = "";
       
        if($limit != 0){
            $limit_string = "LIMIT ".$limit;;
        }
        
        $order_by_array = array(0 => array("column" => "id_kapela", "sort" => $order));
	
		      // pole zaznamu
		      $bands = $this->DBSelectAll($this->table_name, $select_columns_string, $where_array, $limit_string, $order_by_array);

		      return $bands;
    }
}

?>