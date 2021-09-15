<?php 
date_default_timezone_set('Europe/Istanbul');
set_time_limit(0);
error_reporting(0);
session_start();
if (empty($_SESSION['kullanici_oturum'])) {
	header('Location: index.php');
	exit;
}else{
	if (isset($_GET['q'])) {
		if ($_GET['q']=="exit" or $_GET['q']=="settings") {
			if ($_GET['q']=="exit") {
				session_destroy();
				header('Location: index.php');
				exit;
			}

		}else{
			header('Location: index.php');
			exit;		
		}
	}
	if (empty($_GET['q'])) {
		include 'vendor/autoload.php';
		include 'config.php';
		$api = new Binance\API($key,$secret);
		$ticker = $api->prices(); 
		$balances = $api->balances($ticker);
		function get($ticker,$api,$gt){
			$total=false;
			$toplam_miktar=false;
			$birim="BUSD";
			try {
				$history = $api->history($gt.$birim);
				if (empty($history)) {
					$birim="USDT";
					$history = $api->history($gt.$birim);
					if (empty($history)) {
						$birim="BTC";
						$history = $api->history($gt.$birim);
					}
				}
			} catch (Exception $e) {
				try {
					$birim="USDT";
					$history = $api->history($gt.$birim);
					if (empty($history)) {
						$birim="BUSD";
						$history = $api->history($gt.$birim);
						if (empty($history)) {
							$birim="BTC";
							$history = $api->history($gt.$birim);
						}
					}
				} catch (Exception $e) {
					$birim="BTC";
					$history = $api->history($gt.$birim);
					if (empty($history)) {
						$birim="USDT";
						$history = $api->history($gt.$birim);
						if (empty($history)) {
							$birim="BUSD";
							$history = $api->history($gt.$birim);
						}
					}
					
				}
			}
			foreach ($history as $key) {
				if ($key['isBuyer']) {
					$total=$total+$key['qty'];
					$veri[]=$key;
				}else{
					$total=$total-$key['qty'];
					unset($veri);
				}
			}
			if (empty($veri)) {
				return array('symbol'=>$gt,'value'=>'0','birim'=>$birim,'eski'=>'0','yeni'=>'0','yuzde'=>'0');
			}else{
				$net="0";
				$eski="0";
				$yeni="0";
				foreach ($veri as $x) {
					$eski_usdt_bakiye=$x['qty']*$x['price'];
					$yeni_usdt_bakiye=$x['qty']*$ticker[$gt.$birim];
					$net=$net-($eski_usdt_bakiye-$yeni_usdt_bakiye);
					$eski=$eski+$x['qty']/$x['price'];
					$yeni=$yeni+$x['qty']/$ticker[$gt.$birim];
					
				}
				return array('symbol'=>$gt,'value'=>$net,'birim'=>$birim,'eski'=>$eski,'yeni'=>$yeni,'yuzde'=>str_replace('-','',substr(100-(($yeni	*100)/$eski),0,6)));
			}
		}
		preg_match_all('@"(.*?)":{"available"(.*?)",@',json_encode($balances),$balancesx2);
		$balancesx=$balancesx2['1'];
		$rakam="0";
		foreach ($balancesx as $j) {
			$j=explode('"',$j);
			$j=$j[count($j)-1];
			if ($j!="USDT" and $j!="BUSD" and $j!="BTC" and $j!="BNB") {
				if (str_replace(':"','',$balancesx2['2'][$rakam])>'0') {
					$uzanti[]=[$j,str_replace(':"','',$balancesx2['2'][$rakam])];	
				}
			}
			$rakam++;
		}
		foreach ($uzanti as $key) {
			$result=get($ticker,$api,$key['0']);
			if ($result!="0") {
				$datas[]=$result;
			}
		}
		foreach ($datas as $key) {
			if (empty($durum)) {
				$durum[]=array('birim'=>$key['birim'],'value'=>$key['value'],'eski'=>$key['eski']);
			}else{
				$stat=false;
				$ii="0";
				foreach ($durum as $p) {
					if ($stat==false) {
						if ($p['birim']==$key['birim']) {
							$sayi=$ii;
							$stat=true;
						}
						$ii++;
					}
				}
				if ($stat==false) {
					$durum[]=array('birim'=>$key['birim'],'value'=>$key['value'],'eski'=>$key['eski']);
				}else{
					$eski=$durum[$sayi];
					if (!strstr($key['value'],"-")) {
						$durum[$sayi]=array('birim'=>$key['birim'],'value'=>($eski['value']+$key['value']),'eski'=>$key['eski']);
					}else{
						$durum[$sayi]=array('birim'=>$key['birim'],'value'=>($eski['value']+$key['value']),'eski'=>$key['eski']);
					}
				}
			}
		}

	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Anasayfa</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-grid.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-grid.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-reboot.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-reboot.min.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/css.css">
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<a class="navbar-brand" href="dashboard.php"><i class="fas fa-home"></i> Anasayfa</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">

				<li class="nav-item">
					<a class="nav-link" href="?q=settings"><i class="fas fa-cogs"></i> Api Ayarlarım</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="?q=exit"><i class="fas fa-times"></i> Çıkış Yap</a>
				</li>
			</ul>

		</div>
	</nav>
	<div class="container">
		<br>
		<div class="card">
			<?php 
			if (isset($_GET['q'])) {
				if ($_GET['q']=="settings") {
					include 'config.php';
					?>
					<div class="container">
						<form method="post">
							<br>
							<center><h4>Api Ayarlarım</h4></center>
							<br>
							<?php 
							if (empty($key)) {
								?><input type="text" class="form-control" placeholder="Api Key" name="key" autocomplete="off"><?php
							}else{
								?><input type="text" class="form-control" placeholder="Api Key" name="key" autocomplete="off" value="<?php echo $key; ?>"><?php							 
							}
							?>
							<br>
							<?php 
							if (empty($secret)) {
								?><input type="text" class="form-control" placeholder="Secret Key" name="secret" autocomplete="off"><?php	
							}else{
								?><input type="text" class="form-control" placeholder="Secret Key" name="secret" autocomplete="off" value="<?php echo $secret; ?>"><?php	
							}
							?>
							<br>

							
							<button name="save" type="submit" class="btn btn-danger btn-lg btn-block"><strong>Kaydet</strong></button>
							<br>
						</form>
						<?php 
						if (isset($_POST['save'])) {
							if (empty($_POST['key'] and $_POST['secret'])) {
								?><div class="alert alert-danger"><strong>Boş Bırakmayınız</strong></div><?php
							}else{
								$myfile = fopen("config.php", "w");
								$txt = "<?php\n";
								fwrite($myfile, $txt);
								$txt = "\$key=\"".ltrim(rtrim($_POST['key']))."\";\n";
								fwrite($myfile, $txt);
								$txt = "\$secret=\"".ltrim(rtrim($_POST['secret']))."\";\n";
								fwrite($myfile, $txt);
								$txt = "?>";
								fwrite($myfile, $txt);
								fclose($myfile);
								
								?><div class="alert alert-success"><strong>Başarıyla Kaydedildi</strong></div><?php
							}

						}
						?>

					</div>
					<?php
				}
			}else{
				include 'vendor/autoload.php';
				include 'config.php';
				$api = new Binance\API($key,$secret);
				?>
				<center><h1><strong>Son Durum</strong></h1></center>
				<div id="son"  class="row container">
					<?php 
					foreach ($durum as $cevir) {
						if ($cevir['value']<"0") {
							$style="color:red";
						}else{
							$style="color:#49b32f";
						}
						?>
						<div class="col">
							<div class="card" >
								<div class="card-body">
									<h5 class="card-title"><i class="fas fa-coins"></i> <?php echo $cevir['birim']; ?></h5>
									<h6 class="card-subtitle mb-2 text-muted"><strong style="<?php echo $style; ?>"><?php echo $cevir['value']; ?></strong></h6>
								</div>
							</div>
						</div>
						<?php
					}
					?>

				</div>
				<br>
        <ul class="flex-container">
  <li class="flex-item">
    <ul class="dene">
      <li >isim</li>
      <li>ddd</li>
      <li>hgh</li>
    </ul>
  </li>
  
  <li class="flex-item">2</li>
  <li class="flex-item">3</li>
  <li class="flex-item">4</li>
  <li class="flex-item">5</li>
  <li class="flex-item">6</li>
</ul>
				<div class="container">
					<table class="table">
						<thead>
							<tr align="center">
								<th scope="col">Birim</th>
								<th scope="col">Son Durum</th>
								<th scope="col"></th>
							</tr>
						</thead>
						<tbody id="tablo">
							<?php 
							foreach ($datas as $dondur) {
								if ($dondur['value']<"0") {
									$style="color:red";
								}else{
									$style="color:#49b32f";
								}
								?>
								<tr align="center">
									<td><strong><?php echo $dondur['symbol']; ?></strong></td>
									<td style="<?php echo $style; ?>"><strong><?php echo $dondur['value']." ".$dondur['birim']; ?></strong></td>
									<td style="<?php echo $style; ?>"><strong><?php echo $dondur['value']." ".$dondur['birim']; ?></strong></td>
									<td style="<?php echo $style; ?>"><strong><?php echo "%".$dondur['yuzde']; ?></strong></td>
								</tr>
								<?php
							}
							?>


							
							
						</tbody>
					</table>
				</div>
				<br>
				<?php
			}
			?>

		</div>


	</div>
	<br><br>
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.js"></script>
	<script type="text/javascript" src="https://kit.fontawesome.com/088bf550ff.js"></script>
	<script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
	<?php 
	if (empty($_GET['q'])) {
		?>
		<script>
			setInterval(function() {
				gets();
			}, 1000 * 30);
			gets();
			function gets()
			{

				$.post('./ajax.php', '', function(alios){
					if ( alios.son )
					{
						document.getElementById("son").style.display = 'none';
						$('div[id=son]').html(alios.son);
						$("#son").fadeIn(3000);

					}
					if ( alios.table )
					{
						document.getElementById("tablo").style.display = 'none';
						$('tbody[id=tablo]').html(alios.table);
						$("#tablo").fadeIn(3000);
					}
				}, 'json');
			}


		</script>
		<?php
	}
	?>


</script>

</body>
</html>