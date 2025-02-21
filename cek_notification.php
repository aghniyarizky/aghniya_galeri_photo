<?php
include "connection.php";
if(isset($_POST['trash'])){
    $id_notif = $_POST['id_notif'];

    $sql = mysqli_query($conn, "DELETE FROM aghniya_notifikasi WHERE aghniya_notifikasi_id = $id_notif");

    if($sql){
        echo "<script>
                location.href='notification.php';
            </script>";
    }else{
        echo "<script>
                alert('Failed to Remove Notification!');
                location.href='notification.php';
            </script>";
    }
}

?>