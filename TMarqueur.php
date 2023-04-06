<?php namespace TMarqueur;

/**
	||TMarqueur|| is an alternative lightweight markup language 
	for creating formatted text using a plain-text editor.

	||TMarqueur.php|| is used to convert TMarqueur documents into HTML.

	It is coded for PHP 8.1.
*/
const FOR_PHP   = '8.1' ;
const VERSION   = "0.1.202303251739" ;
const NAME      = "TMarqueur aka Terminajones' Marqueur" ;
const COPYRIGHT = 'Copyright 2023 Yoann G.F. FAYOLLE, Terminajones.com' ;
const LICENSE   = <<<END_OF_LICENSE

TMarqueur.js is distributed under the following copyright and licence :

Copyright : (c) 2023, Yoann G.F. FAYOLLE , Terminajones.com 

License : "Terminajones' GFY License"

Redistribution and modification of the software in both source and binary 
forms is permitted under the following conditions: 

	1. These copyright notice, license, conditions list, and disclaimer must 
	be retained in all copies and derivatives of the software ; 

	2. Any modifications made to the source code must be clearly and prominently 
	documented within the redistributed product ; 

	3. The interpretation and application of this license must not prejudice 
	the rights of the copyright holder. 

DISCLAIMER : THIS SOFTWARE IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND,
AND THE COPYRIGHT HOLDER SHALL NOT BE LIABLE FOR ANY DAMAGES ARISING FROM THE
USE OF THE SOFTWARE.

END_OF_LICENSE;

const THIRD_PARTY_MODIFICATIONS_LIST = [ 
		/// XXX if you alter the original source code, summarize your modifications in here, and identify yourself
];

class CONFIG {
	static bool $disable_custom_attr = true ; /// disable custom attributes added to blocks
	static bool $disable_html_blocks = true ; /// disable blocks that accept raw HTML content 

	static int  $tab_size = 4 ; /// how many spaces for tab ?

	static bool $auto_summary = true ;
	static bool $auto_footnote = true ;

	static string $summary_prefix  = 'goto_'; /// prefix to automated titles anchor used to build the table of content 
	static string $footnote_prefix = 'footnote-'; /// prefix to footnote anchor
	static string $footnote_return_prefix = 'ref-footnote-'; /// prefix to linkback to footnote reference anchor

	static string $auto_summary_title_attribute = ' ondblclick="location.replace(\'#\' + TMarqueur.summary_prefix + \'summary\');"' ; /// this attribute is added to titles if TMarqueur.auto_summary

	static string $footnote_title = "== Footnotes : ==" ;

	static string $attribute_separator = ' / ' ; /// used to separate blocks tag's attributes and bracket tag's parameters 
	static string $attribute_keyval_seperator = ' : '; /// used to name the blocks tag's attributes. Ex : """"" cite : René Descartes / date : 1637 """"""

};

// XXX current source code bellow is a loosy direct translation of TMarqueur.js

$html_special_chars = 
[
	[ '&' , '&amp;'],
	[ '<' , '&lt;' ],
	[ '>' , '&gt;' ],
];

$text_to_html =
[
	/// Table of convertion of text codes into HTML :
	/// [ text ,   html   ] is applied if formating != 'HTML'
	/// [ text , [ html ] ] is applied if formating == true
	
	/// NOTE : &#xFE0E; force the preceding emoji or entity to be rendered in text mode

	[ '-&gt;'  , '&rarr;'  ],	//!\ ->
	[ '&lt;-'  ,  '&larr;' ],	//!\ <-
	[ '&lt;-&gt;' , '&harr;' ], //!\ <->

	[ ':-D' , '&#128513;' ],
	[ ':-)' , '&#128512;' ],
	[ 'X-D' , '&#128518;' ],
	[ ';-)' , '&#128521;' ],
	[ '^_^' , '&#128522;' ],
	[ ':-P' , '&#128523;' ],
	[ 'B-)' , '&#128526;' ],
	[ ':-|' , '&#128528;' ],
	[ ':-/' , '&#128533;' ],
	[ ':-S' , '&#128534;' ],
	[ ':-*' , '&#128535;' ],
	[ ';-*' , '&#128536;' ],
	
	[ '/!\\' , '⚠️' ],
	[ '[i]' , 'ℹ️' ],

	[ '(c)' , '&copy;' ],
	[ '(C)' , '&copy;' ],
	[ '(r)' , '&reg;' ],
	[ '(R)' , '&reg;' ],

	[ '(TM)' , '&trade;' ],
	[ '(tm)' , '&trade;' ],

	[ ' :' , '&nbsp;:' ],
	[ ' ?' , '&nbsp;?' ],
	[ ' !' , '&nbsp;!' ],
	[ ' ;' , '&nbsp;;' ],

	[ '[X]' , '❌' ],
	[ '[V]' , '✅' ],

	[ '[x]' , '❌&#xFE0E;' ],
	[ '[v]' , '✅&#xFE0E;' ],

	[ '[N]' , '❌' ],
	[ '[Y]' , '✅' ],

	[ '[n]' , '❌&#xFE0E;' ],
	[ '[y]' , '✅&#xFE0E;' ],

	[ '[NO]' , '❌' ],
	[ '[YES]' , '✅' ],

	[ '[no]' , '❌&#xFE0E;' ],
	[ '[yes]' , '✅&#xFE0E;' ],
];

$escape_seq = 
[
	'\\' =>  '&#92;' ,

	'n' => '<br/>' ,

	' ' => '&nbsp;',

	'`' => '&#96;' ,
];

