<?php
	
/**
 * specialni vypis
 */
function printr($val){
    echo "<hr><pre>";
    print_r($val);
    echo "</pre><hr>";
}

/**
 * Prevede format data z databaze na dd.mm.YYYY
 * @param $date datum ve tvaru YYYY-MM-DD
 * @return datum ve formatu dd.mm.YYYY
 */
function dateFormat($date){
    return strftime("%d. %m. %Y", strtotime($date));
}

/**
 * Prevede dataz celeho pole koncertu
 * @param $concerts pole koncertu
 * @return pole koncertu se zmenenym formatem data
 */
function dateFormatArray($concerts){
    for($i = 0; $i < sizeof($concerts); $i++){
        $concerts[$i]['datum'] = dateFormat($concerts[$i]['datum']);
    }
    
    return $concerts;
}

/**
 * Kontrola klicu pole pred ulozenim do databaze
 * @param $form data z formulare
 * @return true pokud je v poradku, false, pokud se neshoduji klice
 */
function checkValues($form){
    $values = array("id_kapela", "nazev", "styl", "zeme", "mesto", "text", "kapela_id_kapela", "koncert_id_koncert", "id_koncert", "datum", "cas", "misto", "vstup", "uzivatel_id_uzivatel", "id_uzivatel", "jmeno", "heslo", "email", "admin", "pwd", "hesloCheck");
    $keys = array_keys($form);
    
    for($i = 0; $i < sizeof($keys); $i++){
        if(!in_array(($keys[$i]), $values)){
            return false;
        }
    }
           
    return true;
}

/**
 * Prihlaseni uzivatele
 * @param $name jmeno
 * @param $password heslo
 * @param $userObj objekt User
 */
function signin($name, $password, $userObj){
    $user = $userObj->getUser(0, $name);
    if($user == NULL){
        echo "<script>alert(\"Uživatel $name nenalezen\");</script>";
    }
    else if($user['heslo'] == md5($password)){
        $_SESSION['auth'] = 1;
        $_SESSION['id'] = $user['id_uzivatel'];
        $_SESSION['name'] = $name;
        $_SESSION['admin'] = $user['admin'];
        echo "<script> window.history.back(); </script>";
    }
    else{
        echo "<script>alert(\"Špatné heslo\");</script>";
        echo "<script> window.history.back(); </script>";
    }
}

/**
 * Odhlaseni uzivatele
 */
function signout(){
    $_SESSION = array();
    unset($_SESSION['auth']);
    echo "<script> window.history.back(); </script>";
}

/**
 * Registrace, pridani uzivatele do DB
 * @param $name jmeno
 * @param $email email
 * @param $pwd heslo
 * @param $pwdr heslo znovu
 * @users objekt User
 */
function signup($name, $email, $pwd, $pwdr, $users){
    $check = $users->getUser(0, $name);
    if($check != NULL){
        echo "<script>alert(\" Uživatel s tímto jménem už existuje \");</script>";
        echo "<script> window.history.back(); </script>";
    }
    
    if($pwd != $pwdr){
        echo "<script>alert(\" Hesla se neshodují \");</script>";
        echo "<script> window.history.back(); </script>";
    }
    else{
        $pwd = md5($pwd);
        $users->insertUser($name, $email, $pwd);
        echo "<script> window.history.back(); </script>";
    }
}

/**
 * Zmena hesla uzivatele
 * @param $form data z formulare
 * @param $id_user id uzivatele
 * @param $userObj objekt User
 */
function updatePassword($form, $id_user, $userObj){
    $user = $userObj->getUser($id_user, "");
    
    unset($form['updatePass']);
    
    if(!checkValues($form)) return;
    
    if(md5($form['pwd']) == $user['heslo']){
        if($form['heslo'] == $form['hesloCheck']){
            $where_array = array(0 => array("column" => "id_uzivatel", "value" => intval($id_user), "symbol" => "="));
            
            unset($form['pwd']);
            unset($form['hesloCheck']);
            
            $form['heslo'] = md5($form['heslo']);
            
            $userObj->updateUser($form, $where_array);
            
            echo "<script>alert(\" Změněno \");</script>";
            echo "<script> window.history.back(); </script>";
        }
        else{
            echo "<script>alert(\" Hesla se neshodují \");</script>";
            echo "<script> window.history.back(); </script>"; 
        }
    }
    else{
        echo "<script>alert(\" Špatné heslo \");</script>";
        echo "<script> window.history.back(); </script>";  
    }
}

/**
 * Zmena udaju uzivatele
 * @param $form pole dat z formulare
 * @param $id_user id uzivatele
 * @param $userObj objekt User
 */
