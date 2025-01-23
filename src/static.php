<?php
namespace Teescripts\Helpers;

# ------------- 

function bidTypeList($dataid="") {
    $list	='1=>Selling;2=>Buying';
    return $list;
}
# ------------- 

function bidCycleList($dataid="") {
    $list	='DAY=>Day(s);WEEK=>Week(s);MONTH=>Month(s);YEAR=>Year(s)';
    return $list;
}
# ------------- 

function warrantyList($dataid="") {
    $list	='6 MONTH=>6 Months;1 YEAR=>1 Year';
    return $list;
}
# ------------- 

function shelflifeList($dataid="") {
    $list	='1 DAY=>1 Day;2 DAY=>2 Days;1 WEEK=>1 Week;6 MONTH=>6 Months';
    return $list;
}
# ------------- 

function bidStatusList($dataid="") {
    $list	='1=>Pending;2=>Published;3=>Closed';
    return $list;
}
# ------------- 

function offerStatusList($dataid="") {
    $list	='1=>Submitted;2=>Approved;3=>Expired';
    return $list;
}
# ------------- 

function qualityList() {
    $list	="<1=>Normal;11=>Super;12=>High;13=>Medium;14=>Low;15=>Poor>;<2=>Organic;21=>Super;22=>High;23=>Medium;24=>Low;25=>Poor>";
    return $list;
}

function methodList() {
    $list	="clicpay=>Clic Wallet;ondelivery=>Cash on Delivery;financegroup=>Credit Finance";
    return $list;
}

function payTypeList() {
    $list	="1=>Initialised;2=>Pending;3=>Cancelled;4=>Timed Out;5=>Failed;6=>Completed";#;7=>Awaiting Confirmation;8=>Part Paid;21=>Change Token;22=>Check status
    return $list;
}

function forumtypeList() {
    $list	="1=>Products;2=>General,3=>Blog;4=>Other";
    return $list;
}

function activitiesView() {
    return $query;
}
# ------------- 

function pricingList() {
    $list	='1=>Wholesaler;2=>Distributor;3=>Retailer;4=>Consumer';
    return $list;
}
function priceTypesList() {
    $list	=$this->pricingList().';5=>Group;';
    return $list;
}
# ------------- 

function shoptypeList() {
    $list	='1=>Primary;2=>Retail Outlet;3=>Mobile Outlet;4=>Farm land;8=>Other';
    return $list;
}
# ------------- 

function skuTypeList() {
    $list	='piece=>Piece;each=>Each;dozen=>Dozen;carton=>Carton;packs=>Packs';
    return $list;
}
# ------------- 

function expenseTypeList($dataid="") {
    $list	='1=>Fixed Expenses;2=>Periodic Expenses;3=>Variable Expenses;8=>Other';
    return $list;
}
# ------------- 

function expenseStatusList($dataid="") {
    $list	='1=>Pending;2=>Approved;3=>Released;4=>Rejected';
    return $list;
}
# ------------- 

function staffTypeList($dataid="") {
    $list	='1=>Permanent;2=>Temporary;4=>Contract;4=>Expatriate;8=>Other';
    return $list;
}
# ------------- 

function empStatusList($dataid="") {
    $list	='1=>Employed;2=>Suspended;3=>Resigned;4=>Terminated;5=>Retired;6=>AWoL;7=>Other';
    return $list;
}
# ------------- 

function tabActionsList($dataid="") {
    $list	='index=>List records;insert=>Add record;update=>Edit record;view=>View details;delete=>Delete record(s)';
    return $list;
}
# ------------- 
