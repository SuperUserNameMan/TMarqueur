<?php namespace TMarqueur\Tables;
/**
	||TMarqueur|| is an alternative lightweight markup language 
	for creating formatted text using a plain-text editor.

	||TMarqueur.php|| converts TMarqueur document to HTML.

	||TMarqueur.tables.php|| adds a variant to the preformated 
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


\TMarqueur\plug_block_variant( '`' , [ 
	'type' => 'table' , 
	'test_attribute' => 'lang' , 
	'detector' => '/^table[ ]*(.*)$/i' ,

	'tags' => [ 'div class="table-div"' ] , // TODO add support for class
	'attr' => [ 'title' ] , 
	'formating' => true ,

	'callback' => function( $_content , $_attributes )
	{
		$pos_ = 0 ;

		$table_ = '';

		$caption_ = $_attributes['title'];
		if ( ! str_ends_with( $caption_ , ':' ) ) $caption_ .= ' : ';

		$legend_ = '';

		$n_rows_ = 0 ;
		$n_cols_ = 0 ;

		$arr_ = [];


		$content_len = strlen( $_content );

		while( $pos_ < $content_len )
		{
			$len_ = \TMarqueur\line_byte_length_at( $_content , $pos_ );
			$line_ = substr( $_content , $pos_ , $len_ );
			$line_ = str_replace( '&nbsp;' , ' ' , $line_ );
			$line_ = trim( $line_ );

			$line_len = strlen( $line_ );

			if ( $line_len > 0 && is_col_separator_char( $line_[0] ) )
			{
				$arr_[ $n_rows_ ] = split_cols( $line_ ) ; 
				$n_cols_ = max( $n_cols_ , count( $arr_[ $n_rows_ ] ) );
				$n_rows_++;
			}
			else
			if ( $line_len > 0 && is_row_separator_char( $line_[0] ) )
			{
				// ignore
			}
			else
			if ( $n_rows_ == 0 )
			{
				if ( $line_len > 0  )
				{
					if ( strlen( $caption_ ) > 0 ) $caption_ .= "\n";
					$caption_ .= $line_;
				}
			}
			else
			{
				if ( $line_len > 0 )
				{
					if ( strlen( $legend_ ) > 0 ) $legend_ .= "\n";
					$legend_ .= $line_ ;
				}
			}

			$pos_ += $len_ ;
			if ( $_content[ $pos_ ] == "\r" ) $pos_++;
			if ( $_content[ $pos_ ] == "\n" ) $pos_++;
		} 


		for( $row = 0 ; $row < count( $arr_ ) ; $row++ )
		{
			$table_ .= '<tr>';

			for( $col = 0 ; $col < $n_cols_ ; $col++ )
			{
				$col_tag_ = isset( $arr_[ $row ][ $col ] ) && $arr_[ $row ][ $col ][0] == '!' ? 'th' : 'td' ; 
				$table_ .= "\t".'<' . $col_tag_ . '>';
				$table_ .= isset( $arr_[ $row ][ $col ] ) ? trim( substr( $arr_[ $row ][ $col ] , 1 ) ) : '' ; 
				$table_ .= '</' . $col_tag_ . '>'.PHP_EOL;
			}

			$table_ .= '</tr>'.PHP_EOL;
		}
		

		if ( $caption_ != '' )
		{
			$caption_ = '<caption>' . $caption_ . '</caption>'.PHP_EOL;
		}

		if ( $legend_ != '' )
		{
			$legend_ = '<tr class="legend"><td colspan="' . $n_cols_ . '">' . $legend_ . '</td></tr>'.PHP_EOL;
		}

		return '<table>'.PHP_EOL.$caption_.'<tbody>'.PHP_EOL.$table_.'</tbody>'.PHP_EOL.'<tfoot>'.$legend_.'</tfoot>'.PHP_EOL.'</table>'.PHP_EOL;
	}
]);

		
function is_col_separator_char( $c ) { return $c == '|' || $c == '!' ; }
function is_row_separator_char( $c ) { return $c == '+' || $c == '-' ; }

function split_cols( $line ) // TODO replace it by a regex
{
	$cols = [];
	$col_n = 0 ;

	$pos = 0;
	$beg = 0;

	$line_len = strlen( $line );

	while( $pos <= $line_len )
	{
		$is_next_separator = 
			$pos > 0
			&&
			\TMarqueur\is_space( $line[ $pos - 1 ] )
			&&
			is_col_separator_char( $line[ $pos ] )
			&&
			(
				( $pos + 1 ) >= $line_len
				||
				\TMarqueur\is_space( $line[ $pos + 1 ] )					
			)
			||
			( $pos ) >= $line_len
			;

		if ( $is_next_separator && ( $pos - $beg ) > 1 )
		{
			$cols[ $col_n ] = substr( $line , $beg , $pos - $beg );
			$beg = $pos;
			$col_n++;
		}

		$pos++;
	}

	return $cols;
}


// EOF
