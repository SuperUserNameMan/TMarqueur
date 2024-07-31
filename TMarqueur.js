/**
	||TMarqueur|| is an alternative lightweight markup language 
	for creating formatted text using a plain-text editor.

	||TMarqueur.js|| is used to convert TMarqueur documents into HTML.
*/


var TMarqueur = // "Et s’il n’en reste qu’un, je serai celui-là !"
{ 
	VERSION : "0.1.202407311135" ,
	NAME : "TMarqueur aka Terminajones' Marqueur" ,
	COPYRIGHT : 'IDGAF' ,
	LICENSE : "This project is released under the IDGAF LICENSE v1.0 that you can fully admire here : https://github.com/SuperUserNameMan/IDGAF_LICENSE",

	THIRD_PARTY_MODIFICATIONS_LIST : [ 
	/// XXX if you alter the original source code, summarize your modifications in here, and identify yourself
	],
};

TMarqueur.disable_custom_attr = true ; /// disable custom attributes added to blocks
TMarqueur.disable_html_blocks = true ; /// disable blocks that accept raw HTML content 

TMarqueur.tab_size = 4 ; /// how many spaces for tab ?

TMarqueur.auto_summary = true ;
TMarqueur.auto_footnote = true ;

TMarqueur.summary_prefix  = 'goto_'; /// prefix to automated titles anchor used to build the table of content
TMarqueur.footnote_prefix = 'footnote-'; /// prefix to footnote anchor
TMarqueur.footnote_return_prefix = 'ref-footnote-'; /// prefix to linkback to footnote reference anchor

TMarqueur.auto_summary_title_attribute = ' ondblclick="location.replace(\'#\' + TMarqueur.summary_prefix + \'summary\');"' ; /// this attribute is added to titles if TMarqueur.auto_summary

TMarqueur.summary_index = [];
TMarqueur.footnote_index = {}; //XXX associative array

TMarqueur.footnote_title = "== Footnotes : ==" ;

TMarqueur.html_special_chars = 
	[
		[ '&' , '&amp;'],
		[ '<' , '&lt;' ],
		[ '>' , '&gt;' ],
	];

TMarqueur.text_to_html =
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


		[ '[STAR]' , '⭐' ],
		[ '[star]' , '⭐&#xFE0E;' ],

		[ '[0-STAR]' , '&#x2001;' ],
		[ '[0-star]' , '&#x2001;' ],

		[ '[0-STARS]' , '&#x2001;' ],
		[ '[0-stars]' , '&#x2001;' ],

		[ '[1-STAR]' , '⭐' ],
		[ '[1-star]' , '⭐&#xFE0E;' ],

		[ '[1-STARS]' , '⭐' ],
		[ '[1-stars]' , '⭐&#xFE0E;' ],

		[ '[2-STARS]' , '⭐⭐' ],
		[ '[2-stars]' , '⭐&#xFE0E;⭐&#xFE0E;' ],

		[ '[3-STARS]' , '⭐⭐⭐' ],
		[ '[3-stars]' , '⭐&#xFE0E;⭐&#xFE0E;⭐&#xFE0E;' ],

		[ '[4-STARS]' , '⭐⭐⭐⭐' ],
		[ '[4-stars]' , '⭐&#xFE0E;⭐&#xFE0E;⭐&#xFE0E;⭐&#xFE0E;' ],

		[ '[5-STARS]' , '⭐⭐⭐⭐⭐' ],
		[ '[5-stars]' , '⭐&#xFE0E;⭐&#xFE0E;⭐&#xFE0E;⭐&#xFE0E;⭐&#xFE0E;' ],

	];

TMarqueur.escape_seq = 
	{
		'\\' :  '&#92;' ,

		'n' : '<br/>' ,

		' ' : '&nbsp;',

		'`' : '&#96;' ,
	};

TMarqueur.title_tags = 
	{
		/// .tags : contains the list of HTML tags that will surround the content		
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
		
		'#' : { tags : [ 'h1' ] , formating : true , summary : true , summary_indent : 0 , hr_class : 'hr_type_1' },
		'=' : { tags : [ 'h2' ] , formating : true , summary : true , summary_indent : 2 , hr_class : 'hr_type_2' },
		'+' : { tags : [ 'h3' ] , formating : true , summary : true , summary_indent : 4 , hr_class : 'hr_type_3' },
		'-' : { tags : [ 'h4' ] , formating : true , summary : true , summary_indent : 6 , hr_class : 'hr_type_4' },

		'>' : { tags : [ 'blockquote' , 'pre' ] , formating : true , summary : false }, // not title, but behave the same
	};

