<?php
    ob_start();
    session_start();
    include("header.php");
    include("redirect.php");
    //On s'assure que les données mises dans une session précédente soient effacées
    unset($_SESSION['retitle']);
    unset($_SESSION['receiver']);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Reception box</title>
    </head>
    <body>
    <div class="container">
        <a class="btn btn-secondary btn-lg mt-5" type="button" href="message.php">+ New message</a>

<?php
    
    $file_db = new PDO('sqlite:/usr/share/nginx/databases/database.sqlite');
    // Set errormode to exceptions
    $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // On récupère l'id de l'utilisateur connecté grâce à son username
    $user = $_SESSION['username'];
    $user_query = $file_db->query("SELECT id FROM collaborators WHERE `login`='$user';")->fetch();
    $user_id = $user_query['id'];

    // Liste des messages adressés à l'utilisateur
    $messages_query = $file_db->query(" SELECT * FROM 'messages' WHERE idDestinataire = {$user_id} ORDER BY time_value DESC;");

    $html = "";
    // Pour créer un id unique pour permettre à details de ne révéler qu'un seul contenu de message
    $i = 0;
    while($row = $messages_query->fetch(PDO::FETCH_ASSOC)){
        $i += 1;
        $sender_query = $file_db->query(" SELECT login FROM collaborators WHERE `id` = '{$row['idExpediteur']}'; ")->fetch();
        $sender = $sender_query['login'];

        // Pour chaque message qui apparaît, on crée un bouton "Answer", "Delete" et surtout "Details"
        $html .= 
        "<div class='card m-5 w-75'>
            <div class='card-header'>
                <p>    
                    <div class='font-weight-bold'> 
                        <h4 class='d-flex justify-content-between align-items-center'> 
                        {$row['title']}
                        <button class='btn btn-sm btn-dark' data-toggle='collapse' data-target='#collapse{$i}' type='button' aria-expanded='false' aria-controls='collapse{$i}'> Details </button>
                        </h4> 
                    </div>
                    <div class='text-muted'> <h6>From : {$sender} </h6> </div>
                    <div> {$row['time_value']} </div>
                </p>
            </div>
            <div class='collapse' id='collapse{$i}'>
                <div class='card card-body text-justify'>
                    {$row['content']}
                </div>
            </div>
        
            <div class='card-footer text-center'>
                <p>
                    <form action='' method='post'>
                        <input style='display:none;' id='messageid' name='messageid' value='{$row['id']}'/>
                        <input style='display:none;' id='messagetitle' name='messagetitle' value='{$row['title']}'/>
                        <input class='btn btn-dark' name='answer' value='Answer' type='submit'/>    
                        <input class='btn btn-danger' name='delete' value='Delete' type='submit'/>    
                    </form>
                </p>
            </div>
        </div>";
    }
    echo $html;
?>


<?php
    // Vérifie que le bouton delet a bien été pressé
    if(isset($_POST['delete'])){
        // Et que l'id du message existe bien
        if(isset($_POST['messageid'])){
            $message_id = $_POST['messageid'];
            $file_db->exec(" DELETE FROM messages WHERE id=$message_id; ");
            header('Location: '.$_SERVER['REQUEST_URI']);
            die();
        }
    }
    //Sinon on regarde si c'est le bouton answer
    else if(isset($_POST['answer'])){
        // On continue seulement si l'id du message et le titre son bien set
        if(isset($_POST['messageid']) && isset($_POST['messagetitle'])){
            //On récupère alors le nom de l'expéditeur et le titre pour le passer à la page suivante
            $_SESSION['receiver'] = $sender;
            $_SESSION['retitle'] = 'Re:' . $_POST['messagetitle'];
            // On dirige ensuite l'utilisateur à la page de rédaction du message
            header('Location: message.php');
            die();
        }
    }
?>

    </div>
    </body>
</html>