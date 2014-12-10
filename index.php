<?php
session_start(); 
// konfigurace
require 'config/config.inc.php';
require 'config/functions.inc.php';
  
//tridy
require 'application/core/app.class.php';
require 'application/core/db.class.php';
require 'application/core/band.class.php';
require 'application/core/user.class.php';
require 'application/core/concert.class.php';
  
// twig
require_once 'twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);
//pole hodnot do sabolny
$twig_array = array();  
  
//zacatek aplikace
$app = new app();
//databaze
$app->Connect();
$db_connection = $app->getConnection(); 


// ******************** uzivatel ********************
//prihlaseni
if(isset($_POST['signin'])){
    $users = new User($db_connection);
    signin($_POST['nm'], $_POST['pwd'], $users);
    
    unset($_POST);
}
  
//odhlaseni
if(isset($_GET['signout'])){
    signout();
}

//registrace
if(isset($_POST['signup'])){
    $users = new User($db_connection);
    signup($_POST['nm'], $_POST['email'], $_POST['pwd'], $_POST['pwdr'], $users);
    
    unset($_POST);
}

//zmena udaju uzivatele
if(isset($_POST['updateUser']) && $_POST['updateUser'] == 1){
    $userObj = new User($db_connection);
    
    if(isset($_SESSION['auth']))
        updateUser($_POST, $_SESSION['id'], $userObj);
    
    unset($_POST);
}

//zmena heslo
if(isset($_POST['updatePass']) && $_POST['updatePass'] != ""){
    $userObj = new User($db_connection);
    
    if(isset($_SESSION['auth']))
        updatePassword($_POST, $_SESSION['id'], $userObj);
    
    unset($_POST);
}

//tlacitko prihlaseni = false / menu pro prihlaseneho uzivatele = true
if(isset($_SESSION['auth']) && $_SESSION['auth'] == 1){
    $users = new User($db_connection);
    $user = $users->getUser($_SESSION['id'], "");
    
    $twig_array['user_menu'] = true;
    $twig_array['username'] = $_SESSION['name'];
    $twig_array['my_email'] = $user['email'];
    $twig_array['my_text'] = $user['text'];
    $twig_array['userid'] = $user['id_uzivatel'];
    
        //ziskani kapel prihlaseneho uzivatele
        $bands = new Band($db_connection);
        $band_list = getUsersBands($_SESSION['id'], $users, $bands);
        if($band_list != null){
            $twig_array['my_band_list'] = $band_list;
        }
    
        //ziskani koncertu prihlaseneho uzivatele
        $concerts = new Concert($db_connection);
        $concert_list = getUsersConcerts($_SESSION['id'], $concerts);
        if($concert_list != null){
            $twig_array['my_concert_list'] = $concert_list;
        }

} 
else{  
    $twig_array['user_menu'] = false;
}

// ************* kapela ********************
//pridat kapelu
if(isset($_POST['addBand'])){
    $bands = new Band($db_connection);
    $form = $_POST;
    addBand($form, $_SESSION['id'], $bands);
}

//zmena udaju kapely
if(isset($_POST['confBand']) && $_POST['confBand'] == 1){
    $bands = new Band($db_connection);
    //kontrola prav
    if($bands->isInBand($_SESSION['id'], $_POST['id'])){
        updateBand($_POST, $bands);
    }
}

//smazat kapelu
if(isset($_GET['deleteBand']) && $_GET['deleteBand'] != ""){
    $bands = new Band($db_connection);
    
    //kontrola prav
    if($_SESSION['admin'] == 1){
        deleteBand($_GET['deleteBand'], $bands);
    }
}

// ******************** koncert ********************
//pridat koncert
if(isset($_POST['addConcert'])){
    $concerts = new Concert($db_connection);
    $form = $_POST;
    addConcert($form, $_SESSION['id'], $concerts);
    
    unset($_POST);
}