TMarqueur.block_tags = 
	{
		/// .tags : contains the list of HTML tags that will surround the content
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

 		'"'  : { type : 'quote' , tags : [ 'blockquote' ] , attr : [ 'title' , 'cite' , 'date' , 'source' ] , formating : true  },
		'\'' : { type : 'quote-raw' , tags : [ 'blockquote' ] , attr : [ 'title' , 'cite' , 'date' , 'source' ] , formating : false },

		'<' : { type : 'textarea' , tags : [ 'textarea' ] , attr : [ 'name' , 'title' ] , formating : false },

		';' : { type : 'div' , tags : [ 'div' ] , formating : true , attr : [ 'class' , 'id' ], custom_attr : true },

		':' : { type : 'div-HTML' , tags : [ 'div' ] , formating : 'HTML' , attr : [ 'class' , 'id' ], custom_attr : true  },

		'`'  : { type : 'code' , tags : [ 'pre','code' ] , attr : [ 'lang' , 'title' , 'source' ] , formating : false , // TODO choose to which tag goes the attributes
			attr_defaults : {
				'lang' : 'raw-text',
				//'title' : function( attr_val ) { return attr_val[ 'lang' ] || '' ; },
			},

			//variants : [
			//	{ type : 'table' , test_attribute : 'lang' , detector : /^table[ ]*(.*)$/i ,
			//		tags : [ 'div' ] , attr : [ 'title' ] , formating : true , callback : 'TMarqueur_content_callback_for_block_code_variant_table' 
			//	},
			//],
		}, 
	};

TMarqueur.format_tags = 
	{ 
		/// .tags : contains the list of HTML tags that will surround the content
		/// .formating : true | false | 'HTML'
		///		- true   = replaces HTML entities and applies inline formating ;
		///		- false  = only replace HTML special entities ;
		///		- 'HTML' = keep the content untouched (the content is raw HTML code) ;

		'*'  : { tags : [ 'b' ] , formating : true }, // **bold**
		'_'  : { tags : [ 'u' ] , formating : true }, // __underline__
		'~'  : { tags : [ 's' ] , formating : true }, // ~~strike~~
		'\'' : { tags : [ 'i' ] , formating : true }, // ''italic''
		'"'  : { tags : [ 'q' ] , formating : true }, // ""quote""
		
		'^'  : { tags : [ 'sup' ] , formating : true }, // ^^superscript^^
		','  : { tags : [ 'sub' ] , formating : true }, // ,,subscript,,

		'%'  : { tags : [ 'mark' ] , formating : true }, // %%marked%%

		'`'  : { tags : [ 'samp' ] , formating : false }, // ``computer``

		'|'  : { tags : [ 'em' ] , formating : true }, // ||emphasised||

		'$'  : { tags : [ 'strong' ] , formating : true }, // $$important$$

		'/'  : { tags : [ 'span' ] , formating : false }, // //unformated//
	};

TMarqueur.brackets_tags = 
	{
		/// .terminator : contains the ending bracket
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
		'[' : {
				type : 'link',
				terminator : ']' , html : '<a href="!§url§" target="!§target§" title="!§url§">§content§</a>' , 
				params_order    : [ '§content§' , '!§url§' , '!§target§' ] , 
				params_defaults : { 
					'!§url§' : function( _params ) { return _params['§content§']; } , 
					'!§target§' : function( _params ) 
					{ 
						val_ = _params['!§url§'];
						while( typeof val_ == 'function' ) val_ = val_( _params );
						return val_.startsWith('http') && '_blank' || '_self' ; 
					} ,
				},
				params_validator : {
					'!§url§' : /^(http|https|ftp|mailto):.*$|^#[^ ]*$|^[^:/s]+$/i,
					'!§target§' : /^(_self|_blank)$/,
				},
				variants : [
					// [[^footnote-id / title ]]
					{ 	type : 'footnote-link' , 
						test_param : '§content§' , 
						detector : /^[\^][0-9a-zA-Z\-_]+$/i ,
						html : function() { return '<sup>[<a href="#' + TMarqueur.footnote_prefix + '!§id§" title="!§title§" id="'+ TMarqueur.footnote_return_prefix +'!§id§">!§id§</a>]</sup>'; }, 

						params_order    : [ '!§id§' , '!§title§' ] , 
						params_defaults : {
							'!§title§' : '',
						},
						params_filters  : { '!§id§' : /^[\^](.*)$/ } ,
						on_parse_callback : function( _params ) { 
								if ( _params['!§title§'] != '' )
								{
									TMarqueur.footnote_index[ _params['!§id§'] ] = _params['!§title§'] ; 
								}
						},
					},
					// [[:footnote-id / content ]]
					{	type : 'footnote-ref' , 
						test_param : '§content§' , 
						detector : /^[:][0-9a-zA-Z\-_]+$/i ,
						html : '' , 
						params_order    : [ '!§id§' , '!§title§' ] , 
						params_defaults : {
							'!§title§' : '',
						},
						params_filters  : { '!§id§' : /^[:](.*)$/ } ,
						on_parse_callback : function( _params ) { 
								if ( _params['!§title§'] != '' )
								{
									TMarqueur.footnote_index[ _params['!§id§'] ] = _params['!§title§'] ; 
								}
						},
					},
					

					// [[? input-type / input-name / input-default / input-placeholder ]] // TODO
					/*{	type : 'input' , 
						test_param : '§content§' , 
						detector : /^[?]\s*[0-9a-zA-Z\-_]+$/i ,
						html : '<input type="!§type§" name="!§name§" value="!§value§" placeholder="!§placeholder§" title="!§placeholder§"/>' , 
						params_order    : [ '!§type§' , '!§name§' , '!§value§' , '!§placeholder§' ] , 
						params_defaults : {
							'!§placeholder§' : '',
							'!§value§' : function( _params ) { return _params['!§name§']; },
							'!§name§' : function( _params ) { 
								TMarqueur.__input_counters = TMarqueur.__input_counters || 0 ;
								TMarqueur.__input_counters++;
								return 'TMarqueur-'+ _params['!§type§'] +'-' + TMarqueur.__input_counters;
							},
						},
						params_filters  : { '!§type§' : /^[?]\s*(.*)$/ } ,
					},*/

				],
		},

		// {{ abbreviation / definition }}
		'{' : { type : 'abbreviation', terminator : '}' , html : '<abbr title="!§title§">§content§</abbr>' , params_order : [ '§content§' , '!§title§' ] },

		// (( anchor ))
		'(' : { type : 'anchor' , terminator : ')' , html : '<a name="!§anchor§" id="!§anchor§" title="#!§anchor§">⚓</a>', 
				params_order : [ '!§anchor§' ],
		},

		// :: entity ::
		':' : { type : 'entity', terminator : ':' , html : '&§code§;' , params_order : [ '§code§' ] },

	};

