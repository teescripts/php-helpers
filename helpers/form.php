<?php
namespace Teescripts\Helpers;

function form_encoding($value) {
	$value	=html_entity_decode($value, ENT_HTML401, "UTF-8");#ENT_HTML5
	return $value;
}

function form_br($nline1="", $tab="", $other=[]) {
	$text	="";
	if (is_numeric($nline1)) $text.=str_repeat("\r\n", intval($nline1));
	if (is_numeric($tab)) $text.=str_repeat("\t", intval($tab));
	if (is_array($other) && $other) {
		foreach ($other as $tag=>$times) {
			if ($tag && is_numeric($times)) $text.=str_repeat($tag, intval($times));
		}
	}
	return $text;
}
    
function form_list($text) {
	if (is_array($text)) {
		$array =$text;
	}
	elseif (is_object($text)) {
		$text  =json_encode($text);
		$array  =json_decode($text, true);
	}
	else {
		if (strstr($text, "list_")) {
			$value =varKey($text);
			if (!$value) {
				$name   =str_replace("list_", "", $text);
				$array  =list_loadArray($name);
			}
		}
		else {
			$array  =main_arrayConvert($text, "list");
		}
	}
	return $array;
}

function form_value($name, $value, $default="") {
	$value	=main_value($value, $default);
	$value	=old($name, $value);
	return $value;
}

function form_input($type="text", $name="", $value="", $default="", $attrib="", $validate="", $label="") {
	$spa_type   =constKey("app.spa_type");
	$text	="";
	#$values	=form_list($value);
	$dvalue	=arrayKey($name, $default, $default);
	$tags	=form_attrib($type, $name, $label, $attrib, $validate);
	$holder	=arrayKey("placeholder", $tags);
	if ($type=="select") {
		$exclude	='type,value,placeholder';
		$option	="";
		if ($holder!="none") $option='<option value="" class="placeholder" disabled="disabled" selected="selected"> -- '.$holder.' </option>';
		$option	.=form_options($value, $dvalue);
		$option	=str_replace('<option', form_br(1, 7).'<option', $option);
		$text	='<select [attrib]>'.$option.'</select>';
	}
	elseif ($type=="textarea") {
		$exclude	='type,value';
		if ($holder=="none") $exclude.=',placeholder';

		$value	=form_value($name, $value, $dvalue);
		$value	=form_encoding($value);
		$text	='<textarea [attrib]>'.$value.'</textarea>';
	}
	else {
		$type1	=["checkbox", "radio"];
		$type2	=["file", "hidden"];
		
		if (in_array($type, $type1)) {
			$name	=$tags["name"];
			$exclude	='type,name,id,placeholder';
			$text	=form_br(1, 3).form_options($value, $dvalue, $type, $name, $attr_text);
		}
		else {
			$exclude	="";
			if (in_array($type, $type2) || $holder=="none") $exclude='placeholder';

			if ($type=="file" && $spa_type=="livewire") {
				$text	='				
				@if ($'.$name.') 
				<div class="mask">
					<img src="{{ $'.$name.'->temporaryUrl() }}" class="wd-150 img-preview mask-squiggle" />
				</div>
				@endif'."\n\n\t";
			}

			$value	=form_value($name, $value, $dvalue);
			$value	=form_encoding($value);
			$text	.='<input [attrib] value="'.$value.'" />';
		}
	}
	if ($spa_type=="inertia") $spa_type."\n\t".'<InputError :message="form.errors.'.$name.'" class="mt-2" />';
	if ($spa_type=="livewire") $spa_type."\n\t".'<x-input-error :messages="$errors->get(\''.$name.'\')" class="mt-2" />';

	$attrib	=form_tagText($tags, $exclude);
	$text	=str_replace('[attrib]', $attrib, $text);
	if ($text) $text=form_br(1, 3).$text;
	return $text;
}

