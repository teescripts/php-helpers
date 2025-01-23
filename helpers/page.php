<?php
namespace Teescripts\Helpers;
function page_count($query, $data="") {
	$count_query	=varKey("count_query");
	$found_rows		="SQL_CALC_FOUND_ROWS";
	if ($count_query) $query=$count_query;
	if (!strstr($query, $found_rows)) $query=str_ireplace(".SELECT ", "SELECT {$found_rows} ", ".{$query}");
	$connect=db_query($query." LIMIT 0, 1", $data, "", 2, 2);
	$results=db_query("SELECT FOUND_ROWS() AS `rows`", "", "", 2, $connect);
	$result	=arrayKey(0, $results);
	$total	=arrayKey("rows", $result, 0);
	return $total;
}
	
function page_vals($item, $limit="", $page=1) {
	
	$init	=$_GET;
	$index	=main_textNormal($item, 2);
	$get_max	=arrayKey("max_".$index, $init);
	$get_page	=arrayKey("page_".$index, $init, $page);	
	$get_page	=intval($get_page);
	if ($get_page<1) $get_page=1;
		
	$max	=arrayKey("max_".$index, main_var("stored"), $limit);
	if ($get_max && $get_max<=500) {
		$max	=intval($get_max);
		sess_set("max_".$index, $max);
	}
	$results	=["page"=>$get_page, "max"=>$max, "index"=>$index];
	return $results;		
}

function page_result($query, $limit, $item="", $type="", $data="", $page=1) {
	if (!$type) $type=PDO::FETCH_ASSOC;
	$array	=page_vals($item, $limit, $page);
	$max	=$array["max"];
	$page	=$array["page"];
	
	$from	=($page * $max) - $max;
	$query	=$query." LIMIT {$from}, {$max}";
	$results=db_query($query, $data);
	
	if (is_array($results)) return $results;
}

function page_info($query, $limit, $item, $data="", $page=1) {		
	$count	=page_count($query, $data);
	$array	=page_vals($item, $limit, $page);
	$max	=$array["max"];
	$page	=$array["page"];
	$index	=$array["index"];
	# ------ 
	$pages	=@ceil($count / $max);
	$from	=($page * $max) - $max;
		
	$text	=($from + 1).' - '.min($from + $max, $count).' of '.number_format((float)$count);
	$result	=["text"=>$text, "pages"=>$pages, "page"=>$page, "total"=>$count, "index"=>$index, "max"=>$max];
	return $result;
}

function page_links($query, $limit, $item, $single=5, $show=1, $data="", $links="") {
	$paging	=page_info($query, $limit, $item, $data);
	$text	=page_items($paging, $limit, $item, $single, $show, $data, $links);
	return $text;
}