TMarqueur.listing_tags =
	[
		/// type : is CSS code for 'list-style-type:'
		/// detector : is regex used to detect the begining of a list item, and to extracts its value if available

		/// NOTE : \\FE0E forces the text-mode display of the preceding emoji

		{ type : 'disc' ,                 detector : /^[ \t]*[*] /            }, // *
		{ type : 'circle' ,               detector : /^[ \t]*[@] /            }, // @
		{ type : 'square' ,               detector : /^[ \t]*[#] /            }, // #
		{ type : '\'-  \'',               detector : /^[ \t]*[-] /            }, // -

		{ type : 'disclosure-closed' ,    detector : /^[ \t]*[>] /            }, // >
		{ type : 'disclosure-open' ,      detector : /^[ \t]*[v] /i           }, // v   V
		{ type : '\'▲\\FE0E  \'' ,        detector : /^[ \t]*[\^] /           }, // ^

		{ type : '\'\\2192\\FE0E   \'' ,        detector : /^[ \t]*[\-][>] /        }, // ->
		{ type : '\'\\21E8\\FE0E   \'' ,        detector : /^[ \t]*[=][>] /         }, // =>
		{ type : '\'\\21B3\\FE0E   \'' ,        detector : /^[ \t]*[L][>] /         }, // L>

		{ type : '\'§value§. \'' , 		  detector : /^[ \t]*(([0-9]+[.]?)+)[.] /  }, // 1.  1.2.  1.2.3.
		{ type : '\'§value§) \'' , 		  detector : /^[ \t]*(([0-9]+[.]?)+)[)] /  }, // 1)  1.2)  1.2.3)

		{ type : '\'§value§. \'' ,        detector : /^[ \t]*([a-z])[.] /i   }, // a. A.
		{ type : '\'§value§) \'' ,        detector : /^[ \t]*([a-z])[)] /i   }, // a) A)
		{ type : '\'(§value§) \'' ,       detector : /^[ \t]*[(]([a-z]+)[)] /i   }, // (a) (A)


		{ type : '\'☐\\FE0E  \'',          detector : /^[ \t]*\[_\] /          }, // [_] ☐
		{ type : '\'☐\\FE0E  \'',          detector : /^[ \t]*\[\] /           }, // []
		{ type : '\'☐\\FE0E  \'',          detector : /^[ \t]*\[ \] /          }, // [ ]
		{ type : '\'☒\\FE0E  \'',          detector : /^[ \t]*\[x\] /i         }, // [x] 
		{ type : '\'☑\\FE0E  \'',          detector : /^[ \t]*\[v\] /i         }, // [v] ☑

		{ type : '\'⚐\\FE0E  \'',          detector : /^[ \t]*\[f\] /          }, // [f]
		{ type : '\'⚑\\FE0E  \'',          detector : /^[ \t]*\[F\] /          }, // [F]


		//{ type : '\'⚠\\FE0E  \'',          detector : /^[ \t]*\[\!\] /         }, // [!]
		//{ type : '\'ℹ\\FE0E   \'',        detector : /^[ \t]*\[i\] /          }, // [i]
		{ type : '\'⚠️ \'',          detector : /^[ \t]*\[\!\] /         }, // [!]
		{ type : '\'ℹ️ \'',        detector : /^[ \t]*\[i\] /          }, // [i]

	];

