<?php
namespace Teescripts\Helpers;
function upload_path($field, $uploads="", $key=0) {	
	$path	="";
	if (is_array($uploads)) {
		if (array_key_exists($field, $uploads)) {
			$array	=$uploads[$field];
			if (array_key_exists(1, $array)) {
				$array	=$array[1];
				if (array_key_exists(0, $array)) {
					$array	=$array[0];
					# ------ 
					if (array_key_exists($key, $array)) $path=$array[$key];
				}
			}
		}
	}
	return $path;
}

function upload_send($uploads="", $html="file", $num="") {
	$files	=[];
	if (is_array($uploads)) {
		# ------ 
		foreach ($uploads as $field=>$upload) {
			if (is_array($upload)) {
				$array	=array($field=>$upload);
				$file	=upload_postImage($array, $html, $num);
				if ($file) {
					if (strlen($num)) {
						$files[$field][$num]	=$file;
					}
					else {
						$files[$field]	=$file;
					}
				}
			}
		}	
	}
	return $files;
}

function upload_postImage($upload="", $html="", $num="") {
	if (is_array($upload)) {
		$types	=array('png', 'gif', 'jpg', 'jpeg');
		$column	=key($upload);
		$name	=$upload[$column][0];
		$paths	=$upload[$column][1];
		
		$fields	=explode(":", $column);			
		$field	=$fields[0];
		unset($fields[0]);	
		$post_image	=arrayKey($field, $_POST, []);
		$get_image	=arrayKey($field, $_FILES);
		if ($get_image) $get_image=arrayKey("name", $get_image);
		
		if (is_array($fields)) {
			foreach ($fields as $key=>$input) {
				if (array_key_exists($input, $get_image)) $get_image=$get_image[$input];
				if (array_key_exists($input, $post_image)) $post_image=$post_image[$input];
			}
		}
		
		if (is_array($post_image)) $post_image="";
		
		if ($paths) {
			if (!$name) $name=time();
			$name	=trim($name);
			$name	=str_replace(' ', '-', $name);
			$name	=preg_replace("/--+/", '-', $name);
			if (!is_array($paths)) $paths=array($paths);
			if (strlen($post_image)>20||$get_image) {
				$file	="";
				$name	=substr($name, 0, 250);
				if ($post_image) {
					$type	="jpg";
					$text	=$post_image;
					$split	=explode(';base64,', $post_image);
					if (count($split)>1) {
						$data	=arrayKey(0, $split);
						$text	=arrayKey(1, $split);
						$text	=base64_decode($text);
						$image	=str_replace('data:', '', $data);
						$type	=str_replace('image/', '', $image);
					}
					$is_image=(in_array($type, $types));
					if ($is_image) $file=upload_fileName($name, $name, $type);
				}
				foreach ($paths as $key=>$array) {
					$folder	=$array[0];
					if ($folder) {
						$size	=$array[1];
						if (!$size) $size=300;
						if (!is_dir($folder)) mkdir($folder, 0777, true);
						if ($post_image) {
							$path	=rtrim($folder, "/")."/".$file;
							$output	=@file_put_contents($path, $text);
							upload_process($path, $path, $size);
						}
						elseif($get_image) {
							$file	=upload_fileUp($column, $folder, $size, $name, $num);
						}
					}
				}
				return $file;	
			}
		}
		
	}
}

function upload_fileName($rename, $file="", $extension="") {	
	$filename	=$file;
	if (!$rename) $filename=time();
	if ($rename) $filename=$rename;
	$filename	=substr($filename, 0, 250);
	$filename	=$filename.".".$extension;
	
	$filename	=strtolower($filename);
	$filename	=str_replace(",", "_", $filename);
	$filename	=str_replace(" ", "-", $filename);
	$filename	=str_replace('?', "", $filename);
	$filename	=str_replace("'", "", $filename);
	$filename	=str_replace("&", "-", $filename);
	return $filename;
}

function upload_file_attr($column, $attr="", $no=0, $sub="") {
	$attrs	=explode(",", "name,tmp_name,type,error,size");
	$attr	=in_array($attr, $attrs)?$attr:$attrs[0];
	
	$fields	=explode(":", $column);	
	$field	=$fields[0];
	unset($fields[0]);
	$file	=$_FILES;
	if (array_key_exists($field, $file)) $file=$file[$field];
	if (is_array($column)) $file=$column;
	if (array_key_exists($attr, $file)) $file=$file[$attr];
	
	if (is_array($fields)) {
		foreach ($fields as $input) {
			if (array_key_exists($input, $file)) $file=$file[$input];
		}
	}
	if ($sub&&array_key_exists($sub, $file)) $file=$file[$sub];
	if ($no!=""&&array_key_exists($no, $file)) $file=$file[$no];
	return $file;
}