function form_button($label="Submit", $array=[]) {
	$get_type   =constKey("app.spa_type");

	$type    =arrayKey("type", $array, "button");
	$name    =arrayKey("name", $array, "send");
	$class    =arrayKey("class", $array, "btn bg-gradient-dark btn-md mb-4");
	$label   =trans($label);
	$inertia   =arrayKey("inertia", $array);
	$livewire  =arrayKey("livewire", $array);

	$inertia	=($inertia || $get_type=="inertia");
	$livewire	=($livewire || $get_type=="livewire");

	if ($inertia) {
		$text	='<Button class="'.$class.'">'.$label.'</Button>';
	}
	elseif ($livewire) {
		$text	='
		<x-button class="'.$class.'">
			'.$label.' 
			<span wire:loading>
				<i class="fad fa-spinner fa-spin"></i>
			</span>
		</x-button>';
	}
	else {
		$text	='<button type="'.$type.'" name="'.$name.'" class="'.$class.'">'.$label.'</button>';
	}
	return $text;
}

function form_field($type="text", $name="", $value="", $class="", $id="", $attrib="", $validate="", $return="") {
	if ($class) $attrib.=' class="'.$class.'"';
	if ($id) $attrib.=' id="'.$id.'"';
	
	$default="";
	$values	=explode("*", $value);
	$value	=$values[0];
	if (array_key_exists(1, $values)) {
		$default=$value;
		$value	=$values[1];
	}
	$input	=form_input($type, $name, $value, $default, $attrib, $validate, $name);
	if ($return) {
		return $input;
	}
	else {
		echo $input;
	}
}

function form_option($key, $label, $default="", $type="select", $name="", $extras="") {
	global $break;
	$default=str_replace(";", ",", $default);
	$dvalue	=explode(",", $default);
	$optid	=form_cleanAttr($name.$key);
	$state	=($type=="select")?"selected":"checked";
	if (in_array($key, $dvalue)) $extras.=" {$state}";

	$value	=form_value($name, $key, $default);
	$value	=form_encoding($value);
	
	if ($name && $type!="select") {
		$text	='<input type="'.$type.'" name="'.$name.'" value="'.$value.'" id="'.$optid.'" '.$extras.' /> <label class="'.varKey("{$type}_label").'" for="'.$optid.'">'.$label.'</label>'.$break;
	}
	else {
		$text	='<option value="'.$value.'" '.$extras.'>'.$label.'</option>';
	}
	return $text;
}

function form_options($results, $default="", $type="select", $name="", $extras="") {
	$option	=[];
	$key	=$default;
	$value	=$default;
	if (!$results && $default) $results=$default;
	if (is_array($results)) {
		$array	=arrayKey(0, $results);
		if ($array) {
			$keys	=array_keys($array);
			$key	=array_shift($keys);
			$value	=$keys[0];
		}
	}
	else {
		if (!is_array($results)) {
			$list_name	=$results;
			$list_name	=str_replace("list_", "lists/array/", $list_name);
			$list1	=stristr("/{$list_name}", '/lists/');
			$list2	=stristr("/{$list_name}", '/static/');
			$list3	=stristr($list_name, 'form_store');
			if ($list1 || $list2 || $list3) {
				$results	=load_lists($list_name);
			}
		}

		$key	="id";
		$value	="name";
		if (!is_array($results)) $results=main_toArray($results, 1);
	}
	$array_list	=form_data($results, $key, $value, $default, $type, $name, $extras);
	return $array_list;
}

function form_data($results, $key_column, $value_column="", $default="", $type="", $name="", $extras="") {
	if (!$value_column) $value_column=$key_column;
	if (!is_array($results)) $results=db_query($results);
	$option	=[];
	if (is_array($results)) {
		foreach ($results as $key=>$row) {
			$row_key	=arrayKey($key_column, $row);
			if ($row_key) {
				$row_label	=arrayKey($value_column, $row, $row_key);
			}
			else {
				$row_key	=$key;
				$row_label	=$row;
			}
			$option[]	=form_option($row_key, $row_label, $default, $type, $name, $extras);	
		}
	}
	$options	=implode("\r\n\t\t\t", $option);
	return $options;
}