$title_tags = 
[
	/// .tags : contains the list of HTML tags that will surround the content (/!\ only ASCII chr allowed)
	/// .formating : true | false | 'HTML'
	///		- true   = replaces HTML entities and applies inline formating ;
	///		- false  = only replace HTML special entities ;
	///		- 'HTML' = keep the content untouched (the content is raw HTML code) ;
	/// .summary : true | false 
	///		- true : an id attribute will be defined by default, and the content will be used to feed the summary table ;
	/// .summary_indent :
	///		- when the summary table is converted to HTML, the value is used to indent the <li> ;
	/// .hr_class :
	///		- when the content is empty, a <hr> is used instead
	
	'#' => [ 'tags' => [ 'h1' ] , 'formating' => true , 'summary' => true , 'summary_indent' => 0 , 'hr_class' => 'hr_type_1' ],
	'=' => [ 'tags' => [ 'h2' ] , 'formating' => true , 'summary' => true , 'summary_indent' => 2 , 'hr_class' => 'hr_type_2' ],
	'+' => [ 'tags' => [ 'h3' ] , 'formating' => true , 'summary' => true , 'summary_indent' => 4 , 'hr_class' => 'hr_type_3' ],
	'-' => [ 'tags' => [ 'h4' ] , 'formating' => true , 'summary' => true , 'summary_indent' => 6 , 'hr_class' => 'hr_type_4' ],

	'>' => [ 'tags' => [ 'blockquote' , 'pre' ] , 'formating' => true , 'summary' => false ], // not title, but behave the same
];

$block_tags = 
[
	/// .tags : contains the list of HTML tags that will surround the content (/!\ only ASCII chr allowed)
	/// .attr : contains the list of default attributes. They can specified in the given order, or using 
	/// 		the name (see TMarqueur.attribute_separator and TMarqueur.attribute_keyval_seperator)
	/// .formating : true | false | 'HTML'
	///		- true   = replaces HTML entities and applies inline formating ;
	///		- false  = only replace HTML special entities ;
	///		- 'HTML' = keep the content untouched (the content is raw HTML code) ;
	/// .custom_attr : true | false
	///		- true  : accept named attributes not specified into the .attr[] list ;
	///		- false : if named attribute is not specified into the .attr[] list, the name will be prefixed with "data-" ;
	/// .variants : 
	///		The content of the blocks can be parsed and interpreted by plugins/extensions.
	///		These plugins/extensions are called "variants". See : TMarqueur.plug_block_variant()

	'"'  => [ 'type' => 'quote'     , 'tags' => [ 'blockquote' ] , 'attr' => [ 'title' , 'cite' , 'date' , 'source' ] , 'formating' => true  ],
	'\'' => [ 'type' => 'quote-raw' , 'tags' => [ 'blockquote' ] , 'attr' => [ 'title' , 'cite' , 'date' , 'source' ] , 'formating' => false ],

	'<'  => [ 'type' => 'textarea'  , 'tags' => [ 'textarea'   ] , 'attr' => [ 'name' , 'title' ] , 'formating' => false ],

	';'  => [ 'type' => 'div'       , 'tags' => [ 'div'        ] , 'attr' => [ 'class' , 'id' ], 'formating' => true , 'custom_attr' => true ],

	':'  => [ 'type' => 'div-HTML'  , 'tags' => [ 'div'        ] , 'attr' => [ 'class' , 'id' ], 'formating' => 'HTML' , 'custom_attr' => true  ],

	'`'  => [ 'type' => 'code'      , 'tags' => [ 'pre','code' ] , 'attr' => [ 'lang' , 'title' , 'source' ] , 'formating' => false , // TODO choose to which tag goes the attributes
		'attr_defaults' => [
			'lang' => 'raw-text',
			//'title' => function( $attr_val ) { return $attr_val[ 'lang' ] ?? '' ; },
		],
	], 
];

$format_tags = 
[ 
	/// .tags : contains the list of HTML tags that will surround the content (/!\ only ASCII chr allowed)
	/// .formating : true | false | 'HTML'
	///		- true   = replaces HTML entities and applies inline formating ;
	///		- false  = only replace HTML special entities ;
	///		- 'HTML' = keep the content untouched (the content is raw HTML code) ;

	'*'  => [ 'tags' => [ 'b' ] , 'formating' => true ], // **bold**
	'_'  => [ 'tags' => [ 'u' ] , 'formating' => true ], // __underline__
	'~'  => [ 'tags' => [ 's' ] , 'formating' => true ], // ~~strike~~
	'\'' => [ 'tags' => [ 'i' ] , 'formating' => true ], // ''italic''
	'"'  => [ 'tags' => [ 'q' ] , 'formating' => true ], // ""quote""
	
	'^'  => [ 'tags' => [ 'sup' ] , 'formating' => true ], // ^^superscript^^
	','  => [ 'tags' => [ 'sub' ] , 'formating' => true ], // ,,subscript,,

	'%'  => [ 'tags' => [ 'mark' ] , 'formating' => true ], // %%marked%%

	'`'  => [ 'tags' => [ 'samp' ] , 'formating' => false ], // ``computer``

	'|'  => [ 'tags' => [ 'em' ] , 'formating' => true ], // ||emphasised||

	'$'  => [ 'tags' => [ 'strong' ] , 'formating' => true ], // $$important$$

	'/'  => [ 'tags' => [ 'span' ] , 'formating' => false ], // //unformated//
];

