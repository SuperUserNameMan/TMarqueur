<?php namespace TMarqueur\Images;
/**
	||TMarqueur|| is an alternative lightweight markup language 
	for creating formatted text using a plain-text editor.

	||TMarqueur.php|| converts TMarqueur document to HTML.

	||TMarqueur.images.php|| adds a variant to the preformated 
	code block to convert its content into a HTML images.

	Example :


```````````````` image 1.2.3 ```````````````
	http://www.example.com/url_image.jpg


	ASCII art version


 Alternative description of the image
````````````````````````````````````````````

*/


\TMarqueur\plug_block_variant( '`' , [
	'type' => 'image' , 
	'test_attribute' => 'lang' , 
	'detector' => '/^image[ ]*(.*)$/i' ,

	'tags' => [ 'div class="image-div"' ] , // TODO add support for class
	'attr' => [ 'title' ] , 
	'formating' => false ,

	'callback' => function( $_content , $_attributes )
	{
		$pos_ = 0 ;

		$url_ = '';
		$alt_ = '';

		$content_len = strlen( $_content );

		$line_count_ = 0 ;

		while( $pos_ < $content_len )
		{
			$len_ = \TMarqueur\line_byte_length_at( $_content , $pos_ );
			$line_ = substr( $_content , $pos_ , $len_ );
			$line_ = str_replace( '&nbsp;' , ' ' , $line_ );
			$line_ = trim( $line_ );

			$line_len = strlen( $line_ );

			if ( $line_count_ == 0 )
			{
				$url_ = $line_ ;
			}
			else
			{
				$alt_ = $line_ ;
			}

			$pos_ += $len_ ;
			if ( $_content[ $pos_ ] == "\r" ) $pos_++;
			if ( $_content[ $pos_ ] == "\n" ) $pos_++;

			$line_count_++;
		}

		return '<img src="'.rawurlencode($url_).'" alt="'.htmlentities($alt_).'">'.PHP_EOL;
	}
]);


// EOF