function form_store($results, $dataid, $label="") {
	if (!$label) $label	=$dataid;
	if (!is_array($results)) $results=db_query($results);
	$option	=[];
	if (is_array($results)) {
		foreach ($results as $key=>$row) {
			$row_key	=strip_tags($row[$dataid]);
			$row_value	=strip_tags($row[$label]);
			
			$row_value	=str_replace(";",'[s]', $row_value);
			$row_value	=str_replace("=>","->", $row_value);
			$row_value	=main_words($row_value, 12);
			$option[]	=$row_key."=>".$row_value;
		}
	}
	$list	=implode(";", $option);	
	return $list;
}

function form_slash($text) {
	$text	=str_replace('"', '\"', $text);
	return $text;
}

function form_lists($results, $dataid, $label="", $process=2) {
	if ($process==2) {
		if (is_array($results)) $results=implode(";", $results);
		$list	='form_store("'.form_slash($results).'", "'.form_slash($dataid).'", "'.form_slash($label).'", "1")';
	}
	else {
		$list	=form_store($results, $dataid, $label);
	}
	return $list;
}

function form_name($text="") {
	$text	=str_replace(".", "_", $text);
	return $text;
}

function form_tagText($array=[], $excl="") {
	$type	=constKey("app.spa_type");
	
	if ($type=="livewire") {
		$excl	.=",name";
		$name	=arrayKey("name", $array);
		$array["wire:model"]=$name;#, model.blur, model.live[.debounce.500ms][.throttle.150ms]
	}

	if (!is_array($excl)) $excl=explode(",", $excl);
	$text	=[];
	if ($array) {
		foreach ($array as $key=>$value) {
			if (!in_array($key, $excl)) {
				if ($key=="placeholder") {
					$lang	=$value.' ...';
					$value	=$lang;
				}
				$text[]	=$key.'="'.$value.'"';
			}
		}
	}
	$text	=implode(" ", $text);
	return $text;
}

