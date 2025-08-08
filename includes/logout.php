<?php
session_start();
session_destroy();
header("Location: ../store/login.php");
exit;
