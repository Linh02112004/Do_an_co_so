<?php
session_start();
session_destroy();
echo "<script>
    sessionStorage.removeItem('loggedIn');
    window.location.href = 'Home.html';
</script>";
exit;
?>