TMarqueur.attribute_separator = ' / ' ; /// used to separate blocks tag's attributes and bracket tag's parameters 
TMarqueur.attribute_keyval_seperator = ' : '; /// used to name the blocks tag's attributes. Ex : """"" cite : René Descartes / date : 1637 """"""

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
TMarqueur.plug_block_variant = function( _block_tag , _variant  ) 
{ 
	return TMarqueur._plug_variant( 'block_tags' , _block_tag , _variant ); 
}


TMarqueur.plug_brackets_variant = function( _brackets_tag , _variant  )
{
	return TMarqueur._plug_variant( 'brackets_tags' , _brackets_tag , _variant ); 
}


TMarqueur._plug_variant = function( _marker_type, _marker_tag , _variant  )
{
	if ( TMarqueur[ _marker_type ][ _marker_tag ] === undefined ) return false ;

	if ( TMarqueur[ _marker_type ][ _marker_tag ].variants === undefined ) TMarqueur[ _marker_type ][ _marker_tag ].variants = [];

	for( var i = 0 ; i < TMarqueur[ _marker_type ][ _marker_tag ].variants.length ; i++ )
	{
		if ( TMarqueur[ _marker_type ][ _marker_tag ].variants[ i ].type == _marker_tag )
		{
			TMarqueur[ _marker_type ][ _marker_tag ].variants[ i ] = _variant ;
			return true ;
		}
	}

	TMarqueur[ _marker_type ][ _marker_tag ].variants.push( _variant );
	return true ;
}

TMarqueur.is_eol = function( _c ) { return _c == '\n' || _c == '\r' ; } // TODO i dont remember if \n is default on all OS's webbrowsers ?
TMarqueur.is_space = function( _c ) { return _c == ' ' || _c == '\t' ; }

TMarqueur.count_at = function( _src , _pos )
	{
		var beg_ = _pos ;
		var c_ = _src[ _pos ];
		while( _pos < _src.length && _src[ _pos ] == c_ ) _pos++;
		return _pos - beg_ ;
	}

//TMarqueur.count_from = function( _src , _pos ) //TODO remove ?
//	{
//		var end_ = _pos ;
//		var c_ = _src[ _pos ];
//		while( 0 <= _pos && _src[ _pos ] == c_ ) _pos--;
//		return end_ - _pos ;
//	}


TMarqueur.line_length_at = function( _src , _pos )
	{
		var beg_ = _pos ;
		while( _pos < _src.length && ! TMarqueur.is_eol( _src[ _pos ] ) ) _pos++;
		return _pos - beg_ ;
	}

//TMarqueur.line_depth_at = function( _src , _pos ) // TODO remove ?
//	{
//		var beg_ = _pos ;
//		while( _pos < _src.length && TMarqueur.is_space( _src[ _pos ] ) ) _pos++;
//		return _pos - beg_ ;
//	}

TMarqueur.list_depth = function( _line )
	{
		var depth_ = 0 ;
		var pos_ = 0 ;
		while( pos_ < _line.length && TMarqueur.is_space( _line[ pos_ ] ) )
		{
			depth_ += ( _line[ pos_ ] == '\t' ) ? TMarqueur.tab_size : 1 ;
			pos_++;
		}
		return depth_ ;
	}

TMarqueur.ltrim = function( _str , _c ) { var beg_ = 0 ; while( beg_ < _str.length && _str[ beg_ ] == _c ) beg_++ ; return _str.substr( beg_ ); }
TMarqueur.rtrim = function( _str , _c ) { var end_ = _str.length - 1; while( 0 <= end_ && _str[ end_ ] == _c ) end_--; return _str.substr( 0 , end_ + 1 );	}
TMarqueur.trim  = function( _str , _c ) { return TMarqueur.ltrim( TMarqueur.rtrim( _str , _c ) , _c ); }

TMarqueur.escape_html_string = function( _val )
	{
		return _val.replace( /[\\]/g , '\\\\' ).replace( /["]/g , '\\"' ).replace( /[']/g , '\'' );
	}

TMarqueur.open_tags = function( _tags , _attributes )
	{
		var html_ = '';
		for( var i = 0 ; i < _tags.length ; i++ )
		{
			html_ += '<' + _tags[ i ] ;
			if ( _attributes !== undefined ) html_ += ' ' + _attributes;
			html_ += '>';
		}
		return html_ ;
	}

TMarqueur.close_tags = function( _tags )
	{
		var html_ = '';
		for( var i = _tags.length - 1 ; i >= 0 ; i-- )
		{
			html_ += '</' + _tags[ i ] + '>';
		}
		return html_;
	}


