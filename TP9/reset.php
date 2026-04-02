&lt;?php
session_start();
unset($_SESSION['mot']);
unset($_SESSION['motAffiche']);
unset($_SESSION['coups']);
unset($_SESSION['fini']);
echo "OK";
?&gt;