$brackets_tags = // /!\ only ASCII chr allowed as bracket
[
	/// .terminator : contains the ending bracket (/!\ only ASCII chr allowed)
	/// .html : 
	///		- Contains the html code used to replace the tag and its content. 
	///		- This html code can contain placeholders for parameters. Ex : ``§param1§`` or ``!§param2§``.
	///		- The values of the parameters are extracted from the content of the tag, where they are separated using 
	///		the TMarqueur.attribute_separator. Ex : ``[[ param1 / param2 / param3 ]]``
	///		-  placeholders beginning with '!' means the value of the parameter will be escaped using 
	///      TMarqueur.escape_html_string().
	/// .params_order : list the order of the parameters as given into the content of the tag ;
	/// .params_defaults : optional list of the default value of each parameters if not provided intro the tag ;
	/// .params_validator : optional list of regular expression used to validate the value of each parameter ;
	/// .params_filter : optional list of regular expression used to extract the value from a parameter ;

	// [[ linkname / url / target ]]
	'[' => [ 
			'type' => 'link',
			'terminator' => ']' , 'html' => '<a href="!§url§" target="!§target§" title="!§url§">§content§</a>' , 
			'params_order'    => [ '§content§' , '!§url§' , '!§target§' ] , 
			'params_defaults' => [ 
				'!§url§' => function( array $_params ) : string { return $_params['§content§']; } , 
				'!§target§' => '_self' ,
			],
			'params_validator' => [
				'!§url§' => '/^(http|https|ftp|mailto):.*$|^#[^ ]*$|^[^: \t]+$/i',
				'!§target§' => '/^(_self|_blank)$/',
			],
			'variants' => [
				// [[^footnote-id / title ]]
				[ 	'type' => 'footnote-link' , 
					'test_param' => '§content§' , 
					'detector' => '/^[\^][0-9a-zA-Z\-_]+$/i' ,
					'html' => function() {
						return '<sup>[<a href="#'.CONFIG::$footnote_prefix.'!§id§" title="!§title§" id="'.CONFIG::$footnote_return_prefix.'!§id§">!§id§</a>]</sup>' ;
					}, 

					'params_order'    => [ '!§id§' , '!§title§' ] , 
					'params_defaults' => [
						'!§title§' => '',
					],
					'params_filters'  => [ '!§id§' => '/^[\^](.*)$/' ] ,
					'on_parse_callback' => function( array $_params ) { 
						if ( $_params['!§title§'] != '' )
						{
							DOC::$footnote_index[ $_params['!§id§'] ] = $_params['!§title§'] ; 
						}
					},
				],
				// [[:footnote-id / content ]]
				[	'type' => 'footnote-ref' , 
					'test_param' => '§content§' , 
					'detector' => '/^[:][0-9a-zA-Z\-_]+$/i' ,
					'html' => '' , 
					'params_order' => [ '!§id§' , '!§title§' ] , 
					'params_defaults' => [
						'!§title§' => '',
					],
					'params_filters' => [ '!§id§' => '/^[:](.*)$/' ] ,
					'on_parse_callback' => function( array $_params ) { 
						if ( $_params['!§title§'] != '' )
						{
							DOC::$footnote_index[ $_params['!§id§'] ] = $_params['!§title§'] ; 
						}
					},
				],
				
			],
	],

	// {{ abbreviation / definition }}
	'{' => [ 'type'=>'abbreviation' , 'terminator' => '}' , 'html' => '<abbr title="!§title§">§content§</abbr>' , 'params_order' => [ '§content§' , '!§title§' ] ],

	// (( anchor ))
	'(' => [ 'type' => 'anchor' , 'terminator' => ')' , 'html' => '<a name="!§anchor§" id="!§anchor§" title="#!§anchor§">⚓</a>', 
			'params_order' => [ '!§anchor§' ],
	],

	// :: entity ::
	':' => [ 'type' => 'entity', 'terminator' => ':' , 'html' => '&§code§;' , 'params_order' => [ '§code§' ] ],

];

$listing_tags =
[
	/// type : is CSS code for 'list-style-type:'
	/// detector : is regex used to detect the begining of a list item, and to extracts its value if available.

	/// WARNING : /!\ only ASCII chr allowed as raw-text bullet

	/// NOTE : \\FE0E forces the text-mode display of the preceding emoji

	[ 'type' => 'disc' ,                'detector' => '/^[ \t]*[*] /'      ], // *
	[ 'type' => 'circle' ,              'detector' => '/^[ \t]*[@] /'      ], // @
	[ 'type' => 'square' ,              'detector' => '/^[ \t]*[#] /'      ], // #
	[ 'type' => '\'-  \'',              'detector' => '/^[ \t]*[-] /'      ], // -

	[ 'type' => 'disclosure-closed' ,   'detector' => '/^[ \t]*[>] /'      ], // >
	[ 'type' => 'disclosure-open' ,     'detector' => '/^[ \t]*[v] /i'     ], // v   V
	[ 'type' => '\'▲\\FE0E  \'' ,       'detector' => '/^[ \t]*[\^] /'     ], // ^

	[ 'type' => '\'\\2192\\FE0E   \'' , 'detector' => '/^[ \t]*[\-][>] /'  ], // ->
	[ 'type' => '\'\\21E8\\FE0E   \'' , 'detector' => '/^[ \t]*[=][>] /'   ], // =>
	[ 'type' => '\'\\21B3\\FE0E   \'' , 'detector' => '/^[ \t]*[L][>] /'   ], // L>

	[ 'type' => '\'§value§. \'' , 		'detector' => '/^[ \t]*(([0-9]+[.]?)+)[.] /'  ], // 1.  1.2.  1.2.3.
	[ 'type' => '\'§value§) \'' , 		'detector' => '/^[ \t]*(([0-9]+[.]?)+)[)] /'  ], // 1)  1.2)  1.2.3)

	[ 'type' => '\'§value§. \'' ,       'detector' => '/^[ \t]*([a-z])[.] /i'     ], // a. A.
	[ 'type' => '\'§value§) \'' ,       'detector' => '/^[ \t]*([a-z])[)] /i'     ], // a) A)
	[ 'type' => '\'(§value§) \'' ,      'detector' => '/^[ \t]*[(]([a-z]+)[)] /i' ], // (a) (A)

	[ 'type' => '\'☐\\FE0E  \'',        'detector' => '/^[ \t]*\[_\] /'          ], // [_]
	[ 'type' => '\'☐\\FE0E  \'',        'detector' => '/^[ \t]*\[\] /'           ], // []
	[ 'type' => '\'☐\\FE0E  \'',        'detector' => '/^[ \t]*\[ \] /'          ], // [ ]
	[ 'type' => '\'☒\\FE0E  \'',        'detector' => '/^[ \t]*\[x\] /i'         ], // [x] 
	[ 'type' => '\'☑\\FE0E  \'',        'detector' => '/^[ \t]*\[v\] /i'         ], // [v]

	[ 'type' => '\'⚐\\FE0E  \'',        'detector' => '/^[ \t]*\[f\] /'          ], // [f]
	[ 'type' => '\'⚑\\FE0E  \'',        'detector' => '/^[ \t]*\[F\] /'          ], // [F]


	//[ 'type' => '\'⚠\\FE0E  \'',     'detector' => '/^[ \t]*\[\!\] /'         ], // [!]
	//[ 'type' => '\'ℹ\\FE0E   \'',    'detector' => '/^[ \t]*\[i\] /'          ], // [i]
	[ 'type' => '\'⚠️ \'',             'detector' => '/^[ \t]*\[\!\] /'         ], // [!]
	[ 'type' => '\'ℹ️ \'',             'detector' => '/^[ \t]*\[i\] /'          ], // [i]

];


