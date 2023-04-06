/**
	||TMarqueur|| is an alternative lightweight markup language 
	for creating formatted text using a plain-text editor.

	||TMarqueur.js|| converts TMarqueur document to HTML.

	||TMarqueur.tables.js|| adds a variant to the preformated 
	code block to convert its content into a HTML table.

	Example :


```````````````` table 1.2.3 ```````````````
			 caption text of the table
			will be displayed at the top

	+-----------+-----------+------------------+
	! colonne A ! colonne B ! longue colonne C !
	+-----------+-----------+------------------+
	| value 1   | 123       | blab blaablab    |
	| value 2   | 456       | blab blaablab    |
	| value 3   | 789       | blab blaablab    |
	+-----------+-----------+------------------+

		this text will also be displayed
		 under the HTML table.

````````````````````````````````````````````

	Notes :
	- cols starting with ``!`` are translated to ``<th>`` ;
	- rows separators (starting with ``+`` or ``-``) are optional and ignored ;
	- the value of the title attribute ("table 1.2.3" in the example) will be added at the begining of the ``<caption>`` ;
	- if a caption text is defined afterward, a ``:`` will be added to the title as a separator ;
	- the bottom text (legend) is optional, and will be added into a ``<tfoot><tr class="legend"><col colspan="xxx">`` ;

*/


TMarqueur.plug_block_variant( '`' , { 
	type : 'table' , 
	test_attribute : 'lang' , 
	detector : /^table[ ]*(.*)$/i ,

	tags : [ 'div class="table-div"' ] , // TODO add support for class
	attr : [ 'title' ] , 
	formating : true ,

	callback : function( _content , _attributes )
	{
		var pos_ = 0 ;

		var table_ = '';
		var caption_ = _attributes['title'];
		if ( ! caption_.endsWith(':') ) caption_ += ' : ';
		var legend_ = '';

		var n_rows_ = 0 ;
		var n_cols_ = 0 ;

		var arr_ = [];

		
		function is_col_separator_char( c ) { return c == '|' || c == '!' ; }
		function is_row_separator_char( c ) { return c == '+' || c == '-' ; }

		function split_cols( line ) // TODO replace it by a regex
		{
			var cols = [];
			var col_n = 0 ;

			var pos = 0;
			var beg = 0;

			while( pos <= line.length )
			{
				var is_next_separator = 
					TMarqueur.is_space( line[ pos - 1 ] )
					&&
					is_col_separator_char( line[ pos ] )
					&&
					(
						TMarqueur.is_space( line[ pos + 1 ] )
						||
						( pos + 1 ) >= line.length
					)
					||
					( pos ) >= line.length

					;

				if ( is_next_separator && ( pos - beg ) > 1 )
				{
					cols[ col_n ] = line.substring( beg , pos );
					beg = pos;
					col_n++;
				}

				pos++;
			}

			return cols;
		}

		while( pos_ < _content.length )
		{
			var len_ = TMarqueur.line_length_at( _content , pos_ );
			var line_ = _content.substr( pos_ , len_ ).replaceAll('&nbsp;',' ').trim();

			if ( is_col_separator_char( line_[0] ) )
			{
				arr_[ n_rows_ ] = split_cols( line_ ) ; 
				n_cols_ = Math.max( n_cols_ , arr_[ n_rows_ ].length );
				n_rows_++;
			}
			else
			if ( is_row_separator_char( line_[0] ) )
			{
				// ignore
			}
			else
			if ( n_rows_ == 0 )
			{
				if ( line_.length > 0 )
				{
					if ( caption_.length > 0 ) caption_ += '\n';
					caption_ += line_;
				}
			}
			else
			{
				if ( line_.length > 0 )
				{
					if ( legend_.length > 0 ) legend_ += '\n';
					legend_ += line_ ;
				}
			}

			pos_ += len_ + 1;
		} 


		for( var row = 0 ; row < arr_.length ; row++ )
		{
			table_ += '<tr>';

			for( var col = 0 ; col < n_cols_ ; col++ )
			{
				var col_tag_ = arr_[ row ][ col ] !== undefined && arr_[ row ][ col ][0] == '!' ? 'th' : 'td' ; 
				table_ += '\t<' + col_tag_ + '>';
				table_ += arr_[ row ][ col ] !== undefined ? arr_[ row ][ col ].substr(1).trim() : '' ; 
				table_ += '</' + col_tag_ + '>\n';
			}

			table_ += '</tr>\n';
		}
		

		if ( caption_ != '' )
		{
			caption_ = '<caption>' + caption_ + '</caption>\n'
		}

		if ( legend_ != '' )
		{
			legend_ = '<tr class="legend"><td colspan="'+n_cols_+'">' + legend_ + '</td></tr>\n';
		}

		return '<table>\n' + caption_ + '<tbody>\n' + table_ + '</tbody>\n<tfoot>' + legend_ + '</tfoot>\n</table>\n';
	}
});
// EOF