function upload_multiFile($field, $path="", $size="", $rename="", $sub="") {
	$array	=[];
	$files	=arrayKey($field, $_FILES);
	$post	=arrayKey($field, $_POST);
	if ($files||$post) {
		$result	=[];
		if ($files) $result=$files["name"];
		if ($post) $result=[$post];
		foreach ($result as $num=>$row) {
			$name	="{$rename} {$sub} {$num}";
			$upload	=[[$path, $size]];
			$config	=[$field=>[$name, $upload]];
			
			$file	=upload_postImage($config, "file", $num);
			if ($file) $array[]=$file;
		}
	}
	return $array;
}

function upload_fileUp($field, $path="", $size="", $rename="", $num="", $sub="") {		
	return upload_files($path, $field, $rename, $size, "file", $num, $sub);
}

function upload_files($destination, $file_field, $rename="", $size=300, $html="", $num="", $sub="") {
	$files	=upload_file_attr($file_field, "name", $num, $sub);
	if ($files) {
		if (is_array($files)) $files=$files[$num];
		if ($files) {
			$filename	=$files;
			$extension	=pathinfo($filename, PATHINFO_EXTENSION);#DIRNAME, BASENAME, FILENAME
			$extension	=strtolower($extension);
			#----------- 
			$extensions	=array("jpg", "gif", "jpeg", "png", "jpe");
			if(in_array($extension, $extensions)) {
				$result	=upload_resize($destination, $file_field, $rename, $size, $html, $num, $sub);
			}
			else {
				$result	=upload_copy($destination, $file_field, $rename, $html, $num, $sub);
			}
			return $result;
		}
	}
}
#------------------ end function

function upload_copy($destination, $file, $rename="", $html="", $num=0, $sub="") {
	global $files_allowed;

	$done	="";
	$name	=upload_file_attr($file, "name", $num, $sub);
	$source	=upload_file_attr($file, "tmp_name", $num, $sub);
	#------- 
	$extension	=pathinfo($name, PATHINFO_EXTENSION);#DIRNAME, BASENAME, FILENAME
	$extension	=strtolower($extension);
	#  --------- security check
	if (!$files_allowed) $files_allowed="pdf,doc,rtf,csv,xls,ppt";
	$extensions	=explode(",", $files_allowed);
	if(in_array($extension, $extensions)&&$name&&$source) {
		#----------- new file name
		$filename	=upload_fileName($rename, $name, $extension);
		#----------- new file path
		$new_path	=$destination."/".$filename;		
		#----------- upload file
		if ($source) copy($source, $new_path);		
		#----------- resize file
		if ($done) {
			$text	='File <strong>'.$filename."</strong> successfully uploaded!";
		}
		else {
			$text	='<i class="fad fa-ban error"></i> Sorry, file <strong>'.$filename."</strong> failed to upload!";
		}
	}
	else {
		$text	="Errors found in file";
		if ($name&&$source) $text='<i class="fad fa-ban error"></i> This extension (<strong>'.$extension."</strong>) is not allowed for upload!";		
	}
	
	$return	=strip_tags($text);
	if ($html=="hide") $return="";
	if ($html=="file") $return=$filename;
	if ($html=="html") $return=$text;
	return $return;
#  ---------------- end sfx  -----------------------------------
}

function upload_resize($destination, $file_field, $rename="", $size="", $html="", $num="", $sub="") {
	global $watermark;

	$name	=upload_file_attr($file_field, "name", $num, $sub);
	$source	=upload_file_attr($file_field, "tmp_name", $num, $sub);
	if (is_array($name)&&strlen($num)) {
		$name	=$name[$num];
		$source	=$source[$num];
	}
	
	$extensions	=array("jpg","gif","jpeg","png","jpe");
	$extension	=pathinfo($name, PATHINFO_EXTENSION);#DIRNAME, BASENAME, FILENAME
	$extension	=strtolower($extension);
	if(in_array($extension, $extensions)&&$name&&$source) {
		#----------- new file name
		$filename	=upload_fileName($rename, $name, $extension);
		#----------- new file path
		$new_path	=$destination."/".$filename;		
		#----------- upload file
		if ($source) copy($source, $new_path);		
		#----------- resize file
		upload_process($new_path, $new_path, $size);
	}
	#  ----------
	if(is_file($new_path)) {
		$text	='<a href="'.$new_path.'" title="'.$filename.'" rel="ibox">';
		$text	.='<img src="'.$new_path.'" alt="'.$filename.'" style="max-height:50px" border="0" /></a>';
		if ($watermark) upload_watermark($new_path, $watermark, $new_path);
	}
	else {
		$error	=upload_file_attr($file_field, "error", $num, $sub);
		$text	="failed to upload and resize!";
		if ($error) $text=$error;
		if ($name&&$source) $text="not uploaded, (<strong>$extension</strong>s) are not allowed!";
		$text	='<i class="fad fa-ban error"></i> Sorry, file <strong>'.$name.'</strong> '.$text.'';
	}
	
	$return	=strip_tags($text);
	if ($html=="hide") $return="";
	if ($html=="file") $return=$filename;
	if ($html=="html") $return=$text;
	return $return;
}

