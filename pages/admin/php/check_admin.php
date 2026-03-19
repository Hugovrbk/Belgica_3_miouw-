<?php
session_start();

// 1. On vérifie si l'utilisateur est connecté
// 2. On vérifie si la valeur 'isadmin' dans la session est égale à 1 (ou true)
if (!isset($_SESSION['user_id']) || $_SESSION['user_admin'] != 1) {
    
    // Si l'un des deux échoue, on redirige vers la page de connexion ou l'accueil
    header("Location: login.php");
    exit(); 
}
?>