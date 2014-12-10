<?php
class Concert extends db{
    
    private $table_name = "koncert";
  
    public function Concert($connection){
        $this->connection = $connection;	
				}
  
    /**
     * Vlozi koncert
     * @param $form data z formulare
     * @param $id_user id uzivatele
     */
    public function insertConcert($form, $id_user){
        $form['uzivatel_id_uzivatel'] = $id_user;

        $this->DBInsert($this->table_name, $form);
    }
    
    /**
     * Update koncertu
     * @param $form data z formulare
     * @param $where pole podminek
     */
    public function updateConcert($form, $where){
        $this->DBUpdate($this->table_name, $form, $where);
    }
    
    /**
     * Smaze koncert
     * @param $id id koncertu
     */
    public function deleteConcert($id){
        $table_name = "kapela_koncert";
        $where_array = array(0 => array("column" => "koncert_id_koncert", "value" => intval($id), "symbol" => "="));
        $this->DBDelete($table_name, $where_array);
        
        $where_array = array(0 => array("column" => "id_koncert", "value" => intval($id), "symbol" => "="));
        $this->DBDelete($this->table_name, $where_array);
    }
    
    /**
     * Odstrani kapelu ze seznamu vystupujicich koncert
     * @param $id_band id kapely
     * @param $id_concert id koncertu
     */
    public function deleteBand($id_band, $id_concert){
        $table_name = "kapela_koncert";
        $where_array = array(0 => array("column" => "koncert_id_koncert", "value" => intval($id_concert), "symbol" => "="),
                            1 => array("column" => "kapela_id_kapela", "value" => intval($id_band), "symbol" => "="));
        $this->DBDelete($table_name, $where_array);
    }
    
    
    /**
     * Vrati koncert
     * @param $id_concert id koncertu
     * @return koncert
     */
    public function getConcert($id_concert){
        $select_columns_string = "*";
        $where_array = array(0 => array("column" => "id_koncert", "value" => intval($id_concert), "symbol" => "="));
        $concert = $this->DBSelectOne($this->table_name, $select_columns_string, $where_array, "");
       
        return $concert;
    }
    
    /**
     * Vrati pole koncertu
     * @param $limit limit
     * @param $where pole podminek
     * @param $order razeni
     * @return pole koncertu
     */
    public function getConcerts($limit, $where, $order){
        $select_columns_string = "*"; 
        $limit_string = "";
        
        if($limit != 0){
            $limit_string = "LIMIT ".$limit;;
        }
       
        $where_array = $where;
        $order_by_array = array(0 => array("column" => "datum", "sort" => $order));
	
        // pole zaznamu
        $concert = $this->DBSelectAll($this->table_name, $select_columns_string, $where_array, $limit_string, $order_by_array);

        return $concert;
    }
    
    /**
     * Prida kapelu an seznam vystupujicich koncertu
     * @param $id_concert id koncertu
     * @param $id_band id kapely
     */
    public function addBand($id_concert, $id_band){
        $table_name = "kapela_koncert";
        $item = array("kapela_id_kapela" => $id_band, "koncert_id_koncert" => $id_concert);
        $this->DBInsert($table_name, $item);
    }
    
    /**
     * Vrati pole kapel vystupujicich na koncertu
     * @param $limit limit
     * @param $where pole podminek
     * @param $order razeni
     * @return pole kapel
     */
    public function getBands($limit, $where, $order){
        $select_columns_string = "*";
        $table_name = "kapela_koncert";
        
        if($limit != ""){
            $limit_string = "LIMIT ".$limit;;
        }
        else{
            $limit_string = "";
        }
        
        $where_array = $where;
        $order_by_array = array(0 => array("column" => "kapela_id_kapela", "sort" => $order));
	
        // pole zaznamu
        $bands = $this->DBSelectAll($table_name, $select_columns_string, $where_array, $limit_string, $order_by_array);
        
        return $bands;
    }
}

?>