function updateUser($form, $id_user, $userObj){
    unset($form['updateUser']);
    
    if(!checkValues($form)) return;
    
    $where_array = array(0 => array("column" => "id_uzivatel", "value" => intval($id_user), "symbol" => "="));
    $userObj->updateUser($form, $where_array);
    
    echo "<script>alert(\" Změněno \");</script>";
    echo "<script> window.history.back(); </script>";
}

/**
 * Zmena udaju kapely
 * @param $form pole dat z formulare
 * @param bandObj objekt Band
 */
function updateBand($form, $bandObj){
    unset($form['confBand']);
    $id_band = $_POST['id'];
    unset($form['id']);
    
    if(!checkValues($form)) return;
    
    $where_array = array(0 => array("column" => "id_kapela", "value" => intval($id_band), "symbol" => "="));
    $bandObj->updateBand($form, $where_array);
    
    echo "<script>alert(\" Změněno \");</script>";
    echo "<script> window.history.back(); </script>";
}

/**
 * Pridat koncert
 * @param $form pole dat z formulare
 * @param $id_user id uzivatele
 * @param $concertObj objekt Concert
 */
function addConcert($form, $id_user, $concertObj){
    unset($form['addConcert']);
    
    if(!checkValues($form)) return;
    
    $concertObj->insertConcert($form, $id_user);
    
    echo "<script>alert(\" Přidáno \");</script>";
    echo "<script> window.history.back(); </script>";
}

/**
 * Zmena udaju koncertu
 * @param $form pole dat z formulare
 * @param $concertObj objekt Concert
 */
function updateConcert($form, $concertObj){
    unset($form['confConcert']);
    $id_concert = $form['id'];
    unset($form['id']);
    
    if(!checkValues($form)) return;
    
    $where_array = array(0 => array("column" => "id_koncert", "value" => intval($id_concert), "symbol" => "="));
    $concertObj->updateConcert($form, $where_array);
    
    echo "<script>alert(\" Změněno \");</script>";
    echo "<script> window.history.back(); </script>";
}

/**
 * Smazat koncert
 * @param $id id koncertu
 * @param $concertObj objekt Concert
 */
function deleteConcert($id, $concertObj){
    $concertObj->deleteConcert($id);
    
    echo "<script>alert(\" Smazáno \");</script>";
    echo "<script>window.location.replace('index.php'); </script>";
}

/**
 * Smazat kapelu
 * @param $id id kapely
 * @param $bandObj objekt Band
 */
function deleteBand($id, $bandObj){
    $bandObj->deleteBand($id);
    
    echo "<script>alert(\" Smazáno \");</script>";
    echo "<script>window.location.replace('index.php'); </script>";
}

/**
 * Odstranit kapelu z koncertu
 * @param $id_band id kapely
 * @param $id_concert id koncertu
 * @param $concertObj objekt Concert
 */
function deleteBandConcert($id_band, $id_concert, $concertObj){
    $concertObj->deleteBand($id_band, $id_concert);
    
    echo "<script>alert(\" Smazáno \");</script>";
    echo "<script> window.location.replace('index.php?concert=$id_concert'); </script>";
}

/**
 * Pridat kapelu
 * @param $form pole dat z formulare
 * @param $id_user id uzivatele, ktery zaklada kapelu
 * @param $bandObj objekt Band
 */
function addBand($form, $id_user, $bandObj){
    unset($form['addBand']);
    
    if(!checkValues($form)) return;
    
    $bandObj->insertBand($form, $id_user);
    echo "<script>alert(\" Přidáno \");</script>";
    echo "<script> window.history.back(); </script>";
}

/**
 * Vypis tabulky informaci o koncertu
 * @param $concert koncert (pole- zaznam z databaze)
 * @param $users objekt User
 * @return tabulka v HTML s udaji o koncertu
 */
function getConcertInfo($concert, $users){
    $user = $users->getUser($concert['uzivatel_id_uzivatel'], "");
    return 
    '<table class="table table-striped">
        <tr><td><strong>Datum</strong></td><td>'.$concert['datum'].'</td></tr>
        <tr><td><strong>Čas začátku</strong></td><td>'.$concert['cas'].'</td></tr>
        <tr><td><strong>Místo konání</strong></td><td>'.$concert['misto'].'</td></tr>
        <tr><td><strong>Vstupné</strong></td><td>'.$concert['vstup'].'</td></tr>
        <tr><td><strong>Správce koncertu</strong></td><td><a href="index.php?user='.$concert['uzivatel_id_uzivatel'].'">'.$user['jmeno'].'</a></td></tr>
    </table>';
}

