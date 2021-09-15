<?php 

date_default_timezone_set('Europe/Istanbul');
set_time_limit(0);
error_reporting(0);
session_start();
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
		return array('symbol'=>$gt,'value'=>'0','birim'=>$birim,'eski'=>'0');
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
$sons="";
foreach ($durum as $cevir) {
	if ($cevir['value']<"0") {
		$style="color:red";
	}else{
		$style="color:#49b32f";
	}
	$sons=$sons.'<div class="col">
	<div class="card" >
	<div class="card-body">
	<h5 class="card-title"><i class="fas fa-coins"></i> '.$cevir['birim'].'</h5>
	<h6 class="card-subtitle mb-2 text-muted"><strong style=\''.$style.'\'> '.$cevir['value'].'</strong></h6>
	</div>
	</div>
	</div>';
}
$tablo="";
foreach ($datas as $dondur) {
	if ($dondur['value']<"0") {
		$style="color:red";
	}else{
		$style="color:#49b32f";
	}
	$tablo=$tablo.'<tr align="center"><td><strong>'.$dondur['symbol'].'</strong></td><td style=\''.$style.'\'><strong>'.$dondur['value']." ".$dondur['birim'].'</strong></td><td style=\''.$style.'\'><strong>%'.$dondur['yuzde'].'</strong></td></tr>';
}

echo json_encode(array('son'=>$sons,'table'=>$tablo));