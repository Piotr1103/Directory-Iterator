<?php
	session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>檔案管理</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"></link>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/jquery.smartmenus/1.1.0/addons/bootstrap/jquery.smartmenus.bootstrap.min.css" rel="stylesheet"></link>
		<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<style>
			table,th,td{
				text-align: center;
			}
			.contshower{
				text-align: left;
			}
		</style>
	</head>
	<script>
		$(document).ready(function(){
			var whereami = '.';

			//用來取得當前位置的所有目錄
			function dirLoad(whereami){
				$.ajax({
					type: 'GET',
					//whereami紀錄當前位置，傳予ajax作為搜尋當前目錄下的所有目錄的參數
					url: 'ajax.php?dirs=' + whereami,
					success: function(e){
						let where = '';
						if(whereami=='.'){
							/*當從多層直接返回根目錄時，whereami等於"."
							而根目錄按鈕id是root，id選擇器會定位不到，所以需要賦值為root*/
							whereid = 'root';
						}else{
							//當位置不在根目錄上時，將檔案名稱從路徑中分離出來，作為選擇器的id
							whereid = whereami.split('/').pop();
						}
						//每次更新目錄列表時，優先加上根目錄按鈕
						$('#dirs').empty().append('<p id="operator"><input type="button" value="根目錄" id="root" class="btn btn-primary" data-dir="." /></p>&nbsp;');
						//當目前位置不在根目錄上時，出現一個回上層的按鈕
						if(whereami!='.'){
							//當前位置的上一層位置
							let up = whereami.split('/').slice(0,-1).join('/');
							$('#operator').append('&emsp;<input type="button" value="回上層" id="up" class="btn btn-success" data-dir="' + up + '" />');
						}
						//將取得的所有目錄添加到id為dirs的div中
						$('#dirs').append(e);
					}
				});
			}

			//讓ajax查詢所欲查詢的目錄中的檔案並加載到tableshow中
			function contLoad(whoami){
				$.ajax({
					type: 'GET',
					url: 'ajax.php?f=' + whoami,
					dataType: 'html',
					error: function(e){
						console.log(e);
					},
					success: function(e){
						$('#tableshow').empty();
						$('#tableshow').append(e);
					}
				});
			}

			dirLoad(whereami);
			contLoad(whereami);

			//當目錄鈕被按下時，取得新位置下的所有檔案和子目錄
			$('#dirs').on('click','.btn-success,.btn-primary',function(){
				let whoami = whereami = $(this).data('dir');
				dirLoad(whereami);
				contLoad(whoami);
			});

			//檔案更名，改完之後重新取得檔案列表和目錄列表
			$('#tableshow').on('click','.btn-warning',function(){
				let whoami = $(this).data('file');
				let cut = whoami.split('/');
				//用來存放路徑
				let fp = '';
				//用來存放純檔名
				let fn = '';
				//取得純檔名
				fn = cut.pop();
				//取得路徑前綴數組並且合併
				fp = cut.join('/') + "/";
				let rn = prompt("請輸入" + fn + "的新檔名。\n不包含路徑名，但需要指定副檔名。\n\n檔名本體不要包含空格和/ \\ < > * ？喔！\n漢字檔名視環境不同可能有出錯的危險，請斟酌。");
				if(!rn || rn==""){
					return;
				}else{
					$.ajax({
						type: 'GET',
						url: ('ajax.php?on=' + whoami + "&rn=" + fp + rn),
						success: function(e){
							alert(e);
							dirLoad(whereami);
							contLoad(whereami);
						}
					});
				}
			});

			//刪除檔案或目錄
			$('#tableshow').on('click','.btn-danger',function(){
				let whoami = $(this).data('file');
				let del = confirm("確定要刪除" + whoami + "嗎？");
				if(!del){
					console.log(del);
					return;
				}else{
					$.ajax({
						type: 'GET',
						url: ('ajax.php?dn=' + whoami),
						success: function(e){
							alert(e);
							dirLoad(whereami);
							contLoad(whereami);
						}
					});
				}
			});

			$('#mkdir').on('click',function(e){
				let $dir = $('#dirname').val();
				let path = whereami + "/" + $dir;
				$.ajax({
					type: 'GET',
					url: ('ajax.php?md=' + path),
					success: function(e){
						alert(e);
						dirLoad(whereami);
						contLoad(whereami);
						$('#dirname').val('');
					}
				});
			});

			$('#mkfile').on('click',function(e){
				let $dir = $('#filename').val();
				let path = whereami + "/" + $dir;
				$.ajax({
					type: 'GET',
					url: ('ajax.php?mf=' + path),
					success: function(e){
						alert(e);
						dirLoad(whereami);
						contLoad(whereami);
						$('#filename').val('');
					}
				});
			});

		});
	</script>
	<body>
		<div class="container">
			<div class="row col-md-12" align="center">
				<h2>站內目錄</h2>
			</div>
			<div class="row col-md-12">
				<div align="center" id="dirs">
					<p id="operator"><input type="button" value="根目錄" id="root" class="btn btn-primary" data-dir="." /></p>&nbsp;
					<!--
						每個目錄按鈕的value和id都是其當下的名字，不包含路徑。
						而data-dir屬性則含有路徑，方便設計回上層功能取得上一層。
					-->
				</div>
			</div>

			<div class="row col-md-12" align="center">
				<h2>站內檔案</h2>
			</div>
			<div class="row col-md-12">
				<div align="center">
					<form action="" method="post" accept-charset="utf-8">
						<label for="dirname">創建目錄： </label>
						<input type="text" name="dirname" id="dirname">
						<input type="button" value="創建目錄" class="btn btn-info" id="mkdir">
						<br>
						<label for="dirname">創建檔案： </label>
						<input type="text" name="filename" id="filename">
						<input type="button" value="創建檔案" class="btn btn-info" id="mkfile">
						<br><br>
						<table class="table">
							<thead>
								<th>#</th>
								<th>檔名</th>
								<th></th>
							</thead>
							<tbody id="tableshow"></tbody>
						</table>
					</form>
				</div>
			</div>

		</div>
	</body>
</html>