/**
 * Vrati pole koncertu, zalozene danym uzivatelem
 * @param $id_user id uzivatele
 * @param $concertObj objekt Concert
 * @return pole koncertu
 */
function getUsersConcerts($id_user, $concertObj){
    $where_array = array(0 => array("column" => "uzivatel_id_uzivatel", "value" => intval($id_user), "symbol" => "="));
    $concerts = $concertObj->getConcerts("", $where_array, "ASC");
    
    return $concerts;
}

/**
 * Vrati kapely vystupujici na danem koncertu
 * @param $id_concert id koncertu
 * @param $concertObj objekt Concert
 * @param $bandObj objekt Band
 * @return pole kapel
 */
function getConcertsBands($id_concert, $concertObj, $bandObj){
    $bandIds = $concertObj->getBands("", array(0 => array("column" => "koncert_id_koncert", "value" => intval($id_concert), "symbol" => "=")), "");
    $bands = array();
    for($i = 0; $i < sizeof($bandIds); $i++){
       $row = $bandObj->getBand($bandIds[$i]['kapela_id_kapela'], "");
       $bands[$i] = $row;
    }
    
    return $bands;
}

/**
 * Vrati pole koncertu, na nichz kapela vystupuje
 * @param $id_band id kapely
 * @param $concertObj objekt Concert
 * @param $bandObj objekt Band
 * @return pole koncertu
 */
function getBandsConcerts($id_band, $concertObj, $bandObj){
    $concertIds = $bandObj->getConcerts("", array(0 => array("column" => "kapela_id_kapela", "value" => intval($id_band), "symbol" => "=")), "");
    $concerts = array();
    for($i = 0; $i < sizeof($concertIds); $i++){
       $row = $concertObj->getConcert($concertIds[$i]['koncert_id_koncert'], "");
       $concerts[$i] = $row;
    }
    
    return $concerts;
}

/**
 * Vrati kapely daneho uzivatele 
 * @param $id_user id uzivatele
 * @param $userObj objekt User
 * @param $bandObj objekt Band
 * @return pole kapel
 */
function getUsersBands($id_user, $userObj, $bandObj){
    $bandIds = $userObj->getBands("", array(0 => array("column" => "uzivatel_id_uzivatel", "value" => intval($id_user), "symbol" => "=")), "");
    $bands = array();
    for($i = 0; $i < sizeof($bandIds); $i++){
       $row = $bandObj->getBand($bandIds[$i]['kapela_id_kapela'], "");
       $bands[$i] = $row;
    }
     
    return $bands;
}

/**
 * Prida uzivatele ke kapele
 * @param $id_band id kapely
 * @param $id_user id uzivatele
 * @param $userObj objekt User
 * @param $bandObj objekt Band
 */
function addUserToBand($id_band, $id_user, $userObj, $bandObj){
    $check = $userObj->getBands("1", array(0 => array("column" => "kapela_id_kapela", "value" => intval($id_band), "symbol" => "="),
                                             1 => array("column" => "uzivatel_id_uzivatel", "value" => intval($id_user), "symbol" => "=")), "");    
    if($check != NULL){
       echo "<script>alert(\" Uživatel už je přidán ke kapele \");</script>"; 
    }
    else{
        $bandObj->addUser($id_band, $id_user);
        echo "<script>alert(\" Přidáno \");</script>";
    }
    echo "<script> location.replace(\"index.php?user=$id_user\"); </script>"; 
}

/**
 * Odstrani uzivatele z kapely
 * @param $id_user id uzivatele
 * @param $id_band id kapely
 * @param $bandObj objekt Band
 */
function deleteUserFromBand($id_user, $id_band, $bandObj){
    $bandObj->deleteUser($id_band, $id_user);
    
    echo "<script>alert(\" Byl jste odebrán z kapely \");</script>";
    echo "<script> window.history.back(); </script>";
}

/**
 * Prida kapelu na koncert
 * @param $id_concert id koncertu
 * @param $id_band id kapely
 * @param $concertObj objekt Concert
 */
function addBandToConcert($id_concert, $id_band, $concertObj){
    $check = $concertObj->getBands("1", array(0 => array("column" => "kapela_id_kapela", "value" => intval($id_band), "symbol" => "="),
                                             1 => array("column" => "koncert_id_koncert", "value" => intval($id_concert), "symbol" => "=")), "");
    if($check != NULL){
       echo "<script>alert(\" Kapela už je na seznamu \");</script>"; 
    }
    else{
        $concertObj->addBand($id_concert, $id_band);
        echo "<script>alert(\" Přidáno \");</script>";
    }
    echo "<script> location.replace(\"index.php?band=$id_band\"); </script>";
}


?>