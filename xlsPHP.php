<script>
	var arArticles = []; // Тут Массив под артикулы
</script>
<?
function getXLS($xls){
	include_once 'PHPExcel.php';
	$objPHPExcel = PHPExcel_IOFactory::load($xls);

	$objPHPExcel->setActiveSheetIndex(0);
	$aSheet = $objPHPExcel->getActiveSheet();

	//этот массив будет содержать массивы содержащие в себе значения ячеек каждой строки
	$array = array();
	//получим итератор строки и пройдемся по нему циклом
	foreach($aSheet->getRowIterator() as $row){
	  //получим итератор ячеек текущей строки
	  $cellIterator = $row->getCellIterator();
	  //пройдемся циклом по ячейкам строки
	  //этот массив будет содержать значения каждой отдельной строки
	  $item = array();
	  foreach($cellIterator as $cell){
		//заносим значения ячеек одной строки в отдельный массив
		  //array_push($item, iconv('utf-8', 'cp1251', $cell->getCalculatedValue()));
		  array_push($item, iconv('utf-8', 'utf-8', $cell->getCalculatedValue()));
	  }
	  //заносим массив со значениями ячеек отдельной строки в "общий массв строк"
	  array_push($array, $item);
	}
	return $array;
}

$xlsData = getXLS($_POST['file']); //извлеаем данные из XLS

/* Обработка полученных данных из файла */
	$arArticles = array();
	$delay = 0;
	foreach($xlsData as $key=>$Article):
		$arArticles[$key] = $Article[1]; // Артикул чисто
		$delay = $delay + (3*1000);
		?>

		<script>
			arArticles[<?=$key?>] = '<?=$Article[1]?>'; // Записываем артикул в массив JS
			$('#content__articles').append('<?=$Article[1]?>;');
		</script>

		<?
			if($Article[1] == '' || !$Article[1]) // Если вывели количество товара равное максимальной глубине, останавливаемся
			{
				break;
			}
		endforeach;
	?>
		<script>
			setTimeout(function() {$('#content').append('<?=$Article[1]?>;');}, <?=$delay?>);
			//$('#content').append('<?=$Article[1]?>;');
		</script>
		<?
/* Обработка полученных данных из файла */
?>