//zmena udaju koncertu
if(isset($_POST['confConcert']) && $_POST['confConcert'] == 1){
    $concerts = new Concert($db_connection);
    //kontrola prav
    $concert = $concerts->getConcert($_POST['id'], "");
    
    if($_SESSION['id'] == $concert['uzivatel_id_uzivatel']){
        updateConcert($_POST, $concerts);
    }
    
    unset($_POST);
}

//smazat koncert
if(isset($_GET['deleteConcert']) && $_GET['deleteConcert'] != ""){
    $concerts = new Concert($db_connection);
    $concert = $concerts->getConcert($_GET['deleteConcert']);
    
    //kontrola prav
    if(($concert['uzivatel_id_uzivatel'] == $_SESSION['id']) || $_SESSION['admin'] == 1){
        deleteConcert($_GET['deleteConcert'], $concerts);
    }
}

// ******************** kapela_koncert ********************
//pridat kapelu na koncert
if(isset($_GET['idBand']) && $_GET['idBand'] != "" && isset($_GET['idConcert']) && $_GET['idConcert'] != "" && $_SESSION['auth'] == 1){
    $concerts = new Concert($db_connection);
    addBandToConcert($_GET['idConcert'], $_GET['idBand'], $concerts);
}

//odstraneni kapely z koncertu
if(isset($_GET['deleteBandC']) && $_GET['deleteBandC'] != "" && isset($_GET['concertId']) && $_GET['concertId'] != ""){
    $concerts = new Concert($db_connection);
    $concert = $concerts->getConcert($_GET['concertId'], "");
    
    $where = array(0 => array("column" => "uzivatel_id_uzivatel", "value" => intval($_SESSION['id']), "symbol" => "="),
                  1 => array("column" => "kapela_id_kapela", "value" => intval($_GET['deleteBandC']), "symbol" => "="));
    
    $users = new User($db_connection);
    $bandRights = $users->getBands(1, $where, "");
        
    //kontrola prav
    if(($concert['uzivatel_id_uzivatel'] == $_SESSION['id']) || ($bandRights != NULL)){
        deleteBandConcert($_GET['deleteBandC'], $_GET['concertId'], $concerts);
    }
}

// ******************** uzivatel_kapela ********************
//pridat uzivatele do kapely
if(isset($_GET['idUser']) && $_GET['idUser'] != "" && isset($_GET['idBand']) && $_GET['idBand'] != "" && $_SESSION['auth'] == 1){
    $bands = new Band($db_connection);
    $users = new User($db_connection);
    
    addUserToBand($_GET['idBand'], $_GET['idUser'], $users, $bands);
}

//odstraneni uzivatele z kapely
if(isset($_GET['leaveBand']) && $_GET['leaveBand'] != ""){
    deleteUserFromBand($_SESSION['id'], $_GET['leaveBand'], new Band($db_connection));
}
  
//******************** detail ********************
if(isset($_GET['user']) && $_GET['user'] != ""){
    $users = new User($db_connection);
    $bands = new Band($db_connection);
    
    $template = $twig->loadTemplate('template_detail.html');
    $user = $users->getUser(intval($_GET['user']), ""); 
    
    //twig
    if(isset($_SESSION['auth'])){
        $band_list = getUsersBands($_SESSION['id'], $users, $bands);
        if($_SESSION['id'] != $_GET['user'] && $band_list != null){
            $twig_array['band_list'] = $band_list;
        }
    }
    
    $band_list = getUsersBands($_GET['user'], $users, $bands);
    $twig_array['user_band_list'] = $band_list;
    $twig_array['headername'] = "<h1>".$user['jmeno']."</h1>";
    $twig_array['detail_content'] = array("item1" => $user['text'], "item2" => "<h4>E-mail</h4>".$user['email']);
    $twig_array['detail_image'] = "user_icon.png";
    $twig_array['id_user'] = $_GET['user'];
}
else if(isset($_GET['band']) && $_GET['band'] != ""){
    $bands = new Band($db_connection);
    $concerts = new Concert($db_connection);
    $users = new User($db_connection);
    
    $template = $twig->loadTemplate('template_detail.html');
    $band = $bands->getBand(intval($_GET['band']), "");
    $memberIds = $bands->getUserIds(intval($_GET['band']), "", "");
    
    $members = array();
    for($i = 0; $i < sizeof($memberIds); $i++){
        $members[$i] = $users->getUser($memberIds[$i]['uzivatel_id_uzivatel'], "");
    }
    
    //twig
    if(isset($_SESSION['auth'])){      
        $concert_list = getUsersConcerts($_SESSION['id'], $concerts);
        
        if($concert_list != null){
            $twig_array['concert_list'] = $concert_list;
        }
        
        if($bands->isInBand($_SESSION['id'], $_GET['band'])){
            $twig_array['config_band'] = $band;
        }
    }
    
    $twig_array['id_band'] = $band['id_kapela'];
    $twig_array['member_list'] = $members;
    $twig_array['concerts_list'] = getBandsConcerts($_GET['band'], $concerts, $bands);
    $twig_array['headername'] = "<h1>".$band['nazev']."</h1><h3>".$band['styl']." (".$band['mesto']." ".$band['zeme'].")</h3>";
    $twig_array['detail_content'] = array("item1" => $band['text'], "item2" => "");
    $twig_array['detail_image'] = "band_icon.png";
}