/// Plugs a block variant.
/// _block_tag : tag of the default block ;
/// _variant : object following this format :
///		
///			{ 
///				type : 'variant_name' ,
///				test_attribute : 'lang' ,
///				detector : /^variant_name[ ]*(.*)$/i ,
///				callback : 'your_callback' ,
///
///				tags : [ 'div' ] , 
///				attr : [ 'title' ] , 
///				formating : true ,
///			}
///
///		where :
///			- .type : is the unque name of the variant ;
///			- .test_attribute : is the name of the attribute of the default block that will be used to match the .detector ;
///			- .detector : is the regex used to detect the variant ;
///			- .callback : contains the name of the callback function that will update and return the content :
///					function your_callback( content , attributes ) { return updated_content; }
///			- .tags , .attr and .formating : see TMarqueur.block_tags ;
///
///
function plug_block_variant( string $_block_tag , array $_variant  ) : bool
{ 
	return _plug_variant( 'block_tags' , $_block_tag , $_variant ); 
}


function plug_brackets_variant( string $_brackets_tag , array $_variant ) : bool
{
	return _plug_variant( 'brackets_tags' , $_brackets_tag , $_variant ); 
}


function _plug_variant( string $_marker_type, string $_marker_tag , array $_variant  ) : bool
{
	global $$_marker_type;

	if ( ! isset( $$_marker_type[ $_marker_tag ] ) ) return false ;

	$$_marker_type[ $_marker_tag ]['variants'] ??= [];

	for( $i = 0 ; $i < count( $$_marker_type[ $_marker_tag ]['variants'] ) ; $i++ )
	{
		if ( $$_marker_type[ $_marker_tag ]['variants'][ $i ]['type'] == $_marker_tag )
		{
			$$_marker_type[ $_marker_tag ]['variants'][ $i ] = $_variant ;
			return true ;
		}
	}

	$$_marker_type[ $_marker_tag ]['variants'][] = $_variant ;
	return true ;
}


function is_eol( string $_c ) : bool { return $_c == "\n" || $_c == "\r" ; } 
function is_space( string $_c ) : bool { return $_c == " " || $_c == "\t" ; }

function count_at( string $_str , int $_pos ) : int
{
	$len_ = strlen( $_str );
	if ( $len_ <= 1 ) return $len_;

	$c_ = $_str[ $_pos ];

	$beg_ = $_pos ;
	while( $_pos < $len_ && $_str[ $_pos ] == $c_ ) $_pos++;
	return $_pos - $beg_ ;
}

function line_byte_length_at( string $_src , int $_pos ) : int
{
	$beg_ = $_pos ;
	$len_ = strlen( $_src );
	while( $_pos < $len_ && ! is_eol( $_src[$_pos] ) ) $_pos++ ;
	return $_pos - $beg_ ;
}

function list_depth( string $_line ) : int
{
	$depth_ = 0 ;
	$pos_ = 0 ;
	$len_ = strlen( $_line );
	while( $pos_ < $len_ && is_space( $_line[ $pos_ ] ) )
	{
		$depth_ += ( $_line[ $pos_ ] == "\t" ) ? CONFIG::$tab_size : 1 ;
		$pos_++;
	}
	return $depth_ ;
}

function ltrim_tag( string $_str , string $_tag ) : string 
{
	$beg_ = 0 ; 
	$len_ = strlen( $_str );
	while( $beg_ < $len_ && $_str[ $beg_ ] == $_tag ) $beg_++ ; 
	return substr( $_str , $beg_ ); 
}

function rtrim_tag( string $_str , string $_tag ) : string 
{ 
	$end_ = strlen( $_str ) - 1; 
	while( 0 <= $end_ &&  $_str[ $end_ ] == $_tag ) $end_-- ; 
	return substr( $_str  ,0 , $end_ + 1 );	
}

function trim_tag( string $_str , string $_tag ) : string 
{ 
	return ltrim_tag( rtrim_tag( $_str , $_tag ) , $_tag ); 
}

function escape_html_string( string $_val ) : string
{
	return addslashes( $_val );
}

function open_tags( array $_tags , string $_attributes = null ) : string
{
	$html_ = '';
	for( $i = 0 ; $i < count( $_tags ) ; $i++ )
	{
		$html_ .= '<'.$_tags[ $i ] ;
		if ( $_attributes !== null ) $html_ .= ' ' . $_attributes;
		$html_ .= '>';
	}
	return $html_ ;
}

function close_tags( array $_tags ) : string
{
	$html_ = '';
	for( $i = count( $_tags ) - 1 ; $i >= 0 ; $i-- )
	{
		$html_ .= '</'.$_tags[ $i ].'>';
	}
	return $html_;
}

