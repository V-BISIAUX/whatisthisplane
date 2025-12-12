<?php
    require_once "src/backend/User.php";
    try {
        $user = new User();

        $nbr = $user->deleteAccountNotActif();
        echo "Nettoyage terminé. $nbr comptes inactifs supprimés.";
    }catch (Exception $e){
        echo "Erreur lors de l'execution du script : " . $e->getMessage();
    }
?>