<?
//header('Content-type: text/html; charset=utf-8');

$paramsArray = array(
	'login' => 'subaru@suba.parts',
	'password' => '1F108',
	'partnumber' => $_POST['article'],
	'location' => '1',
	'row_count' => '40',
	'class_man' => 'SUBARU',
	'api_key' => 'EAAAAFsm0MAi0qE5Dcrq1L9Pdl5GPFlUUASCUQL4alXMWAuoFvHSqjzK3WqPNGRove4SQg=='
); 
 // преобразуем массив в URL-кодированную строку
$vars = http_build_query($paramsArray);
$host = 'https://www.zzap.ru';
$post = '/webservice/datasharing.asmx/GetStatPrices';
$options = array(
	'http' => array(  
	'method'  => 'POST',  // метод передачи данных
	'header'  => 'Content-type: application/x-www-form-urlencoded',  // заголовок 
	'content' => $vars,  // переменные
	)  
);  
$context  = stream_context_create($options);  // создаём контекст потока
$result = file_get_contents('https://www.zzap.ru//webservice/datasharing.asmx/GetSearchResult', false, $context); //отправляем запрос
//print_r($result);
$vowels = array('<string xmlns="http://www.zzap.ru/">', '</string>', '<?xml version="1.0" encoding="utf-8"?>'); // Убираем шлак, из за которого не читается строка JSON
$result = str_replace($vowels, "", $result); // Убрали

$arZZAP = json_decode($result, true);

$i = 0; // Счётчик
$i_max = 39; // Максимальная глубина

$arDetails = array();
$i=0;
if($arZZAP[table])
{


	foreach($arZZAP[table] as $key=>$oneArticle):
		if($oneArticle[class_user] == 'ООО СЕРВИС-СУБАРУ' && $key < 18)
		{
			$i_max = 20;
			$bg = 'style="background: #7f7;"';

		}
		else {
			if ($_POST['key']%2 == 0) { // Если артикул чётный, то закрашиваем строки серым
				$bg = 'style="background: #bbb;"';
			}
			else { // Если нечётный, не закрашиваем, так мы будем видеть разные артикулы наглядно
							$bg = '';
			}
		}
	
		$filed = $_POST['file']."_parse.xls";
		if($_POST['key'] == 0 && $key == 0)
		{
			if(file_exists($filed)) {
				unlink($filed); // Удаляем файл если такой уже существует
			}

			$start = '<table id="zzap" border="2">
			<thead>
					<th>#</th>
					<th>Артикул</th>
					<th>Продукт</th>
					<th>Бренд</th>
					<th>Обновлено</th>
					<th>Магазин</th>
					<th width="10%">Цена</th>
					<th>Наличие</th>
					<th>Срок доставки</th>
			</thead>
			';
		}
		else
		{
			unset($start);
			//$rez = file_get_contents($filed);
		}

		$oneArticle[price_date] = str_replace("T"," ",$oneArticle[price_date]);
		$number = $key+1;

		$rez = '<tr '.$bg.' ><td>'.$number.'</td><td>'.$oneArticle[partnumber].'</td><td>'.$oneArticle[class_cat].'</td><td>'.$oneArticle[class_man].'</td><td>'.$oneArticle[price_date].'</td><td>'.$oneArticle[class_user].'</td><td>'.$oneArticle[price].'<br>'.$arDetails[descr_price].'</td><td>'.$oneArticle[qty].'</td><td>'.$oneArticle[descr_qty].'</td></tr>
		';

		//file_put_contents($filed, $rez);
		$fileopen=fopen($filed, "a+");
		fwrite($fileopen, $start.$rez);
		fclose($fileopen);
	?>
	<script>
		<?if(!$oneArticle[partnumber]):?>
			var parseContent = '<tr><td></td><td>Превышена частота запросов</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
	<?else:?>
		var parseContent = '<tr <?=$bg?> ><td><?=$key+1?></td><td><?=$oneArticle[partnumber]?></td><td><?=$oneArticle[class_cat]?></td><td><?=$oneArticle[class_man]?></td><td><?=$oneArticle[price_date]?></td><td><?=$oneArticle[class_user]?></td><td><?=$oneArticle[price]?><br><?=$arDetails[descr_price]?></td><td><?=$oneArticle[qty]?></td><td><?=$oneArticle[descr_qty]?></td></tr>';
		<?endif;?>
		$('#zzap tbody').append(parseContent);
	</script>
	<?
		$i++;
		if($i == $i_max) // Если вывели количество товара равное максимальной глубине, останавливаемся
		{
			break;
		}

	endforeach;
}
else {
?>
	<script>
		var errorMessage = 'error';
	</script>
<?
	}
?>