else if(isset($_GET['concert']) && $_GET['concert'] != ""){
    $concerts = new Concert($db_connection);
    $bands = new Band($db_connection);
    $users = new User($db_connection);
    
    $template = $twig->loadTemplate('template_detail.html');
    $concert = $concerts->getConcert(intval($_GET['concert']));
    
    //twig
    if(isset($_SESSION['auth'])){
        if($concert['uzivatel_id_uzivatel'] == $_SESSION['id']){
            $twig_array['config_concert'] = $concert; //doplnit
        }
     }
    
    $concert['datum'] = dateFormat($concert['datum']);
    $twig_array['headername'] = "<h1>".$concert['nazev']."</h1>";
    $twig_array['detail_content'] = array("item1" => $concert['text'], "item2" => getConcertInfo($concert, $users));
    $twig_array['bands_list'] = getConcertsBands($_GET['concert'], $concerts, $bands);
    $twig_array['detail_image'] = "concert_icon.png";
}
  
//******************** seznamy ********************
else if((isset($_GET['list']) && $_GET['list'] != "") || (isset($_GET['search']) && $_GET['search'] != "")){
    $limit_list = 8;
    $where = array();
    
    if(isset($_GET['page']) && $_GET['page']!= 0){
        $page = $_GET['page'];
    }
    else $page = 0;
       
    $start_index = $page * $limit_list;
       
    $template = $twig->loadTemplate('template_list.html');
    
    if(isset($_SESSION['auth']) && $_SESSION['admin'] == 1){
        $twig_array['admin'] = 1;
    }
    
    if(isset($_GET['list']) && !isset($_GET['search'])){
        $list = $_GET['list'];
        switch($list){
            case "bands": $twig_array['listheader'] = "Kapely";
                $bands = new Band($db_connection);
            
                $result = $bands->getBands("", $where, "DESC");
                $twig_array['tabledata'] = array_slice($result, $start_index, $limit_list);
            
                if(($start_index + $limit_list) < sizeof($result))
                    $twig_array['nextpage'] = '<a href="index.php?list=bands&page='.($page+1).'">Další >></a>';
            
                if($page > 0)
                    $twig_array['previouspage'] = '<a href="index.php?list=bands&page='.($page-1).'"><< Předchozí</a>';
                    $twig_array['pagecount'] = ($page+1).'/'.(ceil(sizeof($result)/$limit_list));
            break;
            
            case "concerts": $twig_array['listheader'] = "Koncerty"; 
                $concerts = new Concert($db_connection); 
            
                $date = date("Y-m-d");
                array_push($where, array("column" => "datum", "symbol" => ">", "value" => "$date"));
                
                $result = $concerts->getConcerts("", $where, "ASC");
                $result = dateFormatArray($result);
                
                $twig_array['tabledata'] = array_slice($result, $start_index, $limit_list);
            
                if(($start_index + $limit_list) < sizeof($result))
                    $twig_array['nextpage'] = '<a href="index.php?list=concerts&page='.($page+1).'">Další >></a>';
                
                if($page > 0)
                    $twig_array['previouspage'] = '<a href="index.php?list=concerts&page='.($page-1).'"><< Předchozí</a>';
                
                $twig_array['pagecount'] = ($page+1).'/'.(ceil(sizeof($result)/$limit_list));
            break;
            
            case "users": $twig_array['listheader'] = "Uživatelé";
                $users = new User($db_connection);
                
                $result = $users->getUsers("", array(), "DESC");
                $twig_array['tabledata'] = array_slice($result, $start_index, $limit_list);
            
                if(($start_index + $limit_list) < sizeof($result))
                    $twig_array['nextpage'] = '<a href="index.php?list=users&page='.($page+1).'">Další >></a>';
                
                if($page > 0)
                    $twig_array['previouspage'] = '<a href="index.php?list=users&page='.($page-1).'"><< Předchozí</a>';
                
                $twig_array['pagecount'] = ($page+1).'/'.(ceil(sizeof($result)/$limit_list));
            break;
        }
    }
    else{
        $search = $_GET['search'];
        $twig_array['listheader'] = "Vyhledávání";
        $result;
            
        //kapely
        $where = array(0 => array("column" => "nazev", "symbol" => "LIKE", "value" => "%$search%"));
        $bands = new Band($db_connection);
        $result = $bands->getBands("", $where, "DESC");
        //uzivatele
        $where = array(0 => array("column" => "jmeno", "symbol" => "LIKE", "value" => "%$search%"));
        $users = new User($db_connection);
        $result_users = $users->getUsers("", $where, "DESC");
        $result = array_merge($result, $result_users);
        //koncerty
        $where = array(0 => array("column" => "nazev", "symbol" => "LIKE", "value" => "%$search%"));
        $concerts = new Concert($db_connection);
        
        $result_concerts = $concerts->getConcerts("", $where, "DESC");
        $result_concerts = dateFormatArray($result_concerts);
        
        $result = array_merge($result, $result_concerts);
        
        $twig_array['tabledata'] = array_slice($result, $start_index, $limit_list);
        
        if(($start_index + $limit_list) < sizeof($result))
            $twig_array['nextpage'] = '<a href="index.php?search='.$search.'&page='.($page+1).'">Další >></a>';
        if($page > 0)
            $twig_array['previouspage'] = '<a href="index.php?search='.$_GET['search'].'&page='.($page-1).'"><< Předchozí</a>';
        $twig_array['pagecount'] = ($page+1).'/'.(ceil(sizeof($result)/$limit_list));
    }
}
  
