<?php

include('smarty/Smarty.class.php');

// create object
$smarty = new Smarty;
$smarty->compile_check = true;


function display_page($template, $pagetitle, $header = true, $footer = true)
{
        global $smarty;
        //assign_vars(); // Make assign_vars function if you need to assign vars after processing
        if($pagetitle) $smarty->assign("pagetitle", $pagetitle);
        
        if($header) $smarty->display('header.tpl');
        if(!$footer) return $smarty->display($template);
        $smarty->display($template);
        $smarty->display('footer.tpl');
}


// Assign smart vars here