function tag_attributes_to_object( string $_tag , array $_variant , string $_line ) : array|null
{
	if ( ! isset( $_variant['attr'] ) ) return null ;

	$_line = trim( trim_tag( trim( $_line ) , $_tag ) );

	$attributes_ =  explode( CONFIG::$attribute_separator , $_line );
	$obj_ = [];
	$key_ = '';
	$val_ = '';

	$custom_attr_ = ! CONFIG::$disable_custom_attr && ( $_variant['custom_attr'] ?? false );

	for( $i = 0 ; $i < count( $attributes_ ) ; $i++ )
	{
		if ( str_contains( $attributes_[ $i ] , CONFIG::$attribute_keyval_seperator ) )
		{
			$key_ = trim( explode( CONFIG::$attribute_keyval_seperator , $attributes_[ $i ] ) );
			$val_ = trim( substr( $attributes_[ $i ] , strlen( key_ ) + strlen( CONFIG::$attribute_separator ) ) );

			if ( ! $custom_attr_ )
			{
				if ( ! in_array( $key_ , $_variant['attr'] ) )
				{
					$key_ = "data-" . $key_ ;
				}
			}
		}
		else
		if ( isset( $_variant['attr'][ $i ] ) )
		{
			$key_ = $_variant['attr'][ $i ];
			$val_ = trim( $attributes_[ $i ] );
		}
		else
		{
			$key_ = "data-".$i;
			$val_ = trim( $attributes_[ $i ] );
		}

		$obj_[ $key_ ] = $val_ ;
	}

	if ( isset( $_variant['attr_defaults'] ) )
	{
		for( $i = 0 ; $i < count( $_variant['attr'] ) ; $i++ )
		{
			$key_ = $_variant['attr'][ $i ];

			if ( ( ! isset( $obj_[ $key_ ] ) || $obj_[ $key_ ] == '' ) && isset( $_variant['attr_defaults'][ $key_ ] ) )
			{
				$obj_[ $key_ ] = is_callable( $_variant['attr_defaults'][ $key_ ] ) ? $_variant['attr_defaults'][ $key_ ]( $obj_ ) : $_variant['attr_defaults'][ $key_ ] ;
			}
		}
	}

	return $obj_;
}

function attributes_object_to_html( array|null $_obj ) : string|null
{
	if ( empty( $_obj ) ) return null ;

	$res_ = '';

	foreach( $_obj as $key_ => $val_ )
	{
		$res_ .= $key_ . '="' . escape_html_string( $val_ ) . '" ';
	}

	return $res_ ;
}

function content_to_html( string $_content , bool|string $_content_formating = true ) : string
{
	if ( strcmp( $_content_formating , 'HTML' ) == 0 && ! CONFIG::$disable_html_blocks ) return $_content;

	global $html_special_chars;

	for( $i = 0 ; $i < count( $html_special_chars ) ; $i++ )
	{
		$key_ = $html_special_chars[ $i ][ 0 ];
		$val_ = $html_special_chars[ $i ][ 1 ];
		
		$_content = str_replace( $key_ , $val_ , $_content );
	}

	if ( $_content_formating == false ) return $_content ;

	$stack_ = [];

	$pos_ = 0 ;
	$html_ = '';

	$inner_formating_ = true ;

	global $format_tags;
	global $brackets_tags;

	$content_len_ = strlen( $_content );

	while ( $pos_ < $content_len_ )
	{
		$tag_ = $_content[ $pos_ ];
		$len_ = 1 ;
		$count_ = count_at( $_content , $pos_ );

		if ( isset( $format_tags[ $tag_ ] ) && $count_ >= 2 )
		{
			if ( $count_ >= 2 && ( $inner_formating_ || $stack_[ count( $stack_ ) - 1 ]['tag'] == $tag_ ) )
			{
				if ( count( $stack_ ) == 0 || $stack_[ count( $stack_ ) - 1 ]['tag'] != $tag_ )
				{
					$stack_[] = [ 'tag' => $tag_ , 'pos' => $pos_ ];
					$html_ .= open_tags( $format_tags[ $tag_ ]['tags'] );
					$inner_formating_ = $format_tags[ $tag_ ]['formating'] ;
					$tag_ = str_repeat( $tag_ , $count_ - 2 );
				}
				else
				{
					$html_ .= str_repeat( $tag_ , $count_ - 2 );
					array_pop( $stack_ );
					$html_ .= close_tags( $format_tags[ $tag_ ]['tags'] );
					$tag_ = '';
					$inner_formating_ = true ;
				}
				$len_ = $count_ ;
			}
		}
		else
		if ( $inner_formating_ === true && isset( $brackets_tags[ $tag_ ] ) && $count_ >= 2 )
		{
			//$count_ = count_at( $_content , $pos_ );
			$terminator_ = $brackets_tags[ $tag_ ]['terminator'] ;

			if ( $count_ >= 2 )
			{
				$end_ = $pos_ + $count_ ;

				while
				( 
					$end_ < $content_len_
					&& 
					(
						$_content[ $end_ ] != $terminator_
						|| 
						( 
							$_content[ $end_ ] != $terminator_
							&& 
							count_at( $_content , $end_ ) != 2 
						) 
					) 
				) $end_++ ;

				while( $end_ < $content_len_ && $_content[ $end_ ] == $terminator_ ) $end_++;

				$params_ = substr( $_content , $pos_ , $end_ - $pos_ );
				$len_ = strlen( $params_ );

				$params_ = ltrim_tag( $params_ , $tag_ );
				$params_ = rtrim_tag( $params_ , $brackets_tags[ $tag_ ]['terminator'] );
				$params_ = trim( $params_ );

				$params_ = explode( CONFIG::$attribute_separator , $params_ );

				$variant = $brackets_tags[ $tag_ ] ;

				$vals_ = [];
				$out_ = '';


				if ( strcmp( $_content_formating , 'summary' ) != 0 )
				{
					for( $i = 0 ; $i < count( $variant['params_order'] ) ; $i++ )
					{
						$key_ = $variant['params_order'][ $i ];
						$val_ = '';

						if ( isset( $params_[ $i ] ) )
						{
							$val_ = $params_[ $i ];
							$val_ = preg_replace( '/[\r\n\s]+/', ' ' , $val_ );
							$val_ = str_replace( "\\n", "\n" , $val_ );
							$val_ = trim( $val_);
						}
						else
						if ( isset( $variant['params_defaults'] ) && isset( $variant['params_defaults'][ $key_ ] ) )
						{
							$val_ = $variant['params_defaults'][ $key_ ] ;
						}

						$vals_[ $key_ ] = $val_ ; 
					}

					if ( isset( $variant['variants'] ) )
					{
						for( $i = 0 ; $i < count( $variant['variants'] ) ; $i++ )
						{
							if ( regex_test( $variant['variants'][ $i ]['detector'] , $vals_[ $variant['variants'][ $i ]['test_param'] ] ) ) 
							{
								$variant = $variant['variants'][ $i ];
								break;
							}
						}

						for( $i = 0 ; $i < count( $variant['params_order'] ) ; $i++ )
						{
							$key_ = $variant['params_order'][ $i ];
							$val_ = '';

							if ( isset( $params_[ $i ] ) )
							{
								$val_ = $params_[ $i ];
								$val_ = preg_replace( '/[\r\n\s]+/', ' ' , $val_ );
								$val_ = str_replace( "\\n", "\n" , $val_ );
								$val_ = trim( $val_);
							}
							else
							if ( isset( $variant['params_defaults'] ) && isset( $variant['params_defaults'][ $key_ ] ) )
							{
								$val_ = $variant['params_defaults'][ $key_ ] ;
							}

							$vals_[ $key_ ] = $val_ ; 
						}
					}


					$out_ = $variant['html'];
					if ( is_callable( $out_ ) ) $out_ = $out_();

					for( $i = 0 ; $i < count( $variant['params_order'] ); $i++ )
					{
						$key_ = $variant['params_order'][ $i ];
						$val_ = $vals_[ $key_ ];

						while( is_callable( $val_ ) ) $val_ = $val_( $vals_ );

						if ( isset( $variant['params_validator'] ) && isset( $variant['params_validator'][ $key_ ] ) )
						{
							if ( ! regex_test( $variant['params_validator'][ $key_ ] , $val_ ) )
							{
								$val_ = '';
							}
						}

						if ( isset( $variant['params_filters'] ) && isset( $variant['params_filters'][ $key_ ] ) )
						{
							$val_ = regex_exec( $variant['params_filters'][ $key_ ] , $val_ )[1] ;
							$vals_[ $key_ ] = $val_ ; 
						}

						if ( $variant['params_order'][ $i ][ 0 ] == '!' )
						{

							$val_ = escape_html_string( $val_ );
						}

						$out_ = str_replace( $variant['params_order'][ $i ] , $val_ , $out_ );

					}

					if ( isset( $variant['on_parse_callback'] ) && is_callable( $variant['on_parse_callback'] ) )
					{
						$variant['on_parse_callback']( $vals_ );
					}
				}

				$tag_ = $out_ ;
			}
		}
		else
		if ( $tag_ == "\\" && $inner_formating_ == true )
		{
			$next = $_content[ $pos_ + 1 ];

			if ( $next != '' )
			{
				global $escape_seq ;

				if ( isset( $escape_seq[ $next ] ) )
				{
					$tag_ = $escape_seq[ $next ];
					$len_ = 2 ;
				}
				else
				if ( isset( $format_tags[ $next ] ) )
				{
					$tag_ = '&#'.ord( $next ).';' ;
					$len_ = 2 ;
				}
			}
		}
		else
		if ( $inner_formating_ == true && $tag_ != ' ' )
		{
			global $text_to_html ;
			for( $i = 0 ; $i < count( $text_to_html ) ; $i++ )
			{
				$key_ = $text_to_html[ $i ][0];

				if ( $tag_ == $key_[ 0 ] && substr( $_content , $pos_ , strlen( $key_ ) ) == $key_ )
				{
					$tag_ = $text_to_html[ $i ][1];
					$len_ = strlen( $key_ );
					break;
				}
			}
		}

		$html_ .= $tag_ ;
		$pos_ += $len_ ;
	}

	while( count( $stack_ ) )
	{
		$html_ .= close_tags( $format_tags[ array_pop( $stack_ )['tag'] ]['tags'] );
	}

	return $html_ ;
}