//******************** homepage ********************
else{
    $template = $twig->loadTemplate('template_home.html');
  
    //hlavni strana text
    $twig_array['heading'] = 'Vítejte na Kapely & Koncerty';
    $twig_array['text'] = '
      <p>Databáze kapel a koncertů. Můžete se zde registrovat a založit svojí kapelu nebo koncert.</p>
      <p>Můžete do svojí kapely přidat ostatní uživatele, nebo na svůj koncert přidat kapely.</p>  
      ';
  
    //vypis kapel
    $bands = new Band($db_connection);
    $latest_bands = $bands->getBands(5, "", "DESC");
    $twig_array['bands'] = $latest_bands;
  
    //vypis koncertu - podle data
    $concerts = new Concert($db_connection);
    $date = date("Y-m-d");
    $where = array();
    array_push($where, array("column" => "datum", "symbol" => ">", "value" => "$date"));
    $concert_list = $concerts->getConcerts(5, $where, "ASC");
    $concert_list = dateFormatArray($concert_list);
    $twig_array['concerts'] = $concert_list;
  
    //vypis uzivatelu
    $users = new User($db_connection);
    $latest_users = $users->getUsers(5, array(),"DESC");
    $twig_array['users'] = $latest_users;
}
  
//vykresleni
echo $template->render($twig_array);
?>