<?php
$mysqli = new mysqli('localhost', 'root', '', 'siperkul');
$res = $mysqli->query('DESCRIBE tb_dosen');
while($row = $res->fetch_assoc()) {
    print_r($row);
}