function regex_exec( string $pattern , string $subject ) : array
{
	$out = [];

	if ( false === preg_match( $pattern , $subject , $out ) )
	{
		echo "$pattern : $subject".PHP_EOL;
		debug_print_backtrace();
		exit();
	}

	return $out ;
}

function regex_test( string $pattern , string $subject ) : bool
{

	$res = preg_match( $pattern , $subject );

	if ( $res === false )
	{
		echo "$pattern : $subject".PHP_EOL;
		debug_print_backtrace();
		exit();
	}

	return $res === 1 ;
}

function is_listing_bullet( string $_line ) : bool|array
{
	global $listing_tags;

	for( $i = 0 ; $i < count( $listing_tags ) ; $i++ )
	{
		if ( regex_test( $listing_tags[ $i ]['detector'] , $_line ) ) 
		{
			return [ 
				'type' => $listing_tags[ $i ]['type'] , 
				'value' => regex_exec( $listing_tags[ $i ]['detector'] , $_line ) ,
				'depth' => list_depth( $_line ) 
			];
		}
	}
	return false;
}

class DOC 
{
	static $summary_index = [];
	static $footnote_index = []; //XXX associative array

	static string $src = '';
	static int $src_len = 0 ;

	static string $html = '';
	static string $content = '';
	static string $line = '';

	static int $pos = 0 ;

	static $inside_title = false ;
	static $inside_listing = false ;
	static $inside_block = false ; 
	static $inside_block_variant = null ; 
	static $inside_block_attributes = null ;
	static $inside_paragraph = false ;

	static function flush_paragraph()
	{
		if ( false === DOC::$inside_paragraph || DOC::$content == '' ) return ;

		DOC::$html .= '<p style="text-align:'.DOC::$inside_paragraph.'">' ;
		DOC::$html .= content_to_html( DOC::$content ) ;
		DOC::$html .= '</p>'.PHP_EOL.PHP_EOL ;

		DOC::$content = '';
		DOC::$inside_paragraph = false ;
	}

	static function flush_listing()
	{
		if ( false === DOC::$inside_listing ) return ;
		DOC::parse_listing_line('');
	}

	static function flush_title()
	{
		if ( false === DOC::$inside_title ) return ;
		DOC::parse_title_line('');
	}