function form_attrib($type="text", $name="", $label="", $attrib="", $validate="") {
	$oname	=($name)?$name:$type;
	$nname	=strtolower($name);
	$rule	=strtolower($validate);
	$tags	=form_tagArray($attrib);
	#------- 
	$id	=arrayKey("id", $tags);
	if (!$id) {
		$id	=form_cleanAttr("{$oname}1");
		$tags["id"]	=$id;
	}
	#------- 
	if (stristr($attrib, "multiple")) $oname.="[]";
	$tags["name"]	=form_name($oname);
	#------- 
	$types	=["radio", "checkbox", "select", "text", "textarea", "hidden", "password", "file", "jmenu"];
	$type	=($type=="check")?$types[1]:strtolower($type);
	if ($type=="jmenu") {
		$type	=$types[2];
		$attrib	="";
		$class	=varKey("select_class", "*")." jump-Menu";
	}
	$tags["type"]	=$type;
	#------- 
	$holder	=arrayKey("placeholder", $tags);
	if (!$holder) $holder=trans($name);
	if ($label) $holder=trans($label);
	if (!$holder) {
		$key	=form_cleanAttr($name);
		$holder	=trans("var_{$key}");
		if (!$holder) $holder=$key;
	}

	if ($holder=="none") $holder="";
	if ($holder) $tags["placeholder"]	=$holder;
	
	if ($type=="file") $tags["capture"]="environment";
	# derive color / date type from name
	$new_rule	="";
	if (strstr($nname, "color")) $new_rule="color";
	if (strstr($nname, "colour")) $new_rule="color";
	if (strstr($nname, "time")) $new_rule="time";
	if (strstr($nname, "date")) $new_rule="date";
	if (strstr($nname, "daterange")) $new_rule="daterange";
	if (strstr($nname, "datetime")) $new_rule="datetime";
	if (strstr($nname, "monthday")) $new_rule="monthday";
	if (strstr($nname, "yearmonth")) $new_rule="yearmonth";
	if (strstr($nname, "day")) $new_rule="day";
	if (strstr($nname, "month")) $new_rule="month";
	if (strstr($nname, "year")) $new_rule="year";
		
	# optional rule	
	$required	=0;
	$split	=str_split($rule, 1);
	if (arrayKey(0, $split)=="r"||$rule=="yes") {
		$required	=1;
		$rule	=($rule=="yes")?"":substr($rule, 1, strlen($rule));	
		//$tags["required"]	="required";//true
		$tags["data-required"]	="true";
	}
	if (!$rule && $type!="select") $rule=$new_rule;

	$time_list	="date,datetime,daterange,drange,trange,dtrange,yearmonth,monthday,day,month,year";
	$time_array	=explode(",", $time_list);
	$is_date	=in_array($rule, $time_array);
	
	$new_list	="number,search,tel,url,email,datetime,date,month,week,time,range";#,datetime-local,color
	$new_array	=explode(",", $new_list);
	
	//if (strlen($value)=="7"&&substr($value, 0, 1)=="#") $tags["style"]='background-color:'.$value.';';
	#----------- 
	
	$vclass	=[];
	if ($rule) {
		if ($type!="hidden") {
			#start validation
			$custom	="email,integer,number,date,datetime,ipv4,url,zip,phone,alpha,alphaNum,alphaSpace,numSpace,spaces,alphaAccent,numAccent,accents";
			$custom	=explode(",", $custom);
			
			$vrule	=$rule;
			if ($required) $vclass[]="required";
			if (strstr($rule, "inrange")) {	
				$range	=str_replace("inrange", "", $rule);
				$chip	=explode("-", $range);
				$min	=arrayKey(0, $chip);
				$max	=arrayKey(1, $chip);		  
				$vrule	='integer],min['.$min.'],max['.$max;
			}
			if (in_array($rule, $custom)) $vclass[]="custom[".$vrule."]";
			if ($rule=="confirm") {
				$field	=str_replace("confirm_", "", $id);
				$vclass[]="equals[".$field."]";
			}
		}
	}			
	$vclass	=implode(", ", $vclass);
	
	$types	=["tel", "url", "number", "email", "date", "time", "datetime", "datetime-local"];
	if (in_array($type, $types)) $type="text";

	$def_class	=varKey("{$type}_class", "form-control");
	$xtra_class	=varKey("form_class");

	if (is_array($def_class)) $def_class=implode(" ", $def_class);
	$class	=arrayKey("class", $tags, $def_class);
	if (!$class) $class=varKey("text_class");
	if (!$class) $class="* {$type}-input size-{$type}";
	if ($is_date && $type=="text") $class.=" pl_{$rule} date-icon";
	if ($rule=="time") $class.=" pl_{$rule} time-icon";
	if ($rule=="color") $class.=" color-icon";
	if ($vclass) $class.=" validate[".$vclass."]";
	$class	=str_replace("*", $def_class, $class);
	if ($xtra_class) $class.=" {$xtra_class}";
	if ($class) $tags["class"]=trim($class);
	if (stristr($xtra_class, "material") && $type=="select") $tags["data-theme"]="material";
	return $tags;
}

function form_label($title, $attrib="", $class="", $require="", $icon="", $temp="") {
	
	if (!$temp) $temp="itr";
	if (!$class) $class="control-label";
	$attrib	='class="'.$class.'" '.trim($attrib);
	$blocks	=array('t'=>'&nbsp;', 'i'=>'', 'r'=>'');
	if ($title) $blocks['t']=lang($title);
	if ($icon) $blocks['i']=' <i class="fad fa-'.$icon.'">'.$icon.'</i> ';
	if ($require) $blocks['r']=' <span class="require_red small">*</span> ';
	$nTemp	=$temp;
	$array	=str_split($temp);
	foreach ($array as $block) {
		$nTemp	=str_replace($block, '['.$block.']', $nTemp);
	}
	foreach ($array as $block) {
		$nTemp	=str_replace('['.$block.']', $blocks[$block], $nTemp);
	}
	$nTemp	=trim($nTemp);
	$label	='<label '.$attrib.'>'.$nTemp.'</label>';
	return $label;
}

