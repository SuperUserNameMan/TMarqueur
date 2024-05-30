# TMarqueur
			
## What is TMarqueur ?

**TMarqueur** is a lightweight yet rich markup language inspired by **Markdown**
 and **ASCIIdoc** and etc.

You can use them to add formating elements to your raw-text documents.

These raw-text documents remain relatively easy and clear to read, and they can 
be converted to HTML to be displayed in a web browser or in a viewer application.

## What does it looks like ?

You can try real-time javascript demo here : https://www.terminajones.com/TMarqueur/

## Why TMarqueur ?

**Markdown** is great, but I never understood why `*` and `_` do the same 
thing, and why doubling them do something different once converted to HTML ...

Surrounding text using `_underscores_` looks like underlining it, so why should
 it be interpreted as `_italic_` and `__bold__` ?

The idea that led to **TMarqueur** is that the basic formating elements should 
mean what they look like to mean.

For instance : ``__underscore__`` should mean __underlined__.

From there, I extended the formating elements as much as I could to make it as 
rich as possible.

## How does it differ from **Markdown** ?

* The formatting elements are more numerous, and try to be more consistent in 
regard to their appearance in raw-text and HTML once rendered.

* Basic formatting elements must be doubled `**like that**`, and block 
elements must be _at least_ quadrupled.

* Indented text is not implicitely interpreted as a code block. 
If you want a code block, you must be explicit and put your code sample
inside a code block (equivalent to **Markdown**'s fenced code block).

* Titles (headings) are formated using different markers :
	- `##` for `<h1>`
	- `==` for `<h2>`
	- `++` for `<h3>`
	- `--` for `<h4>`

* Indented titles will be converted to centered title in HTML.

* Titles can be multiline, can be centered and can be surrounded in different decorative manners in raw-text mode.

* You can't mix HTML code with **TMarqueur**. Characters `<` and `>` are rendered
to their HTML entities equivalents. If you want to include custom HTML code inside
your **TMarqueur** document, you may use the _HTML div_ block.

* Tables are supported through an extension and must be "drawn" inside a code block.

* etc.

See full documentation here : https://www.terminajones.com/TMarqueur/

## What TMarqueur stands for ?

**Marqueur** is the french spelling of **marker**.


## How to use ?

### Javascript :
```` Javascript 
<link rel="stylesheet" href="TMarqueur.css">

<script src="TMarqueur.js"></script>
<script src="TMarqueur.tables.js"></script>

<script>
function preview() 
{
    var src = document.getElementById('tmarqueur').value  ;
    var dst = TMarqueur.to_html( src );
    document.getElementById('preview').innerHTML = dst ;
}
window.onload = preview ;
</script>

<textarea id="tmarqueur" onkeyup="preview();"></textarea>
<div id="preview"></div>
```````````

### PHP 8.1
```` PHP
<?php
require_once("TMarqueur.php");
require_once("TMarqueur.tables.php");

$source = file_get_contents( 'doc.tmq' );

$html = \TMarqueur\to_html( $source );
````
