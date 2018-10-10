<?php namespace Enovision\Rocktober;

use System\Classes\PluginBase;

use Illuminate\Database\Eloquent\Relations\Relation;
use Enovision\Rocktober\classes\twig\featuredImage;
use Enovision\Rocktober\classes\twig\post;

class Plugin extends PluginBase {


	public function boot() {

		Relation::morphMap( [
			'RainLab\Blog\Models\Post' => 'Enovision\Rocktober\Models\Post'
		] );

	}

	public function registerComponents() {
		return [
			'Enovision\Rocktober\Components\Postblock' => 'postBlock'
		];
	}

	public function registerSettings() {
	}

	/**
	 * Register TWIG extensions
	 * @return array
	 */
	public function registerMarkupTags() {
		return [
			'functions' => [
				'url'                => [post::class, 'getUrl'],
				'rtFeaturedImage'    => [featuredImage::class, 'getFeaturedImage'],
				'rtHasFeaturedImage' => [featuredImage::class, 'hasFeaturedImage'],
				'rtGetPath'          => [featuredImage::class, 'getPath'],
				'rtGetAlt'           => [ featuredImage::class, 'getAlt' ],
				'rtGetAuthorName'    => [ post::class, 'getAuthorName' ],
				'rtGetAuthorAvatar'    => [ post::class, 'getAvatar' ],
				'gravatar'           => [post::class, 'getGravatar'],
				'rtGetPostDate'      => [ post::class, 'getPostDate' ],
				'rtGetTrimmedPostTitle' => [ post::class, 'getTrimmedPostTitle' ]
			],
			'filters'   => [
				'uppercase' => [ $this, 'makeTextAllCaps' ],
			]
		];
	}

	public function makeTextAllCaps( $text ) {
		//return strtoupper( $text );
		return $text;
	}
}