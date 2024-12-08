<?php
try {
   $konek = new mysqli('localhost','root','root','loker');
} catch (\Throwable $th) {
   die('mysql_error');
}