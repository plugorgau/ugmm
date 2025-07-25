<!DOCTYPE html>
<html>
<head>
<title>PLUG - Members Area{block name=pagetitle}{/block}</title>

<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<meta name="generator" content="UGMM V0.4" />
<!-- CSS Stylesheet -->
<link rel="stylesheet" type="text/css" href="style.css" id="plug_css" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
{block name=head_extra}{/block}
</head>

<body>

<header>
  <div>
  <a href="/"><img src="/logo.png" alt="[logo]" class="logo" style='vertical-align: middle'></a>
  <span style='font-weight: bold; font-size: 2.5em'>&nbsp; Perth Linux Users Group</span>
  </div>

{include file="menu.tpl"}
</header>
<article>
<h1>{block name=title}Title{/block}</h1>
{include file="messages.tpl"}
{block name=body}{/block}
</article>

<footer>
<p>This page is maintained by the <a href="/contact/">PLUG webmasters</a>. E-mail: {mailto address=$emails.webmasters encode="javascript_charcode"}<br/>

Copyright &copy; 1996-{'Y'|date} PLUG, Inc.
</p>

<div id="generated">{page_gen_stats}</div>
</footer>
</body>
</html>
