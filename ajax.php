<?php
	//由mng_file.php調用，以表格列形式返回傳入路徑f所有的檔案和目錄
	if(isset($_REQUEST['f'])){
		$dir = $_REQUEST['f'];
		$subdir = glob($dir.'/*');
		$tbody = '';
		$count = 0;
		foreach($subdir as $file){
			$count++;
			$filename = end(explode('/',$file));
			if(is_dir($file)){
				$filename = '<font color="red">'.$filename.'</font>';
			}
			$tr = <<<EOF
<tr>
	<td>$count</td>
	<td>$filename</td>
	<td>
		<input type="button" value="刪除" class="btn btn-danger" data-file="$file" />
		<input type="button" value="更名" class="btn btn-warning" data-file="$file" />
	</td>
</tr>
EOF;
			$tbody.=$tr;
		}
		echo $tbody;
	}

	//由mng_file.php調用，以按鈕形式返回傳入路徑dirs所有的目錄
	if(isset($_REQUEST['dirs'])){
		$dirs = glob($_REQUEST['dirs']."/".'*',GLOB_ONLYDIR);
		$dr = 0;
		foreach($dirs as $dir){
			$dirval = end(explode('/',$dir));
			$dr++;
			$buttons = <<<EOF
<input type="button" value="$dirval" id="$dirval" class="btn btn-success" data-dir="$dir" />&emsp;
EOF;
			if($dr==4){
				$buttons.='<br><br>';
				$dr = 0;
			}
			echo $buttons;
		}
	}

	//由mng_file.php調用，用來修改檔名
	if(isset($_REQUEST['on']) && $_REQUEST['rn']!=""){
		$judge = file_exists($_REQUEST['rn']);
		if($judge){
			echo '該名稱已存在！';
		}else{
			rename($_REQUEST['on'],$_REQUEST['rn']);
			echo '修改成功';
		}
	}

	//刪除多層目錄時調用的函數
	function delMultiDir($dir){
		//wamp環境下需要修改權限為0600才能刪除目錄
		//byethost環境下則需要修改權限為0755才能刪除目錄
		chmod($dir,0755);
		//若該目錄或檔案不存在則返回，否則判斷是否是目錄
		//不是目錄則直接用unlink函數刪除
		if(!file_exists($dir)){
			return;
		}elseif(!is_dir($dir)){
			return unlink($dir);
		}
		//若判斷是目錄則讀取其內容
		foreach(scandir($dir) as $item){
			//不刪除當前目錄和父目錄
			if($item=='.'||$item=='..'){
				continue;
			//以遞歸的方式刪除目錄下的內容，刪除完後回歸
			}elseif(!delMultiDir($dir.DIRECTORY_SEPARATOR.$item)){
				return;
			}
		}
		return rmdir($dir);
	}

	//由mng_file.php調用，用來刪除檔案和目錄
	if(isset($_REQUEST['dn'])){
		if(is_dir($_REQUEST['dn'])){
			delMultiDir($_REQUEST['dn']);
			echo '目錄刪除成功';
		}else{
			unlink($_REQUEST['dn']);
			echo '檔案刪除成功';
		}
	}

	//創建多層目錄時調用的函數
	function mkMultiDir($dir){
		$path = explode('/',$dir);
		//若傳入的路徑包含了當前目錄，為避免錯誤將其移除
		if($path[0]=='.'){
			array_shift($path);
		}
		//用來存儲當前創建目錄層級的字串
		$cur = './';
		for($i=0;$i<count($path);$i++){
			$cur .= $path[$i]."/";
			//若欲創建的目錄在當前層級中已經存在，則跳到下一層繼續創建
			if(file_exists($cur)){
				continue;
			}else{
				mkdir($cur);
				chmod($cur,0755);
			}
		}
	}

	//由mng_file.php調用，用來創建目錄
	if(isset($_REQUEST['md'])){
		if(file_exists($_REQUEST['md'])){
			echo '該目錄已存在！';
		}else{
			mkMultiDir($_REQUEST['md']);
			echo '目錄創建成功';
		}
		
	}

	//由mng_file.php調用，用來創建檔案
	if(isset($_REQUEST['mf'])){
		if(file_exists($_REQUEST['mf'])){
			echo '該檔案已存在！';
		}else{
			touch($_REQUEST['mf']);
			//byethost環境下默認權限為0644
			chmod($_REQUEST['mf'],0644);
			echo '檔案創建成功';
		}
		
	}
?>