TMarqueur.tag_attributes_to_object = function( _tag , _variant , _line  )
	{
		if ( _variant.attr === undefined ) return undefined ;

		_line = TMarqueur.trim( _line.trim() , _tag ).trim();

		var attributes_ = _line.split( TMarqueur.attribute_separator );
		var obj_ = {};
		var key_ = '';
		var val_ = '';

		var custom_attr_ = ! TMarqueur.disable_custom_attr && _variant.custom_attr !== undefined && _variant.custom_attr ;

		for( var i = 0 ; i < attributes_.length ; i++ )
		{
			if ( attributes_[ i ].includes( TMarqueur.attribute_keyval_seperator ) )
			{
				key_ = attributes_[ i ].split( TMarqueur.attribute_keyval_seperator )[ 0 ].trim();
				val_ = attributes_[ i ].substr( key_.length + TMarqueur.attribute_separator.length ).trim();

				if ( ! custom_attr_ )
				{
					if ( _variant.attr.indexOf( key_ ) < 0 )
					{
						key_ = "data-" + key_ ;
					}
				}
			}
			else
			if ( _variant.attr[ i ] !== undefined )
			{
				key_ = _variant.attr[ i ];
				val_ = attributes_[ i ].trim();
			}
			else
			{
				key_ = "data-"+i;
				val_ = attributes_[ i ].trim();
			}

			obj_[ key_ ] = val_ ;
		}

		if ( _variant.attr_defaults !== undefined )
		{
			for( var i = 0 ; i < _variant.attr.length ; i++ )
			{
				var key_ = _variant.attr[ i ];

				if ( ( obj_[ key_ ] == undefined || obj_[ key_ ] == '' ) && _variant.attr_defaults[ key_ ] !== undefined )
				{
					obj_[ key_ ] = typeof _variant.attr_defaults[ key_ ] == 'function' ? _variant.attr_defaults[ key_ ]( obj_ ) : _variant.attr_defaults[ key_ ] ;
				}
			}
		}

		return obj_;
	}

TMarqueur.attributes_object_to_html = function( _obj )
	{
		if ( _obj === undefined ) return undefined ;

		var keys_ = Object.keys( _obj );
		var res_ = '';

		for( var i = 0 ; i < keys_.length ; i++ )
		{
			var key_ = keys_[ i ];
			var val_ = _obj[ key_ ];

			res_ += key_ + '="' + TMarqueur.escape_html_string( val_ ) + '" ';
		}

		return res_ ;
	}

