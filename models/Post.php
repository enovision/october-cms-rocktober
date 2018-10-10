<?php

namespace Enovision\Rocktober\Models;

use DB;
use Str;
use Model;
use Markdown;
use Date;
use Carbon\Carbon;


class Post extends Model {

	public $url;
	public $table = 'rainlab_blog_posts';
	public $implement = [ '@RainLab.Translate.Behaviors.TranslatableModel' ];

	protected $isArchivePage = false;
	protected $slugCategory = null;
	protected $usePaging = false;
	protected $currentPage = null;
	protected $offset = 0;

	public $rules = [
		'title'   => 'required',
		'slug'    => [ 'required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:rainlab_blog_posts' ],
		'content' => 'required',
		'excerpt' => ''
	];

	public $translatable = [
		'title',
		'content',
		'content_html',
		'excerpt',
		[ 'slug', 'index' => true ]
	];

	public static $allowedSortingOptions = [
		'title'        => 'Title',
		'created_at'   => 'Created',
		'updated_at'   => 'Updated',
		'published_at' => 'Published',
		'random'       => 'Random'
	];

	/**
	 * The attributes that should be mutated to dates.
	 * @var array
	 */
	protected $dates = [ 'published_at' ];

	/*
	 * Relations
	 */
	public $belongsTo = [
		'user' => [ 'Backend\Models\User' ],
	];

	public $belongsToMany = [
		'categories' => [
			'RainLab\Blog\Models\Category',
			'table' => 'rainlab_blog_posts_categories',
			'order' => 'name'
		]
	];

	public $attachMany = [
		'featured_images' => [
			'System\Models\File',
			'order' => 'sort_order'
		],
		'content_images'  => [
			'System\Models\File'
		]
	];


	public function scopeIsPublished( $query ) {
		return $query
			->whereNotNull( 'published' )
			->where( 'published', true )
			->whereNotNull( 'published_at' )
			->where( 'published_at', '<', Carbon::now() );
	}

	public function scopeSetArchivePage( $query, $options ) {
		$this->isArchivePage = isset( $options['isArchivePage'] ) ? $options['isArchivePage'] === '1' : false;

		if ( false === $this->isArchivePage ) {
			return $query;
		} else {
			$this->slugCategory = isset( $options['slugCategory'] ) ? $options['slugCategory'] : null;
			$this->usePaging = isset( $options['usePaging'] ) ? $options['usePaging'] === '1' : false;

			return $query;
		}

	}

	public function scopeIncludeCategories( $query, $options ) {
		if ( $this->isArchivePage && isset( $this->slugCategory ) ) {
			$categories = [ $this->slugCategory ];
		} else {
			$categories = isset( $options['includeCategories'] ) ? $options['includeCategories'] : null;
		}

		return $query->whereHas( 'categories', function ( $q ) use ( $categories ) {
			if ( $categories !== null ) {
				$q->whereIn( 'id', $categories );
			}
		} );
	}

	public function scopeExcludeCategories( $query, $options ) {
		if ( $this->isArchivePage && isset( $this->slugCategory ) ) {
			$categories = null;
		} else {
			$categories = isset( $options['excludeCategories'] ) ? $options['excludeCategories'] : null;
		}

		return $query->whereHas( 'categories', function ( $q ) use ( $categories ) {
			if ( $categories !== null ) {
				$q->whereNotIn( 'id', $categories );
			}
		} );
	}

	public function scopeSetOffset( $query, $options ) {
		$offset = $this->offset =  isset( $options['offset'] ) ? $options['offset'] : 0;
		$query->skip( $offset );
	}

	public function scopeSetLimit( $query, $options ) {
		$limit = $this->limit = isset( $options['limit'] ) ? $options['limit'] : 12;
		$query->take( $limit );
	}

	public function scopeSetStart( $query, $options ) {
		$currentPage = $this->currentPage = isset( $options['currentPage'] ) ? $options['currentPage'] : 1;
		if ($currentPage > 1) {
			$offset = ($currentPage * $this->limit) + $this->offset;
			$query->offset($offset);
		} else {
			return $query;
		}
	}

	public function scopeListFrontend( $query, $options ) {

		extract( array_merge( [
			'page'          => $this->currentPage,
			'perPage'       => 30,
			'usePaging'     => false,
			'isArchivePage' => false,
			'sortOrder'     => 'created_at',
			'sortDirection' => 'ASC',
			'published'     => true
		], $options ) );

		if ( ! is_array( $sortOrder ) ) {
			$sortOrder = [ $sortOrder ];
		}

		foreach ( $sortOrder as $sort ) {

			if ( in_array( $sort, array_keys( self::$allowedSortingOptions ) ) ) {
				$parts = explode( ' ', $sort );

				if ( count( $parts ) < 2 ) {
					array_push( $parts, 'desc' );
				}

				list( $sortField, $sortDirection ) = $parts;
				if ( $sortField == 'random' ) {
					$sortField = Db::raw( 'RAND()' );
				}

				$query->orderBy( $sortField, $sortDirection );
			}
		}

		return $this->usePaging ? $query->paginate( $perPage, $page ) : $query->get();
	}

	/**
	 * Sets the "url" attribute with a URL to this object
	 *
	 * @param string $pageName
	 * @param Cms\Classes\Controller $controller
	 */
	public function setUrl( $pageName, $controller ) {

		$params = [
			'id'   => $this->id,
			'slug' => $this->slug,
		];

		if ( array_key_exists( 'categories', $this->getRelations() ) ) {
			$params['category'] = $this->categories->count() ? $this->categories->first()->slug : null;
		}

		//expose published year, month and day as URL parameters
		if ( $this->published ) {
			$params['year']  = $this->published_at->format( 'Y' );
			$params['month'] = $this->published_at->format( 'm' );
			$params['day']   = $this->published_at->format( 'd' );
		}

		$this->url = $controller->pageUrl( $pageName, $params );

		return $this->url;
	}


	/**
	 * @param $input
	 * @param bool $preview
	 *
	 * @return mixed
	 */
	public static function formatHtml( $input, $preview = false ) {
		$result = Markdown::parse( trim( $input ) );

		if ( $preview ) {
			$result = str_replace( '<pre>', '<pre class="prettyprint">', $result );
		}

		$result = TagProcessor::instance()->processTags( $result, $preview );

		return $result;
	}
}