function upload_imageCreate($path) {
	$ext	=pathinfo($path, PATHINFO_EXTENSION);
	$ext	=strtolower($ext);
	# imagecreatefrom{$ext}: bmp,xbm,wbmp';
	if ($ext=="png") {
		$image	=imagecreatefrompng($path);
	}
	elseif ($ext=="gif") {
		$image	=imagecreatefromgif($path);
	}
	else {
		$image	=imagecreatefromjpeg($path);
	}
	return $image;
}

function upload_dimension($size) {
	if (!is_array($size)) {
		$size	=str_replace("px", "", $size);
		$size	=str_replace("x", "*", $size);
		$size	=str_replace(" ", "", $size);
		$size	=str_replace(",", "", $size);
		$size	=explode("*", $size);
	}
	return $size;
}

function upload_process($source, $path, $size) {
	if (is_file($source)&&$path) {
		$image_size	=getimagesize($source);
		$width	=arrayKey(0, $image_size);
		$height	=arrayKey(1, $image_size);

		$size	=upload_dimension($size);
		$size_x	=arrayKey(0, $size);
		$size_y	=arrayKey(1, $size);
		
		$ratio	=($width / $height);
		
		if ($size_y) {
			if ($width > $height) {
				$modwidth	=($size_x * $ratio);
				$modheight	=$size_x;
			}
			else {
				$modwidth	=$size_x;
				$modheight	=($size_x / $ratio);
			}
		}
		else {
			if ($width > $height) {
				$modwidth	=$size_x;
				$modheight	=($size_x / $ratio);
			}
			else {
				$modwidth	=($size_x * $ratio);
				$modheight	=$size_x;
			}
		}
		
		$resizeable	=($width!=$modwidth || $height!=$modheight);
		if ($resizeable) {
			$ext	=pathinfo(strtolower($source), PATHINFO_EXTENSION);
			$thumb	=imagecreatetruecolor($modwidth, $modheight);
			# maintain transparency
			if (in_array($ext, ["png", "gif", "webp"])) {
				# create alpha in transparency
				$color	=imagecolorallocatealpha($thumb, 255, 255, 255, 127);#0-127
			}
			else {
				# create white bg in transparency
				$color	=imagecolorallocate($thumb, 255, 255, 255);
			}
			imagefill($thumb, 0, 0, $color);
			# ------ create
			$image	=upload_imageCreate($path);
			# ------ resize
			imagecopyresampled($thumb, $image, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);
				
			# ------ crop
			if ($size_y) {
				$point_x	=0;
				$point_y	=0;
				if ($modwidth > $size_x) $point_x=($modwidth - $size_x) / 2;
				if ($modheight > $size_y) $point_y=($modheight - $size_y) / 2;
				$thumb	=imagecrop($thumb, ['x' => $point_x, 'y' => $point_y, 'width' => $size_x, 'height' => $size_y]);
			}

			# -------- 
			if ($ext=="png") {
				imagepng($thumb, $path);
			}
			elseif ($ext=="gif") {
				imagegif ($thumb, $path);
			}
			else {
				imagejpeg($thumb, $path);
			}
			imagedestroy($thumb);

		}
	}
}

function upload_watermark($SourceFile, $WaterMarkText, $DestinationFile) {
	list($width, $height)=getimagesize($SourceFile);
	$thumb	=imagecreatetruecolor($width, $height);
	$image	=imagecreatefromjpeg($SourceFile);
	imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $width, $height);
	$black	=imagecolorallocate($thumb, 255, 255, 255);
	$font	='public/fonts/economica-regular-webfont.ttf';
	$font_size=12;
	$posx	=($height-2);
	$posy	=2;
	imagettftext($thumb, $font_size, 0, $posy, $posx, $black, $font, $WaterMarkText);
	if ($DestinationFile!="") {
		imagejpeg($thumb, $DestinationFile, 100);
	}
	else {
		header('Content-Type: image/jpeg');
		imagejpeg($thumb, null, 100);
	}
	imagedestroy($image);
	imagedestroy($thumb);
}

function upload_decodeImage($content="")	{
	$split	=explode(';base64,', $content);
	$text	=arrayKey(1, $split);
	if ($text) $content=base64_decode($text);
	return $content;
}

