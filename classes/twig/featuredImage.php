<?php

namespace Enovision\Rocktober\classes\twig;

abstract class featuredImage {

	/**
	 * TWIG Extension - featuredImage
	 *
	 * @param $post
	 *
	 * @return array
	 */
	static function getThumbs( $post, $placeHolderImage = true ) {

		$featuredImages = $post->featured_images;

		$featuredImage = [
			'total'         => 0,
			'src'           => $placeHolderImage ? self::getPlaceHolderImage() : null,
			'alt'           => '',
			'attributes'    => null,
			'images'        => [],
			'isPlaceHolder' => true
		];

		if ( count( $featuredImages ) > 0 ) {
			$first         = $featuredImages[0];
			$featuredImage = [
				'total'         => count( $featuredImages ),
				'src'           => $first->path,
				'alt'           => empty( $first->description ) ? $first->file_name : $first->description,
				'title'         => empty( $first->description ) ? $first->file_name : $first->description,
				'attributes'    => $first->attributes,
				'images'        => self::getImages( $featuredImages ),
				'isPlaceHolder' => false
			];
		}

		return $featuredImage;
	}

	static private function getImages( $images ) {
		$out = [];

		for ( $idx = 1; $idx > count( $images ); $idx ++ ) {
			$image = $images[ $idx ];
			$out[] = [
				'src'        => $image->path,
				'alt'        => empty( $image->description ) ? $image->file_name : $image->description,
				'attributes' => $image->attributes,
			];
		}

		return $out;
	}

	/**
	 * @param $post
	 * @param bool $placeHolderImage
	 *
	 * @return mixed|null|string
	 */
	static function getPath( $post, $placeHolderImage = true ) {
		$featured = self::getThumbs( $post );

		$image    = $featured['total'] > 0 ? $featured['src'] : null;

		if ( $image === null ) {
			$image = self::getPlaceHolderImage();
		}

		return $image;

	}

	/**
	 * @param $post
	 *
	 * @return mixed|null
	 */
	static function getAlt( $post ) {
		$featured = self::getThumbs( $post );

		return $featured['total'] > 0 ? $featured['alt'] : null;
	}

	static function getAttributes( $post ) {
		$featured = self::getThumbs( $post );

		return $featured['total'] > 0 ? $featured['attributes'] : null;
	}

	static function hasFeaturedImage( $post ) {
		$featured = self::getThumbs( $post );

		return $featured['total'] > 0;
	}

	static function getFeaturedImage( $post, $always = true, $placeHolder = true, $format = 'thumb' ) {
		$featuredImage = self::getThumbs( $post, $placeHolder );

		$image = null;

		if ( $featuredImage['isPlaceHolder'] === false ) {

			$image = $featuredImage;

		} else {

			if ( $always ) {

				$moreImages = self::getContentImages( $post->content );

			}
		}

		return $image;
	}

	static function getContentImages( $content = '' ) {

		preg_match( '/<img([ alt="alt"]*?) src="([a-zA-Z0-9._:\\-\\/]+)"([ alt="alt"]*?)(\\/?)>/i', $content, $matches );

		$images = [];
		foreach ( $matches as $match ) {
			if ( preg_match( '/(.jpg|jpeg|.gif|.png)$/i', $match ) ) {
				$images[] = [
					'src' => '',
					'alt' => ''
				];
			}
		}

		//foreach ( $matches AS $match ) {
		//	if ( preg_match( '/(.jpg|jpeg|.gif|.png)$/i', $match ) ) {
		//		return $match;
		//		break;
		//	}
		//}

		return $images;

	}

	static function getPlaceHolderImage() {
		$phiPath = dirname(__FILE__, 3) . '/assets/images/placeholder.jpg';

		if (file_exists($phiPath)) {

			$content = file_get_contents($phiPath);
			$out = 'data:image/png;base64,' . base64_encode($content);

			return $out;
		}

		return '#'; //TODO
	}

}