TMarqueur.content_to_html = function( _content , _content_formating = true )
	{
		if ( _content_formating == 'HTML' && ! TMarqueur.disable_html_blocks ) return _content;

		for( var i = 0 ; i < TMarqueur.html_special_chars.length ; i++ )
		{
			var key_ = TMarqueur.html_special_chars[ i ][ 0 ];
			var val_ = TMarqueur.html_special_chars[ i ][ 1 ];
			
			_content = _content.replaceAll( key_ , val_ );
		}

		if ( _content_formating == false ) return _content ;

		var stack_ = [];

		var pos_ = 0 ;
		var html_ = '';

		var inner_formating_ = true ;

		while ( pos_ < _content.length )
		{
			var tag_ = _content[ pos_ ];
			var len_ = 1 ;
			var count_ = TMarqueur.count_at( _content , pos_ );

			if ( TMarqueur.format_tags[ tag_ ] !== undefined && count_ >= 2 )
			{
				if ( count_ >= 2 && ( inner_formating_ || stack_[ stack_.length - 1 ].tag == tag_ ) )
				{
					if ( stack_.length == 0 || stack_[ stack_.length - 1 ].tag != tag_ )
					{
						stack_.push( { tag : tag_ , pos : pos_ } );
						html_ += TMarqueur.open_tags( TMarqueur.format_tags[ tag_ ].tags );
						inner_formating_ = TMarqueur.format_tags[ tag_ ].formating ;
						tag_ = tag_.repeat( count_ - 2 );
					}
					else
					{
						html_ += tag_.repeat( count_ - 2 );
						stack_.pop();
						html_ += TMarqueur.close_tags( TMarqueur.format_tags[ tag_ ].tags );
						tag_ = '';
						inner_formating_ = true ;
					}
					len_ = count_ ;
				}
			}
			else
			if ( inner_formating_ && TMarqueur.brackets_tags[ tag_ ] !== undefined && count_ >= 2 )
			{
				var count_ = TMarqueur.count_at( _content , pos_ );
				var terminator_ = TMarqueur.brackets_tags[ tag_ ].terminator ;

				if ( count_ >= 2 )
				{
					var end_ = pos_ + count_ ;

					while
					( 
						end_ < _content.length 
						&& 
						(
							_content[ end_ ] != terminator_
							|| 
							( 
								_content[ end_ ] == terminator_
								&& 
								TMarqueur.count_at( _content , end_ ) != 2 
							) 
						) 
					) end_++ ;

					while( end_ < _content.length && _content[ end_ ] == terminator_ ) end_++;

					var params_ = _content.substring( pos_ , end_ );
					len_ = params_.length;

					params_ = TMarqueur.rtrim( TMarqueur.ltrim( params_ , tag_ ) , TMarqueur.brackets_tags[ tag_ ].terminator ).trim().split( TMarqueur.attribute_separator );

					var variant = TMarqueur.brackets_tags[ tag_ ] ; 

					var vals_ = {};
					var out_ = '';

					if ( _content_formating != 'summary' )
					{

						for( var i = 0 ; i < variant.params_order.length ; i++ )
						{
							var key_ = variant.params_order[ i ];
							var val_ = '';

							if ( params_[ i ] !== undefined )
							{
								val_ = params_[ i ].replace(/[\r\n\s]+/g,' ').replaceAll('\\n', '\n').trim() ;
							}
							else
							if ( variant.params_defaults !== undefined && variant.params_defaults[ key_ ] !== undefined )
							{
								val_ = variant.params_defaults[ key_ ] ;
							}

							vals_[ key_ ] = val_ ; 
						}

						if ( variant.variants !== undefined )
						{
							for( var i = 0 ; i < variant.variants.length ; i++ )
							{
								if ( variant.variants[ i ].detector.test( vals_[ variant.variants[ i ].test_param ] ) )
								{
									variant = variant.variants[ i ];
									break;
								}
							}

							for( var i = 0 ; i < variant.params_order.length ; i++ )
							{
								var key_ = variant.params_order[ i ];
								var val_ = '';

								if ( params_[ i ] !== undefined )
								{
									val_ = params_[ i ].replace(/[\r\n\s]+/g,' ').replaceAll('\\n', '\n').trim() ;
								}
								else
								if ( variant.params_defaults !== undefined && variant.params_defaults[ key_ ] !== undefined )
								{
									val_ = variant.params_defaults[ key_ ] ;
								}

								vals_[ key_ ] = val_ ; 
							}
						}

						out_ = variant.html;
						if ( typeof out_ == 'function' ) out_ = out_();

						for( var i = 0 ; i < variant.params_order.length ; i++ )
						{
							var key_ = variant.params_order[ i ];
							var val_ = vals_[ key_ ];

							while( typeof val_ == 'function' ) val_ = val_( vals_ );

							if ( variant.params_validator !== undefined && variant.params_validator[ key_ ] !== undefined )
							{
								if ( ! variant.params_validator[ key_ ].test( val_ ) )
								{
									val_ = '';
								}
							}

							if ( variant.params_filters !== undefined && variant.params_filters[ key_ ] !== undefined )
							{
								val_ = variant.params_filters[ key_ ].exec( val_ )[1] || '';
								vals_[ key_ ] = val_ ; 
							}

							if ( variant.params_order[ i ][0] == '!' )
							{

								val_ = TMarqueur.escape_html_string( val_ );
							}

							out_ = out_.replaceAll( variant.params_order[ i ] , val_ );
						}

						if ( typeof variant.on_parse_callback == 'function' )
						{
							variant.on_parse_callback( vals_ );
						}
					}

					tag_ = out_ ;
				}
			}
			else
			if ( tag_ == '\\' && inner_formating_ == true )
			{
				var next = _content[ pos_ + 1 ];

				if ( next !== undefined )
				{
					if ( TMarqueur.escape_seq[ next ] !== undefined )
					{
						tag_ = TMarqueur.escape_seq[ next ];
						len_ = 2 ;
					}
					else
					if ( TMarqueur.format_tags[ next ] !== undefined )
					{
						tag_ = '&#' + next.charCodeAt() + ';' ;
						len_ = 2 ;
					}
				}
			}
			else
			if ( inner_formating_ == true && tag_ != ' ' )
			{
				for( var i = 0 ; i < TMarqueur.text_to_html.length ; i++ )
				{
					var key_ = TMarqueur.text_to_html[ i ][0];

					if ( tag_ == key_[0] && _content.substr( pos_ , key_.length ) == key_ )
					{
						tag_ = TMarqueur.text_to_html[ i ][1];
						len_ = key_.length;
						break;
					}
				}
			}

			html_ += tag_ ;
			pos_ += len_ ;
		}

		while( stack_.length )
		{
			html_ += TMarqueur.close_tags( TMarqueur.format_tags[ stack_.pop().tag ].tags );
		}

		return html_ ;
	}

TMarqueur.is_listing_bullet = function( _line )
	{
		for( var i = 0 ; i < TMarqueur.listing_tags.length ; i++ )
		{
			if ( TMarqueur.listing_tags[ i ].detector.test( _line ) ) 
			{
				return { type : TMarqueur.listing_tags[ i ].type , value : TMarqueur.listing_tags[ i ].detector.exec( _line ) , depth : TMarqueur.list_depth( _line ) };
			}
		}

		return false;
	}