	static function read_line() : bool
	{
		DOC::$line = substr( DOC::$src, DOC::$pos , line_byte_length_at( DOC::$src , DOC::$pos ) ); 
		DOC::$pos += strlen( DOC::$line ) ;
		if ( DOC::$pos < DOC::$src_len && DOC::$src[ DOC::$pos ] == "\r" ) DOC::$pos++;
		if ( DOC::$pos < DOC::$src_len && DOC::$src[ DOC::$pos ] == "\n" ) DOC::$pos++;
		return true ; 
	}

	static function parse_listing_line( string $line ) : bool
	{
		$is_bullet = is_listing_bullet( $line ); // * ....

		$is_empty_line = trim( $line ) == '' ;

		$is_first_item  = DOC::$inside_listing === false && $is_bullet !== false ; // <ul><li> ...
		$is_multiline   = DOC::$inside_listing !== false && $is_bullet === false && ! $is_empty_line ; // ...
		$is_next_item   = DOC::$inside_listing !== false && $is_bullet !== false ; // ...</li><li>...
		$is_list_ending = DOC::$inside_listing !== false && $is_empty_line ; // ... </li></ul>

		if ( $is_first_item )
		{
			DOC::flush_title() ;
			DOC::flush_paragraph() ;
			DOC::$html .= '<ul>'.PHP_EOL ;
			DOC::$content = substr( $line , strlen( $is_bullet['value'][0] ) );
			DOC::$inside_listing = $is_bullet ;
			return true ;
		}

		if ( $is_multiline )
		{
			DOC::$content .= PHP_EOL . $line ;
			return true ; 
		}

		if ( $is_next_item )
		{
			$value = DOC::$inside_listing['value'][1] ?? '' ;
			$type = str_replace( '§value§' , $value , DOC::$inside_listing['type'] ) ; 
			DOC::$html .= '<li';
				DOC::$html .= ' value="'.$value.'"';
				DOC::$html .= ' style="list-style-type:'.$type.'; margin-left:'.DOC::$inside_listing['depth'].'ch;"';
			DOC::$html .= '><span>';
			DOC::$html .= content_to_html( DOC::$content );
			DOC::$html .= '</span></li>'.PHP_EOL;

			DOC::$content = substr( $line , strlen( $is_bullet['value'][0] ) );
			DOC::$inside_listing = $is_bullet ;

			return true ;
		}

		if ( $is_list_ending )
		{
			$value = DOC::$inside_listing['value'][1] ?? '' ;
			$type = str_replace( '§value§' , $value , DOC::$inside_listing['type'] ) ; 
			DOC::$html .= '<li';
				DOC::$html .= ' value="'.$value.'"';
				DOC::$html .= ' style="list-style-type:'.$type.'; margin-left:'.DOC::$inside_listing['depth'].'ch;"';
			DOC::$html .= '><span>';
			DOC::$html .= content_to_html( DOC::$content );
			DOC::$html .= '</span></li>'.PHP_EOL;

			DOC::$html .= '</ul>'.PHP_EOL.PHP_EOL;
			DOC::$inside_listing = false ;
			DOC::$content = '';
			return true ;
		}

		return false ;
	}

	static function parse_title_line( $line ) : bool
	{
		global $title_tags ;

		$beg = 0 ; while( is_space( $line[ $beg ] ?? '!' ) ) $beg++;
		$tag = $line[ $beg ] ?? '';
		$is_title = isset( $title_tags[ $tag ] ) && count_at( $line , $beg ) >= 2 ;

		$is_opening = $is_title && ( DOC::$inside_title === false || DOC::$inside_title['tag'] != $tag ) ;
		$is_closing = DOC::$inside_title !== false  && ( ! $is_title || DOC::$inside_title['tag'] != $tag ) ;

		if ( $is_closing ) 
		{
			if ( trim( DOC::$content ) == '' )
			{
				DOC::$html .= '<hr';
				if ( isset( $title_tags[ DOC::$inside_title['tag'] ]['hr_class'] ) )
				{
					DOC::$html .= ' class="'.$title_tags[ DOC::$inside_title['tag'] ]['hr_class'] .'"';
				}
				DOC::$html .= '/>'.PHP_EOL.PHP_EOL;
			}
			else
			{
				if ( CONFIG::$auto_summary )
				{
					DOC::$inside_title['attributes'] .= CONFIG::$auto_summary_title_attribute ;
				}

				DOC::$html .= open_tags( $title_tags[ DOC::$inside_title['tag'] ]['tags'] , DOC::$inside_title['attributes'] );

				DOC::$html .= content_to_html( DOC::$content , $title_tags[ DOC::$inside_title['tag'] ]['formating'] );

				if ( $title_tags[ DOC::$inside_title['tag'] ]['summary'] )
				{
					DOC::$summary_index[] = [ 'content' => DOC::$content , 'indent' => $title_tags[ DOC::$inside_title['tag'] ]['summary_indent'] ];
				}

				DOC::$html .= close_tags( $title_tags[ DOC::$inside_title['tag'] ]['tags'] ) .PHP_EOL.PHP_EOL;
			}

			DOC::$content = '' ;
			DOC::$inside_title = false ;
		}

		if ( $is_opening )
		{
			DOC::flush_paragraph();

			$attributes = 'id="' . CONFIG::$summary_prefix . count( DOC::$summary_index ) . '"';
			if ( $beg > 0 ) $attributes .= ' data-centered=true';

			DOC::$inside_title = [ 'tag' => $tag , 'attributes' => $attributes , 'count' => count_at( $line , $beg ) ];
		}

		if ( DOC::$inside_title !== false )
		{
			$line = trim( trim_tag( trim( $line ) , $tag ) ) ;
			DOC::$content .= $line.PHP_EOL;
		}

		return $is_title ;
	}

