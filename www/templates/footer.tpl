

</div> <!-- Close #pagecontainer -->

<p class="footer">
This page is maintained by the <a href="http://plug.org.au/contact/">PLUG webmasters</a>. E-mail: {mailto address=$emails.webmasters encode="javascript_charcode"}<br/>

Copyright &copy; 1996-{'Y'|date} PLUG, Inc.
</p>

<div id="generated">
{php}
   global $pagestarttime;
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = round(($endtime - $pagestarttime), 2);
   echo "Page generated in ".$totaltime." seconds using ";    
   echo memory_get_peak_usage(true)/1024/1024 ;{/php} Mb mem
</div>


</body>