function page_items($page_array, $limit, $item, $single=5, $show=1, $data="", $links="") {
	// [search] => Array ( [url] => adminland ) ) 

	$max	=arrayKey("max", $page_array, 20);
	$text	=arrayKey("text", $page_array);
	$page	=arrayKey("page", $page_array, 0);
	$pages	=arrayKey("pages", $page_array, 0);
	$total	=arrayKey("total", $page_array, 0);
	$index	=arrayKey("index", $page_array);
	
	# ------ start pagination list items
	$link	=main_var("init_getUrl");
	$link	=main_splitUrl("max_{$index},page_{$index}");
	if ($links) $link.="&{$links}";
	$page_link	=$link."&page_{$index}=";
	$link_path	=$link."&max_{$index}=";
	
	$first	=1;
	$prev	=max($first, $page - 1);
	$next	=min($pages, $page + 1);
	$last	=$pages;		
	$halfway=@floor($pages/2);

	$title		=ucfirst($index);
	$item_1		=Str::singular($title);
	$item_2		=Str::plural($title);
	$page_count	="{$item_1} {$text} {$item_2}";
	# ------ start pagination list items
	$a_class	="page-link";
	$li_class	="page-item";
	
	$border	=($page<$halfway)?4:($last-3);
	$border	=($page<=$border||($border==$page-1)||($border==$page+1))?0:$border;
	
	$list_text	='';
	if ($page>1) {
		$list_text	.='
		<li class="first hide-mobile '.$li_class.'">
			<a href="'.($page_link).'1" rel="first" title="First page" class="'.$a_class.' page-link--with-arrow">
				<i class="fad fa-fast-backward"></i>
			</a>
		</li>';	
		$list_text	.='
		<li class="prev '.$li_class.'">
			<a href="'.($page_link.$prev).'" rel="prev" title="Previous page" class="'.$a_class.' page-link--with-arrow">
				<i class="fad fa-step-backward"></i>
			</a>
		</li>';
	}
	$hidden	="";
	if ($pages>1) {
		for ($i=1; $i<=$pages; $i++) { 
			if (($i!=($page-1) && $i!=$page && $i!=($page+1))||$i==$border) $hidden="hide-mobile ";
			if ($i==$page) { 
				$list_text	.='
			<li class="'.$hidden.$li_class.' active"><a class="'.$a_class.'">'.$i.' <span class="sr-only">(current)</span></a></li>'; 
			}
			elseif ($pages>20) {
				#pages too many
				if (($i<4||$i==($halfway-1)||$i==$halfway||$i==($halfway+1)||$i==($page-1)||$i==($page+1))&&$i!=$border||$i>($pages-3)) {
					#pages in the middle
					$list_text	.='
			<li class="'.$hidden.$li_class.'"><a href="'.($page_link.$i).'" title="Page '.$i.'" class="'.$a_class.'">'.$i.'</a></li>';
				}
				else {
					# summarise the middle
					$list_text	.=(($i>=($halfway-2)&&$i<=($halfway+2))||$i==$border)?'
			<li class="'.$hidden.$li_class.'"><span>&hellip;</span></li>':"";
				}
			}#end pages too many
			else { 
				$list_text	.='
			<li class="'.$hidden.'"><a href="'.($page_link.$i).'" class="'.$a_class.'">'.$i.'</a></li>';
			}
		}# end for each loop
	}

	// Build Next Link 
	if ($page < $pages) {
		$list_text	.='
		<li class="next '.$li_class.'">
			<a href="'.($page_link.$next).'" rel="next" title="Next page" class="'.$a_class.' page-link--with-arrow">
				<i class="fad fa-step-forward"></i>
			</a>
		</li>';
		$list_text	.='
		<li class="last hide-mobile '.$li_class.' rarr">
			<a href="'.($page_link.$pages).'" rel="last" title="Last page" class="'.$a_class.' page-link--with-arrow">
				<i class="fad fa-fast-forward"></i>
			</a>
		</li>';
	}#-- end links pagination 
	
	# -------------- select
	$select_text	='
	<div class="form-group toolbox-item toolbox-show pt10">
		<select class="form-control '.main_class("select").' jump-Menu input-md" name="max_'.$index.'" href="'.$link_path.'">';
	
	$opts	=[9, 18, 27, 42, 90, 180, 360, 500];
	foreach ($opts as $opt=>$val) {
		$state	=($max==$val)?' selected':"";
		$select_text	.='
			<option value="'.($val).'"'.$state.'>'.$val.'</option>';
	}
	#---------------- 
	$select_text	.='
		</select>	
	</div>';
	# ---------- code show pagination
	$final_text	="";
	$text_none	=lang_htmlId("no_records", "No ".ucwords($item_2)." found");
	if (!$pages) $final_text=msgLang(2, $text_none);
	if (!$show) $final_text="";

	if ($pages>1) {
		$final_text	='
		<ul class="pagination justify-content-center toolbox-item">'.$list_text.'</ul>';

		$final_text	='
		<div class="products-view__pagination p5">
			'.$final_text.'
		</div>';//posts-view__pagination
		$final_text	='
		<div class="row bt mt10 wd-100p">
			<div class="col-md-2 col-sm-4 s12">'.$select_text.'</div>
			<div class="col-md-10 col-sm-8 s12">'.$final_text.'</div>
			<div class="col-md-12 text-center s12">'.$page_count.'</div>
		</div>';
	}
	
	return $final_text;
	# return the pagination result
}

function page_Ajax($query, $page_array=[], $data="", $page=1) {
	$var_elem	="";
	@extract($page_array, EXTR_PREFIX_ALL, "var");
	
	$array	=$query;
	if (!is_array($array)) $array=page_info($query, $var_max, $var_item, $data, $page);
	
	$page	=$array["page"];
	$text	=$array["text"];
	$pages	=$array["pages"];
	$item	=$array["index"];
	$total	=$array["total"];
	
	if (!$var_elem) $var_elem=$var_load;
	$elemId	=main_textNormal($var_elem, 2);
	$elemId	=ltrim($elemId, "_");

	$next_page	=($page + 1);
	$next_link	=$var_url.'&page_'. $var_item .'='. $next_page .'';
	$text_pages	='Page <span class="page-no tx-bold">'. $page .'</span> of '. $pages .'';
	$text_info	='Record '.$text.' records, '.strip_tags($text_pages);
	$load_text	="";
	if ($page < $pages) {			
		$load_text	='
		<div class="page-load-info">'. $text_pages .'</div>
		<a class="page-load-button text-muted pd-y-10" href="'.$next_link.'">'.lang("Load More").' ... </a>
		<img class="page-load-image" src="'.public_path("images/ajax-loader.gif").'" alt="'.lang("Loading").' ..." />';
	}		
	$page_text	='<div class="page-load-trigger" id="trig_'.$elemId.'" data-elem="'.$var_elem.'" data-load="'.$var_load.'" data-url="'.$var_url.'" data-item="'.$item.'" data-total="'.$pages.'" data-page="'.$next_page.'" data-info="'.$text_info.'">'.$load_text.'</div>';
	return $page_text;		
}