function form_title($name, $label="", $array=[]) {
	$get_class    =arrayKey("class", $array);
	$attrib='for="'.$name.'1"';
	if ($get_class) $attrib.=' class="'.$get_class.'"';
	$text   ='<label '.$attrib.'>'.$label.'</label>';
	return $text;
} 

function form_open($get_url="", $array="") {
	$get_type   =constKey("app.spa_type");

	$get_class    =arrayKey("class", $array);
	$get_files    =arrayKey("files", $array);
	$get_method   =arrayKey("method", $array);
	$get_inertia   =arrayKey("inertia", $array);
	$get_livewire   =arrayKey("wire", $array);

	$inertia	=($get_inertia || $get_type=="inertia");
	$livewire	=($get_livewire || $get_type=="livewire");

	$tag  =[];
	if ($get_class) $tag[]='class="'.$get_class.'"';
	if ($get_method) $tag[]='method="'.$get_method.'"';
	if ($get_files) $tag[]='enctype="multipart/form-data"';
	if ($livewire) $tag[]='wire:submit="'.$get_livewire.'"';
	if ($inertia) {
		$tag[]='@submit.prevent="form.post(url(\''.$get_url.'\'), { onSuccess: () => form.reset() })"';
	}
	else {
		$tag[]='action="'.$get_url.'"';
	}
	
	$tags   =implode(" ", $tag);
	$text   ="<form {$tags}>";
	return $text;
}

function form_close() {
	$text   ="\n</form>";
	return $text;
} 

function form_cleanAttr($text="") {
	$text = str_replace('][', '_', $text);
	$text = str_replace('[', '_', $text);
	$text = str_replace(']', '', $text);
	$text = main_textDash($text, 2);
	return $text;
}

function form_tagArray($text="") {
	$partial=array("required", "multiple", "disabled", "readonly", "selected", "checked");
	$text	="{$text} ";
	foreach ($partial as $key) {
		if (stristr($text, "{$key} ")) $text=str_replace("{$key} ", $key.'="'.$key.'" ', $text);
	}
	
	$text	=trim($text);
	$text	=preg_replace("/\s\s+/", ' ', $text);
	$text	=str_replace(' "', '"', $text);
	$text	=str_replace('"', '\"', $text);
	$text	=str_replace(' :', ':', $text);
	$text	=str_replace(': ', ':', $text);
	$text	=str_replace(' ,', ',', $text);
	$text	=str_replace(', ', ',', $text);
	$text	=str_replace('= ', '=', $text);
	$text	=str_replace(' =', '=', $text);
	$text	=str_replace('- ', '-', $text);
	$text	=str_replace(' -', '-', $text);
	$text	=str_replace(' >', '>', $text);
	$text	=str_replace('> ', '>', $text);
	$text	=str_replace(' [', '[', $text);
	$text	=str_replace('] ', '[', $text);
	$text	=str_replace(' [', '[', $text);
	$text	=str_replace('[ ', '[', $text);
	$text	=str_replace(' }', '}', $text);
	$text	=str_replace('} ', '}', $text);
	$text	=str_replace(' {', '{', $text);
	$text	=str_replace('{ ', '{', $text);
	$text	=str_replace(' ;', ';', $text);
	$text	=str_replace('; ', ';', $text);
	$text	=str_replace('=\"', '="', $text);
	$text	=str_replace("='", '="', $text);
	$text	=str_replace('\' ', '" ', $text);
	$text	=str_replace('\" ', '" ', $text);
	$text	=str_replace('="', '":"', $text);
	$text	=str_replace('" ', '", "', $text);
	$text	='{"'.$text.'}';
	$text	=str_replace('\"}', '"}', $text);
	$array	=json_decode($text, 1);
	return $array;
}
