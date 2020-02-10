<?php
///error_reporting(0);
@session_start();
$_SESSION['cururl'] = "";
unset($_SESSION['cururl']);
$_SESSION['cururl'] = $_POST['currents'];

if($_SERVER['environment'] == 'staging') {

  	$conn = mysqli_connect("master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com", "fsmaster", "FSdbm6512", "fsmaster-nfstage");
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

} elseif($_SERVER['environment'] == 'production') {

  $conn = mysqli_connect("master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com", "fsmaster", "FSdbm6512", "fsmaster-production");
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
}
$sql = "SELECT limit_ip FROM check_ip where limit_ip='".$_POST['ip']."'";

$result = mysqli_query($conn,$sql);

$count_rows	=	mysqli_num_rows($result);
if($_POST['ip']!='' && $count_rows <= 4){

	 $sql_insert = "INSERT INTO check_ip (limit_ip)
VALUES ('".$_POST['ip']."')";

mysqli_query($conn,$sql_insert);
	}
$sql_counting = "SELECT limit_ip FROM check_ip where limit_ip='".$_POST['ip']."'";
$result_counting = mysqli_query($conn,$sql_counting);
$count_rows_counting	=	mysqli_num_rows($result_counting);


$res_array = array("beforecount"=>$count_rows,"aftercount"=>$count_rows_counting);
echo json_encode($res_array);
?>