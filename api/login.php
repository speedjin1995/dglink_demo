<?php
require_once 'db_connect.php';

session_start();

$post = json_decode(file_get_contents('php://input'), true);
$services = 'Login';
$requests = json_encode($post);

$stmtL = $db->prepare("INSERT INTO api_requests (services, request) VALUES (?, ?)");
$stmtL->bind_param('ss', $services, $requests);
$stmtL->execute();
$invid = $stmtL->insert_id;

$username=$post['userEmail'];
$password=$post['userPassword'];
$now = date("Y-m-d H:i:s");

$stmt = $db->prepare("SELECT users.*, companies.reg_no, companies.name AS comp_name, companies.address, companies.address2, companies.address3, companies.address4, companies.phone, companies.email from users, companies where users.customer = companies.id AND users.username= ?");
//$stmt = $db->prepare("SELECT * from users where username= ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if(($row = $result->fetch_assoc()) !== null){
	$password = hash('sha512', $password . $row['salt']);
	
	if($password == $row['password']){
	    $message = array();
	    $message['id'] = $row['id'];
        $message['username'] = $row['username'];
        $message['name'] = $row['name'];
        $message['role_code'] = $row['role_code'];
        $message['languages'] = $row['languages'];
        $message['customer'] = $row['customer'];
        $message['package'] = $row['packages'];
        $message['customer_det'] = array(
            "id" => $row['customer'],
            "reg_no" => $row['reg_no'] ?? '',
            "name" => $row['name'],
            "address" => $row['address'],
            "address2" => $row['address2'] ?? '',
            "address3" => $row['address3'] ?? '',
            "address4" => $row['address4'] ?? '',
            "phone" => $row['phone'],
            "email" => $row['email']
        );

        if($row['farms'] != null){
            $message['farms'] = json_decode($row['farms'], true);
        }
        else{
            $message['farms'] = array();
        }
        
		
        $response = json_encode(
            array(
                "status"=> "success", 
                "message"=> $message
            )
        );
        $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
        $stmtU->bind_param('ss', $response, $invid);
        $stmtU->execute();

        $stmt->close();
        $stmtU->close();
		$db->close();
        echo $response;
	} 
	else{
		$response = json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Username or Password is wrong"
            )
        );
        $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
        $stmtU->bind_param('ss', $response, $invid);
        $stmtU->execute();
    
        $db->close();
        echo $response;
	}
} 
else{
    $response = json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Username or Password is wrong"
        )
    );
    $stmtU = $db->prepare("UPDATE api_requests SET response = ? WHERE id = ?");
    $stmtU->bind_param('ss', $response, $invid);
    $stmtU->execute();

    $db->close();
    echo $response;
}
?>