TMarqueur.to_html = function( src  )
{
	TMarqueur.summary_index = [];
	TMarqueur.footnote_index = {}; //XXX associative array

	function flush_paragraph()
	{
		if ( false === inside_paragraph || content == '' ) return ;

		html += '<p style="text-align:' + inside_paragraph + '">' ;
		html += TMarqueur.content_to_html( content ) ;
		html += '</p>\n\n' ;

		content = '';
		inside_paragraph = false ;
	}

	function flush_listing()
	{
		if ( false === inside_listing ) return ;
		parse_listing('');
	}

	function flush_title()
	{
		if ( false === inside_title ) return ;
		parse_title('');
	}

	// ------------

	var html = '';
	var content = '';
	var line = '';
	var pos = 0 ;
	var inside_title = false ;
	var inside_listing = false ;
	var inside_block = false ; var inside_block_variant = undefined ; var inside_block_attributes = undefined ;
	var inside_paragraph = false ;


	function read_line()
	{
		line = src.substring( pos , pos + TMarqueur.line_length_at( src , pos ) ); 
		pos += line.length ;
		if ( src[ pos ] == '\r' ) pos++;
		if ( src[ pos ] == '\n' ) pos++;
		return true ; 
	}

	function parse_listing( line )
	{
		var is_bullet = TMarqueur.is_listing_bullet( line ); // * ....

		var is_empty_line  = line.trim() == '' ;

		var is_first_item  = inside_listing === false && is_bullet !== false ; // <ul><li> ...
		var is_multiline   = inside_listing !== false && is_bullet === false && ! is_empty_line ; // ...
		var is_next_item   = inside_listing !== false && is_bullet !== false ; // ...</li><li>...
		var is_list_ending = inside_listing !== false && is_empty_line ; // ... </li></ul>

		if ( is_first_item )
		{
			flush_title() ;
			flush_paragraph() ;
			html += '<ul>\n' ;
			content = line.substr( is_bullet.value[0].length );
			inside_listing = is_bullet ;
			return true ;
		}

		if ( is_multiline )
		{
			content += '\n' + line ;
			return true ; 
		}

		if ( is_next_item )
		{
			var value = inside_listing.value[1] || '' ;
			var type = inside_listing.type.replace( '§value§' , value ) ; 
			html += '<li';
				html += ' value="'+ value +'"';
				html += ' style="list-style-type:' + type + '; margin-left:'+ inside_listing.depth + 'ch;"';
			html += '><span>';
			html += TMarqueur.content_to_html( content );
			html += '</span></li>\n';

			content = line.substr( is_bullet.value[0].length );
			inside_listing = is_bullet ;

			return true ;
		}

		if ( is_list_ending )
		{
			var value = inside_listing.value[1] || '' ;
			var type = inside_listing.type.replace( '§value§' , value ) ; 
			html += '<li';
				html += ' value="'+ value +'"';
				html += ' style="list-style-type:' + type + '; margin-left:'+ inside_listing.depth + 'ch;"';
			html += '><span>';
			html += TMarqueur.content_to_html( content );
			html += '</span></li>\n';

			html += '</ul>\n\n';
			inside_listing = false ;
			content = '';
			return true ;
		}

		return false ;
	}

	function parse_title( line )
	{
		var beg = 0 ; while( TMarqueur.is_space( line[ beg ] ) ) beg++;
		var tag = line[ beg ] ;
		var is_title = TMarqueur.title_tags[ tag ] !== undefined && TMarqueur.count_at( line , beg ) >= 2 ;

		var is_opening = is_title && ( inside_title === false || inside_title.tag != tag ) ;
		var is_closing = inside_title !== false  && ( ! is_title || inside_title.tag != tag ) ;

		if ( is_closing ) 
		{
			if ( content.trim() == '' )
			{
				html += '<hr';
				if ( TMarqueur.title_tags[ inside_title.tag ].hr_class !== undefined )
				{
					html += ' class="'+ TMarqueur.title_tags[ inside_title.tag ].hr_class +'"';
				}
				html += '/>\n\n';
			}
			else
			{
				if ( TMarqueur.auto_summary )
				{
					inside_title.attributes += TMarqueur.auto_summary_title_attribute ;
				}


				html += TMarqueur.open_tags( TMarqueur.title_tags[ inside_title.tag ].tags , inside_title.attributes );

				html += TMarqueur.content_to_html( content , TMarqueur.title_tags[ inside_title.tag ].formating );

				if ( TMarqueur.title_tags[ inside_title.tag ].summary )
				{
					TMarqueur.summary_index.push( { content : content , indent : TMarqueur.title_tags[ inside_title.tag ].summary_indent } );
				}


				html += TMarqueur.close_tags( TMarqueur.title_tags[ inside_title.tag ].tags ) + '\n\n';
			}

			content = '' ;	
			inside_title = false ;
		}

		if ( is_opening )
		{
			flush_paragraph();

			var attributes = 'id="' + TMarqueur.summary_prefix + TMarqueur.summary_index.length + '"';
			if ( beg > 0 ) attributes += ' data-centered=true';

			inside_title = { tag : tag , attributes : attributes , count : TMarqueur.count_at( line , beg ) };
		}

		if ( inside_title !== false )
		{
			line = TMarqueur.trim( line.trim() , tag ).trim() ;
			content += line + '\n';
		}

		return is_title ;
	}

	function parse_block( line )
	{
		var tag = line[0]; 
				
		var is_block_tag = TMarqueur.block_tags[ tag ] !== undefined && TMarqueur.count_at( line , 0 ) >= 4 ; 
		var is_block_beg = is_block_tag && inside_block === false ;
		var is_block_end = is_block_tag && inside_block !== false && inside_block == tag || line == '' && pos >= src.length && inside_block !== false ;

		if ( is_block_beg )
		{
			flush_paragraph();
			flush_listing();
			flush_title();

			inside_block = tag ;

			inside_block_variant = TMarqueur.block_tags[ tag ] ;

			var attributes = TMarqueur.tag_attributes_to_object( tag , TMarqueur.block_tags[ tag ] , line );

			if ( TMarqueur.block_tags[ tag ].variants !== undefined )
			{
				for( var v = 0 ; v < TMarqueur.block_tags[ tag ].variants.length ; v++ )
				{
					var variant = TMarqueur.block_tags[ tag ].variants[ v ];

					if ( variant.detector.test( attributes[ variant.test_attribute ] ) )
					{
						inside_block_variant = variant ;
						attributes = TMarqueur.tag_attributes_to_object( tag , variant , line );

						break;
					}
				}
			}

			inside_block_attributes = attributes ;
			
			html += TMarqueur.open_tags( inside_block_variant.tags , TMarqueur.attributes_object_to_html( attributes ) );
			return true;
		}

		if ( is_block_end )
		{
			content = TMarqueur.content_to_html( content , inside_block_variant.formating ) ;

			if ( typeof inside_block_variant.callback == 'function' )
			{
				content = inside_block_variant.callback( content , inside_block_attributes );
			}
			else
			if ( typeof inside_block_variant.callback == 'string' && typeof window[ inside_block_variant.callback ] === 'function' )
			{
				content = window[ inside_block_variant.callback ]( content , inside_block_attributes );
			}

			html += content ;
			html += TMarqueur.close_tags( inside_block_variant.tags ) + '\n\n';
			content = '';
			inside_block = false ;
			return true ;
		}

		if ( inside_block !== false )
		{
			content += line + '\n';
			return true ;
		}

		return false ;
	}

	function parse_paragraph( line )
	{
		var first = line[ 0 ];
		var count = TMarqueur.count_at( line , 0 );

		line = line.trim();

		var is_begining = content == '' ;
		var is_ending   = line == '' && content != '';


		if ( is_ending )
		{
			flush_paragraph();
		}
		else
		{
			if ( is_begining ) 
			{
				if ( TMarqueur.is_space( first ) )
				{
					switch( count )
					{
						case 1 :
							content = '\\ ';
							inside_paragraph = 'left' ;
						break;
						case 2 :
							content = '\\ ';
							inside_paragraph = 'justify' ;
						break;
						default:
					 		inside_paragraph = 'center' ;
					}
				}
				else
				{
					inside_paragraph = 'left' ;
				}
			}
			else
			{
				content += ' ';
			}
			content += line ;
		}

		return  content != '' ;
	}


	do { read_line() } while( ( parse_block( line ) || parse_listing( line ) || parse_title( line ) || parse_paragraph( line ) || pos < src.length ) ); 

	if ( TMarqueur.auto_footnote )
	{
		var keys = Object.keys( TMarqueur.footnote_index );
		
		if ( keys.length > 0 )
		{
			var title_id = TMarqueur.footnote_prefix + 'list' ;

			var TOC = '<a name="' + title_id + '" id="' + title_id + '"></a>' ;

			parse_title( TMarqueur.footnote_title ); flush_title();

			TOC += '\n<ul>\n';

			for( var i = 0 ; i < keys.length ; i++ )
			{
				var key = keys[ i ];
				var val = TMarqueur.footnote_index[ key ];

				TOC += '<li>' + TMarqueur.content_to_html( key ) ;
				TOC += '[<a href="#' + TMarqueur.footnote_return_prefix + key + '" id="'+ TMarqueur.footnote_prefix + key +'">&uarr;</a>] : ';
				TOC += TMarqueur.content_to_html( val ) ;
				TOC += '</li>\n';

			}
			TOC += '</ul>\n';

			html = html + TOC;
		}
	}

	if ( TMarqueur.auto_summary && TMarqueur.summary_index.length > 0 )
	{
		var TOC = '<details id="'+TMarqueur.summary_prefix+'summary"><summary>Menu</summary><nav id="'+TMarqueur.summary_prefix+'list"><ul>\n';
		for( var i = 0 ; i < TMarqueur.summary_index.length ; i++ )
		{
			var content = TMarqueur.summary_index[ i ].content.trim(); 
//			content = TMarqueur.trim( content , ':' );
			content = TMarqueur.content_to_html( content , 'summary' ); 

			TOC += '<li style="margin-left:' + TMarqueur.summary_index[ i ].indent + 'ch;"><a href="#' + TMarqueur.summary_prefix + i + '">' + content + '</a></li>\n';
		}
		TOC += '</ul></nav></details>\n';

		html = TOC + html ;
	}



	return html;
}

// EOF
