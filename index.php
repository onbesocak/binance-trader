<?php 
session_start();
if (!empty($_SESSION['kullanici_oturum'])) {
	header('Location: dashboard.php');
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Giriş Yap</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-grid.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-grid.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-reboot.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-reboot.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
</head>
<body>
	<div class="container col-md-4">
		<br><br><br><br>
		<div class="card">
			<div class="container">
				<form method="post">
					<br><br>
					<input type="text" class="form-control" placeholder="Kullanıcı Adınız" name="kadi" autocomplete="off">
					<br>
					<input type="password" class="form-control" placeholder="Şifreniz" name="sifre" autocomplete="off">
					<br>
					<button name="login" type="submit" class="btn btn-primary btn-lg btn-block"><strong>Giriş Yap</strong></button>
					<br>
				</form>
				<?php 
				if (isset($_POST['login'])) {
					if (empty($_POST['kadi'] and $_POST['sifre'])) {
						?><div class="alert alert-danger"><strong>Boş Bırakmayınız</strong></div><?php
					}else{

						if ($_POST['kadi']!="admin" and $_POST['sifre']!="admin") {
							?><div class="alert alert-danger"><strong>Geçersiz Bilgiler</strong></div><?php
						}else{
							$_SESSION['kullanici_oturum']=true;
							header('Location: dashboard.php');
							exit;
						}
					}

				}
				?>
			</div>
		</div>


	</div>

	<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.js"></script>
	<script type="text/javascript" src="https://kit.fontawesome.com/088bf550ff.js"></script>
	<script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>