function upload_encodeImage($filename="", $exist="") {
	//if (!is_file($filename)) $filename=pathinfo($_SERVER["SCRIPT_FILENAME"], PATHINFO_DIRNAME).'/'.$filename;
	$text	="";
	if (is_array($filename)) $filename=implode("", $filename);
	if (is_file($filename) || $exist) {
		//$contents	=fread(fopen($filename, "r"), filesize($filename));
		$contents	=file_get_contents($filename);
		if ($contents) {
			$type	=main_txt(".", $filename, "ext");
			$text	='data:image/'. $type .';base64,'. base64_encode($contents);
		}
		else {
			if ($exist) $text=$filename;
		}
	}
	return $text;
}

function upload_render($contents, $file_path="") {
	if (!$file_path) $file_path=main_appName()."_download_".date("HmsA").".jpg";
	
	$file	=pathinfo($file_path);
	$basename	=arrayKey("basename", $file);
	$filename	=arrayKey("filename", $file, $basename);
	$extension	=arrayKey("extension", $file);
	if (!$extension||$extension==$filename||strlen($extension)>4) $extension="jpg";
	$extension	=strtolower($extension);

	header("Content-type: ".upload_type($extension));
	header("Content-Disposition: attachment;Filename=\"{$file_path}\"");
	header("Cache-Control: private");
	if (!$contents&&is_file($file_path)) {
		header("Content-Length: ".filesize($file_path));
		$buffer	=8*1024;
		$handle	=fopen($file_path, "rb");
		while (!feof($handle)) {
			$contents	.=fread($handle, $buffer);
		}
		fclose($handle);
	}
	echo $contents;
	exit;
}

function upload_type($extension) {
	$extension	=".{$extension}";
	switch ($extension) {
		case ".csv":
			$ctype = "application/csv";
			break;
		case ".asf":
			$ctype = "video/x-ms-asf";
			break;
		case ".avi":
			$ctype = "video/avi";
			break;
		case ".doc":
			$ctype = "application/msword";
			break;
		case ".docx":
			$ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
			break;
		case ".zip":
		case ".rar":
		case ".gzip":
		case ".tar":
		case ".tar.biz":
			$ctype = "application/zip";
			break;
		case ".xlsx":
			$ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
			break;
		case ".xls":
			$ctype = "application/vnd.ms-excel";
			break;
		case "":
		case ".":
		case ".folder":
			$ctype = "directory/folder";
			break;
		case ".jpg":
		case ".jpeg":
			$ctype = "image/jpeg";
			break;
		case ".gif":
			$ctype = "image/gif";
			break;
		case ".png":
			$ctype = "image/png";
			break;
		case ".bmp":
			$ctype = "image/bmp";
			break;
		case ".wav":
			$ctype = "audio/wav";
			break;
		case ".mid":
			$ctype = "audio/mid";
			break;
		case ".mp3":
			$ctype = "audio/mpeg3";
			break;
		case ".mid":
			$ctype = "audio/mid";
			break;
		case ".mp2":
			$ctype = "audio/mp2";
			break;
		case ".ord":
			$ctype = "audio/ord";
			break;
		case ".dat":
			$ctype = "video/dat";
			break;
		case ".vob":
			$ctype = "video/vob";
			break;
		case ".vid":
			$ctype = "video/vid";
			break;
		case ".swf":
			$ctype = "video/swf";
			break;
		case ".flv":
			$ctype = "video/flv";
			break;
		case ".mp4":
			$ctype = "video/mpeg4";
			break;
		case ".wmv":
			$ctype = "video/wmv";
			break;
		case ".3gp":
			$ctype = "video/3gpp";
			break;
		case ".ogv":
			$ctype = "video/ogg";
			break;
		case ".webm":
			$ctype = "video/webm";
			break;
		case ".mpg":
		case ".mpeg":
			$ctype = "video/mpeg";
			break;
		case ".rtf":
			$ctype = "application/rtf";
			break;
		case ".pdf":
			$ctype = "application/pdf";
			break;
		case ".html":
		case ".htm":
			$ctype = "text/html";
			break;
		case ".php":
			$ctype = "text/php";
			break;
		case ".asp":
			$ctype = "text/asp";
			break;
		case ".xml":
			$ctype = "text/xml";
			break;
		case ".":
			$ctype = "application/octet-stream";
			break;
		case ".txt":
			$ctype = "text/plain";
			break;
		case ".sql":
			$ctype = "text/sql";
			break;
		case ".css":
			$ctype = "text/css";
			break;
		case ".bat":
			$ctype = "application/batch file";
			break;
		case ".exe":
			$ctype = "application/executable";
			break;
		case ".dll":
			$ctype = "application/dll";
			break;
		case ".msi":
			$ctype = "application/executable";
			break;
		default:
			$ctype = "application/unspecified";
			break;
	}
	return $ctype;
}