	static function parse_block_line( string $line ) : bool
	{
		global $block_tags ;

		$tag = $line[ 0 ] ?? ''; 

		$is_block_tag = isset( $block_tags[ $tag ] ) && count_at( $line , 0 ) >= 4 ; 
		$is_block_beg = $is_block_tag && DOC::$inside_block === false ;
		$is_block_end = $is_block_tag && DOC::$inside_block !== false && DOC::$inside_block == $tag || $line == '' && DOC::$pos >= DOC::$src_len && DOC::$inside_block !== false ;

		if ( $is_block_beg )
		{
			DOC::flush_paragraph();
			DOC::flush_listing();
			DOC::flush_title();

			DOC::$inside_block = $tag ;

			DOC::$inside_block_variant = $block_tags[ $tag ] ;

			$attributes = tag_attributes_to_object( $tag , $block_tags[ $tag ] , $line );

			if ( isset( $block_tags[ $tag ]['variants'] ) )
			{
				for( $v = 0 ; $v < count( $block_tags[ $tag ]['variants'] ) ; $v++ )
				{
					$variant = $block_tags[ $tag ]['variants'][ $v ];

					if ( regex_test( $variant['detector'] , $attributes[ $variant['test_attribute'] ] ) )
					{
						DOC::$inside_block_variant = $variant ;
						$attributes = tag_attributes_to_object( $tag , $variant , $line );

						break;
					}
				}
			}

			DOC::$inside_block_attributes = $attributes ;
			
			DOC::$html .= open_tags( DOC::$inside_block_variant['tags'] , attributes_object_to_html( $attributes ) );
			return true;
		}

		if ( $is_block_end )
		{
			DOC::$content = content_to_html( DOC::$content , DOC::$inside_block_variant['formating'] ) ;

			if ( isset( DOC::$inside_block_variant['callback'] ) && is_callable( DOC::$inside_block_variant['callback'] ) )
			{
				DOC::$content = DOC::$inside_block_variant['callback']( DOC::$content , DOC::$inside_block_attributes );
			}

			DOC::$html .= DOC::$content ;
			DOC::$html .= close_tags( DOC::$inside_block_variant['tags'] ) .PHP_EOL.PHP_EOL;
			DOC::$content = '';
			DOC::$inside_block = false ;
			return true ;
		}

		if ( DOC::$inside_block !== false )
		{
			DOC::$content .= $line .PHP_EOL;
			return true ;
		}

		return false ;
	}

	static function parse_paragraph_line( $line ) : bool
	{
		$first = $line[ 0 ] ?? '';
		$count = count_at( $line , 0 );

		$line = trim( $line );

		$is_begining = DOC::$content == '' ;
		$is_ending   = $line == '' && DOC::$content != '';


		if ( $is_ending )
		{
			DOC::flush_paragraph();
		}
		else
		{
			if ( $is_begining ) 
			{
				if ( is_space( $first ) )
				{
					switch( $count )
					{
						case 1 :
							DOC::$content = '\\ ';
							DOC::$inside_paragraph = 'left' ;
						break;
						case 2 :
							DOC::$content = '\\ ';
							DOC::$inside_paragraph = 'justify' ;
						break;
						default:
					 		DOC::$inside_paragraph = 'center' ;
					}
				}
				else
				{
					DOC::$inside_paragraph = 'left' ;
				}
			}
			else
			{
				DOC::$content .= ' ';
			}
			DOC::$content .= DOC::$line ;
		}

		return DOC::$content != '' ;
	}
}

function to_html( string $src  ) : string
{
	DOC::$summary_index = [];
	DOC::$footnote_index = [];

//	DOC::$src = str_replace( [ "\r\n" , "\r" ] , "\n" , $src );
	DOC::$src = $src; //str_replace( [ "\r\n" , "\r" ] , "\n" , $src )
	DOC::$src_len = strlen( DOC::$src );

	do { DOC::read_line(); } 
	while( 
		DOC::parse_block_line( DOC::$line ) 
		|| 
		DOC::parse_listing_line( DOC::$line ) 
		|| 
		DOC::parse_title_line( DOC::$line ) 
		|| 
		DOC::parse_paragraph_line( DOC::$line ) 
		|| 
		DOC::$pos < DOC::$src_len
	); 

	if ( CONFIG::$auto_footnote )
	{		
		if ( ! empty( DOC::$footnote_index ) )
		{
			$title_id = CONFIG::$footnote_prefix . 'list' ;

			$TOC = '<a name="' . $title_id . '" id="' . $title_id . '"></a>' ;

			DOC::parse_title_line( CONFIG::$footnote_title ); 
			DOC::flush_title();

			$TOC .= PHP_EOL.'<ul>'.PHP_EOL;

			foreach( DOC::$footnote_index  as $key => $val )
			{
				$TOC .= '<li>' . content_to_html( $key ) ;
				$TOC .= '[<a href="#' . CONFIG::$footnote_return_prefix . $key . '" id="'. CONFIG::$footnote_prefix . $key .'">&uarr;</a>] : ';
				$TOC .= content_to_html( $val ) ;
				$TOC .= '</li>'.PHP_EOL;

			}
			$TOC .= '</ul>'.PHP_EOL;

			DOC::$html .= $TOC;
		}
	}

	if ( CONFIG::$auto_summary && count( DOC::$summary_index ) > 0 )
	{
		$TOC = '<details id="'.CONFIG::$summary_prefix.'summary"><summary>Menu</summary><nav id="'.CONFIG::$summary_prefix.'list"><ul>'.PHP_EOL;
		for( $i = 0 ; $i < count( DOC::$summary_index ) ; $i++ )
		{
			$entry = trim( DOC::$summary_index[ $i ]['content'] ); 
			$entry = content_to_html( $entry , 'summary' ); 

			$TOC .= '<li style="margin-left:' . DOC::$summary_index[ $i ]['indent'] . 'ch;"><a href="#' . CONFIG::$summary_prefix . $i . '">' . $entry . '</a></li>'.PHP_EOL;
		}
		$TOC .= '</ul></nav></details>'.PHP_EOL;

		DOC::$html = $TOC . DOC::$html ;
	}

	return DOC::$html;
}
// EOF
