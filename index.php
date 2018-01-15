<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Парсинг сайта zzap.ru");
?>
<?global $USER;
if ($USER->IsAdmin()):?>
<script>
function parseZZAP(article, key, filename) { // Парсинг сайта zzap.ru согласно артикулам из массива arArticles[i], подгрузка с помощью AJAX порциями
	$.ajax({
			url: "parseZZAP.php",
			data: "article="+article+"&key="+key+"&file="+filename, // key нужен для определения чётный ли артикул, чтобы закрашивать его
			dataType: "html",
			type: "POST",
			success: function(data, textStatus){
				var articlesCount = arArticles.length; // Количество артикулов
				var progressbar = ((key+1)/articlesCount)*100;
				progressbar = progressbar.toFixed(2);
				$('#content').append(data);
				$('#progress-bar-percents').css('width', progressbar+'%');
				$('#progress-bar-number').html(Math.round(progressbar)+' %');
				if(progressbar == 100)
				{
					$('#progress-bar-number').html('Импорт завершен!');
					$('#wait').remove();
				}
				$(".art_complited").html(key+1);
			},
			error: function(jqXHR, textStatus, errorThrown){ // Если ошибка, то выкладываем печаль в консоль
				console.log('Error: '+ errorThrown);
			}
	});
}
	<?if($_FILES["file"]["name"]):?>
		var filename = '<?=$_FILES["file"]["name"]?>';
		var errorMessage;
	<?endif;?>
function repeat_import(file) { // Эта функция достаёт артикулы и записывает их в массив, после этого запускает функцию parseZZAP
	$.ajax({
			url: "xlsPHP.php",
			type: "POST",
			data: "file="+file, // key нужен для определения чётный ли артикул, чтобы закрашивать его
			timeout: 50000,
			success: function(data, textStatus){
				$("#content__articles").html( data );	// Список артикулов
				$("#content").append('<p>Количество артикулов: <b>'+arArticles.length+'</b></p>');
				$("#content").append('<p id="">Обработано: <b class="art_complited">0</b></p>');
		$("#content").append('<p id="">Скачать: <a target="_blank" href="<?=$_FILES["file"]["name"]?>_parse.xls"><?=$_FILES["file"]["name"]?>_parse.xls</a> <span id="wait">(дождитесь полной обработки)</span></p>');
			},
			complete: 
				function(xhr, textStatus){
					// Запускаем процесс парсинга
					var delay=3500; // Разрешено 1 запрос в 3 секунды
					var step = 6100; // Но нужно чуть больше 3 секунд делать делэй, иначе будет чуть быстрее
					for (var i=0, len=arArticles.length; i<len; i++) {
						if(errorMessage == 'error')
						{
							i--; 
						}
						else {
							setTimeout(parseZZAP, delay, arArticles[i], i, file); // Запускаем парсинг с задержкой
						}
						delay=delay+step;
					}
				}
	});
}

$(document).ready(function() {
	$('.hidetable').on('click', function() {
		$('table#zzap').toggleClass('dnone');
	});
});
</script>
<?
/* ЗАГРУЗКА ФАЙЛА НА СЕРВЕР */
	if($_FILES)
	{
		$uploaddir = '/home/c/ct75745/public_html/zzap/';
		$uploadfile = $uploaddir.basename($_FILES['file']['name']);
		// Копируем файл из каталога для временного хранения файлов:
		if (copy($_FILES['file']['tmp_name'], $uploadfile))
		{
			echo "<h3>Файл успешно загружен на сервер</h3>";
		}
		else { echo "<h3>Ошибка! Не удалось загрузить файл на сервер!</h3>"; }
	}
/* ЗАГРУЗКА ФАЙЛА НА СЕРВЕР */
?>
<div class="zzap_parsing_wrapper">
	<h1>Импорт прайс-листа</h1>
		<?if($_FILES["file"]["name"]):?>
			Подождите завершения импорта, не закрывайте данную страницу!
			<input type="button" class="hidetable" value="Скрыть таблицу" />
			<div id="progress-bar">
				<div id="progress-bar-number"></div>
				<div id="progress-bar-percents" style="width: 0%;"></div>
			</div>
		<?else:?>
	<h3>Загрузите артикулы в формате <b>*xlsx</b>:</h3>
		<form action="" method="post" id="upload_file" enctype="multipart/form-data">
			<input id="uploadile" type="file" name="file" placeholder="Выберете файл" /><br>
			<input type="submit" value="Загрузить" />
		</form>
		<?endif;?>
		<div id="content">
		</div> 
		<div style="display: none;">
			<div id="content__articles" style="display: none;"></div>
		</div>

	<div class="table_wrapper">
	<table id="zzap" border="2">
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
		<tbody>
			<? // Место импорта данных ?>
		</tbody>
	</table>
	</div>
	<?if($_FILES["file"]["name"]): // Если нажали кнопку, запускаем процесс ?>
		<script>
		$(function (){
			repeat_import('<?=$_FILES["file"]["name"]?>'); // Достаём из файла все артикулы и заносим в массив
		});
		</script>
	<?endif;?>
</div>
<style>
	#zzap { 
		width: 100%;
		border-color: #262626;
		border-width: 2px;
		background: #fff;
		font-size: 16px;
		color: #262626;
		font-family: 'PFDinCondensedMedium';
	}
	#zzap tr td {
		padding: 2px 10px;
	}
	#zzap thead {
		background: #fff;
		font-size: 20px;
	}

	#progress-bar {
		height: 40px;
		background: #fff;
		width: 100%;
		border: 2px solid #bbb;
		float: left;
	}

	#progress-bar-percents {
		background: #7f7;
		float: left;
		width: 0%;
    	height: 100%;
	}

	#progress-bar-number {
		position: absolute;
		font-weight: 400;
		margin: auto;
		font-family: 'PFDinCondensedMedium';
		width: 100%;
		text-align: center;
		height: 40px;
		line-height: 2.2;
	}
	.dnone {
		display: none !important;
	}
	.hidetable {
		float: right;
	}
	.zzap_parsing_wrapper input {
		padding: 5px 15px;
		margin: 10px 0;
	}
</style>
<?else:?>
	<h1>Нет доступа</h1>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>