<?php
/**
 * @class post
 */

namespace Enovision\Rocktober\classes\twig;

abstract class post {

	static public function getUrl( $post ) {
		return $post['url'];
	}

	static public function getAuthor( $post ) {
		$user = $post->user;

		return isset( $post ) ? $user : null;
	}

	static public function getAuthorName( $post ) {
		$author = self::getAuthor( $post );

		return isset( $author ) ? trim( $author->first_name . ' ' . $author->last_name ) : '';
	}

	static public function getAvatar( $post ) {
		$author = self::getAuthor( $post );

		// dump($author->avatar);

		return isset( $author ) ? $author->avatar : '';
	}


	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param boole $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 *
	 * @return String containing either just a URL or a complete image tag
	 * @source https://gravatar.com/site/implement/images/php/
	 */

	static public function getGravatar( $email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = [] ) {
		$url = 'https://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val ) {
				$url .= ' ' . $key . '="' . $val . '"';
			}
			$url .= ' />';
		}

		return $url;
	}

	static public function getPostDate( $post, $format = 'Y-m-d' ) {
		// published_at is a Carbon date object
		// see: https://carbon.nesbot.com/docs/#api-commonformats
		$date = $post->published_at;

		return $date->format( $format );
	}

	static public function getTrimmedPostTitle($title, $maxChar = 100) {
		if (is_string($title) === false) {
			return $title;
		}

		return str_limit($title, $maxChar);
	}
}