<?php

/**
 * Kontrola behu aplikce. Vytvori spojeni s databazi.
 *
 */

class app
{
    // promenna tridy
    private $data = null;
    
    // pripojeni k db - pomocny objekt
    private $db = null;

    /**
     * Konstruktor.
     */
    public function app()
    {
        $this->db = new db();
    }

    public function GetConnection()
    {
    	return $this->db->GetConnection();
    }
    
    /**
     * Pripojit k databazi.
     */
    public function Connect()
    {
    	$this->db->Connect();
    }
}

?>